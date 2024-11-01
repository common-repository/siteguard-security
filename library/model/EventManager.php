<?php namespace siteguard\security\model;


use siteguard\security\logger\Logger;
use siteguard\security\query\Select;
use siteguard\security\system\Cache;
use siteguard\security\system\Input;


/**
 * Class EventManager
 *
 * @package siteguard\security\model
 */
class EventManager {
	private static $event_types = array(
		"siteguard_login"     => true,
		"siteguard_error"     => true,
		"siteguard_exception" => true,
	);


	/**
	 * @return int
	 */
	public static function getTodayEventNum() {
		return Cache::get( __METHOD__, function () {
			$time_count_keys = Logger::getTimeCountKeys();

			$query = Select::from( "posts" )
			               ->where( "posts.post_type in (%s, %s, %s)", "siteguard_login", "siteguard_error", "siteguard_exception" );

			foreach ( array( "today" ) as $key ) {
				$query->join( "left outer", "postmeta", "today", "posts.id = today.post_id and today.meta_key = %s", $time_count_keys[ $key ] );
				$query->select( "sum(coalesce(today.meta_value, 0)) as num" );
			}

			return (int) $query->exec( "get_var" );
		} );
	}


	/**
	 * @return mixed
	 */
	public static function getStatisticByType() {
		return Cache::get( __METHOD__, function () {
			$time_count_keys = Logger::getTimeCountKeys();

			$query = Select::from( "posts" )
			               ->select( "posts.post_type as post_type" )
			               ->where( "posts.post_type in (%s, %s, %s)", "siteguard_login", "siteguard_error", "siteguard_exception" );

			foreach ( array( "total", "today", "today-1", "month", "month-1" ) as $key ) {
				$query->join( "left outer", "postmeta", "`{$key}`", "posts.id = `{$key}`.post_id and `{$key}`.meta_key = %s", $time_count_keys[ $key ] );
				$query->select( "sum(coalesce(`{$key}`.meta_value, 0)) as `{$key}`" );
			}

			$query->group_by( "posts.post_type" );

			return $query->exec( "get_results", OBJECT_K );
		} );
	}


	/**
	 * @return \WP_Post|null
	 */
	public static function getCurrentEvent() {
		return Cache::get( __METHOD__, function () {
			$item = get_post( (int) Input::read( "id" ) );

			if ( EventManager::canAny( $item ) ) {
				return $item;
			}

			return null;
		} );
	}


	/**
	 * @param \WP_Post|null $event
	 *
	 * @return bool
	 */
	public static function reset( $event ) {
		if ( self::canDelete( $event ) ) {
			update_post_meta( $event->ID, "reset_time", time() );

			$keys = array_keys( (array) get_post_meta( $event->ID, "" ) );
			foreach ( $keys as $k ) {
				if ( substr( $k, 0, 6 ) === "count-" ) {
					delete_post_meta( $event->ID, $k );
				}
			}

			update_post_meta( $event->ID, "count", 0 );

			if ( $event->post_type === "siteguard_login" ) {
				update_post_meta( $event->ID, "ip_map", array() );
			}

			return true;
		}

		return false;
	}


	/**
	 * @param \WP_Post|null $event
	 *
	 * @return bool
	 */
	public static function canReset( $event ) {
		return self::canAny( $event );
	}


	/**
	 * @param \WP_Post|null $event
	 *
	 * @return bool
	 */
	public static function delete( $event ) {
		if ( self::canDelete( $event ) ) {
			return wp_delete_post( $event->ID, true ) !== false;
		}

		return false;
	}


	/**
	 * @param \WP_Post|null $event
	 *
	 * @return bool
	 */
	public static function canDelete( $event ) {
		return self::canAny( $event );
	}


	/**
	 * @param \WP_Post|null $event
	 *
	 * @return bool
	 */
	private static function canAny( $event ) {
		if ( is_null( $event ) ) {
			return false;
		}

		if ( ! isset( self::$event_types[ $event->post_type ] ) ) {
			return false;
		}

		if ( ! current_user_can( "manage_options" ) ) {
			return false;
		}

		return true;
	}
}
