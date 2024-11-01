<?php namespace siteguard\security\model;


use siteguard\security\logger\Logger;


/**
 * Class ErrorLev
 *
 * @package siteguard\security\format
 */
class Format {
	/**
	 * @param array       $table
	 * @param string|null $row
	 * @param string      $key
	 *
	 * @return int|string
	 */
	public static function getNumFromTable( $table, $row, $key ) {
		$num = 0;

		if ( is_null( $row ) ) {
			foreach ( $table as $row_data ) {
				$num += (int) ( $row_data->{$key} );
			}
		} else {
			if ( isset( $table[ $row ] ) ) {
				$num += (int) ( $table[ $row ]->{$key} );
			}
		}

		return $num > 0 ? $num : "-";
	}


	/**
	 * @param int|array $from
	 * @param bool      $abbr
	 *
	 * @return string
	 */
	public static function getCount( $from, $abbr = true ) {
		if ( is_array( $from ) ) {
			return self::getCountBy( function ( $key ) use ( $from ) {
				return (int) ( isset( $from[ $key ] ) ? $from[ $key ] : 0 );
			}, $abbr );
		}

		return self::getCountBy( function ( $key ) use ( $from ) {
			return (int) get_post_meta( (int) $from, $key, true );
		}, $abbr );
	}


	/**
	 * @param callable $reader
	 * @param bool     $abbr
	 *
	 * @return string
	 */
	public static function getCountBy( $reader, $abbr = true ) {
		$time_count_keys = Logger::getTimeCountKeys();

		$count = (int) $reader( $time_count_keys["total"] );

		$full = sprintf(
			__( "Total: <b>%d</b>, today: <b>%d</b>, yesterday: <b>%d</b>, this month: <b>%d</b>, previous month: <b>%d</b>.", "siteguard-security" ),
			$count,
			(int) $reader( $time_count_keys["today"] ),
			(int) $reader( $time_count_keys["today-1"] ),
			(int) $reader( $time_count_keys["month"] ),
			(int) $reader( $time_count_keys["month-1"] )
		);

		if ( $abbr ) {
			$full_title = strip_tags( $full );

			return "<abbr title='$full_title'>{$count}</abbr>";
		}

		return $full;
	}


	/**
	 * @param int $post_id
	 *
	 * @return string
	 */
	public static function getPluginLink( $post_id ) {
		$plugin = get_post_meta( $post_id, "plugin", true );
		if ( ! empty( $plugin ) ) {
			$name = get_post_meta( $post_id, "plugin-name", true );
			if ( empty( $name ) ) {
				$name = $plugin;
			}

			$ver = get_post_meta( $post_id, "plugin-version", true );
			if ( empty( $ver ) ) {
				$ver = "-";
			}

			$name_html = ( $ver !== "-" )
				? sprintf( __( "%s (%s)", "siteguard-security" ), esc_html( $name ), esc_html( $ver ) )
				: esc_html( $name );

			$name_url = urldecode( $name );

			return "<a href='/wp-admin/plugins.php?s={$name_url}'>{$name_html}</a>";
		}

		return "";
	}


	/**
	 * @param string $gmt
	 *
	 * @return string
	 */
	public static function formatDateGMT( $gmt ) {
		return self::formatTime( strtotime( get_date_from_gmt( $gmt ) ) );
	}


	/**
	 * @param int $time
	 *
	 * @return string
	 */
	public static function formatTime( $time ) {
		$int_time = (int) $time;

		$time_diff = time() - $int_time;
		$time_text = ( $time_diff > 0 && $time_diff < 24 * 60 * 60 )
			? sprintf( __( "%s ago", "siteguard-security" ), human_time_diff( $time ) )
			: date_i18n( __( "Y/m/d" ), $int_time );
		$time_tip  = date_i18n( __( 'Y/m/d g:i:s a' ), $int_time );

		return "<abbr title='{$time_tip}'>{$time_text}</abbr>";
	}


	/**
	 * @param $lev
	 *
	 * @return string
	 */
	public static function errorLevToString( $lev ) {
		switch ( (int) $lev ) {
			case E_ERROR:
				return 'E_ERROR';
			case E_WARNING:
				return 'E_WARNING';
			case E_PARSE:
				return 'E_PARSE';
			case E_NOTICE:
				return 'E_NOTICE';
			case E_CORE_ERROR:
				return 'E_CORE_ERROR';
			case E_CORE_WARNING:
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR:
				return 'E_COMPILE_ERROR';
			case E_COMPILE_WARNING:
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR:
				return 'E_USER_ERROR';
			case E_USER_WARNING:
				return 'E_USER_WARNING';
			case E_USER_NOTICE:
				return 'E_USER_NOTICE';
			case E_STRICT:
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR:
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED:
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED:
				return 'E_USER_DEPRECATED';
		}

		return "";
	}
}
