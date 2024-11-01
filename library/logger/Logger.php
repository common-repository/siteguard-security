<?php namespace siteguard\security\logger;


use DateInterval;
use DateTime;
use siteguard\security\query\Select;
use siteguard\security\system\Cache;
use siteguard\security\system\System;


/**
 * Class Logger
 *
 * @package siteguard\security\logger
 */
abstract class Logger {
	private static $loggers = array();


	protected $old_hook = null;
	protected $name = null;


	/**
	 * Init the requested logs
	 *
	 * @param array $logs
	 */
	public static function init( $logs = array() ) {
		foreach ( $logs as $log ) {
			self::getLogger( $log );
		}
	}


	/**
	 * @param string $log
	 *
	 * @return Logger
	 */
	public static function getLogger( $log ) {
		if ( ! isset( self::$loggers[ $log ] ) ) {
			$class_name = "\\siteguard\\security\\logger\\{$log}Logger";

			self::$loggers[ $log ] = class_exists( $class_name )
				? new $class_name( $log )
				: null;
		}

		return self::$loggers[ $log ];
	}


	/**
	 * Create a new logger
	 *
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->old_hook = $this->register( $this );

		$this->name = strtolower( "siteguard_{$name}" );
	}


	/**
	 * Register the required hooks
	 *
	 * @param Logger $self
	 *
	 * @return callable
	 */
	public abstract function register( $self );


	/**
	 * @param array  $key_values
	 * @param string $title
	 * @param string $content
	 * @param int    $post_id
	 * @param array  $data
	 */
	protected function load( $key_values, $title, $content, &$post_id, &$data ) {
		$post_name = md5( json_encode( $key_values ) );

		$post_ids = Select::from( "posts" )
		                  ->select( "posts.id" )
		                  ->where( "posts.post_status = %s", "private" )
		                  ->where( "posts.post_type = %s", $this->name )
		                  ->where( "posts.post_name = %s", $post_name )
		                  ->exec( "get_col" );

		switch ( count( $post_ids ) ) {
			case 0:
				$post_id = wp_insert_post( array(
					"post_status"  => "private",
					"post_type"    => $this->name,
					"post_title"   => wp_slash( $title ),
					"post_content" => wp_slash( $content ),
					"post_author"  => - 1, // set no user
					"post_name"    => $post_name,
				) );
				break;
			default:
				$post_id = $post_ids[0];
				break;
		}

		$data = (array) get_post_meta( $post_id, "" );
		foreach ( $data as $k => $v ) {
			if ( is_array( $v ) && count( $v ) > 0 ) {
				$data[ $k ] = $v[0];
			} else {
				$data[ $k ] = null;
			}
		}
		$data["__keys__"] = array_keys( $data ); // just save all the key when load!
	}


	/**
	 * @return string The entry point (Relative to the wp root)
	 */
	protected function getScriptFileName() {
		return System::getRelativeToWpRootPath( realpath( $_SERVER["SCRIPT_FILENAME"] ) );
	}


	/**
	 * Add base info
	 *
	 * @param array $data          The data to fill up
	 * @param bool  $log_user_data Log user data?
	 */
	protected function addBaseInfo( &$data, $log_user_data = true ) {
		$data["script_filename"] = $this->getScriptFileName();

		foreach ( array( "HTTP_USER_AGENT", "REQUEST_URI", "REMOTE_ADDR" ) as $key ) {
			if ( isset( $_SERVER[ $key ] ) ) {
				$data[ strtolower( $key ) ] = $_SERVER[ $key ];
			}
		}

		if ( $log_user_data ) {
			$data["user"] = get_current_user_id();
		}
	}


	/**
	 * @param array  $data The data to fill up
	 * @param string $file The absolute path of the file that generate the event, something like:
	 *                     /some-where/wp-content-dir-name/plugins/plugin-file.php
	 *                     /some-where/wp-content-dir-name/plugins/plugin-dir/plugin-file.php
	 *                     /some-where/wp-content-dir-name/plugins/plugin-dir/some-path/some-file.php
	 *
	 *                     /some-where/wp-content-dir-name/themes/theme-name/some-path/some-file.php
	 *
	 *                     /some-where/some-path/some-file.php
	 */
	protected function addPluginInfo( &$data, $file ) {
		$rel_to_plugin_dir_path = System::getRelativeToPath( $file, WP_PLUGIN_DIR );

		// if the $file is inside of the WP_PLUGIN_DIR directory
		if ( $rel_to_plugin_dir_path !== false ) {
			$plugin_info = $this->getPluginInfo( $rel_to_plugin_dir_path );

			// if identified ..
			if ( $plugin_info !== false ) {
				$data["plugin"] = $plugin_info["key"];

				foreach ( array( "Name", "Version" ) as $prop ) {
					if ( isset( $plugin_info["info"][ $prop ] ) ) {
						$data[ "plugin-" . strtolower( $prop ) ] = $plugin_info["info"][ $prop ];
					}
				}
			}
		}
	}


	/**
	 * Given a file path relative the plugin directory search for plugin data, if can't identify the plugin return false.
	 *
	 * @param string $rel_to_plugin_dir_path The path relative to the plugin dir, the first slash will be trimmed if present, something like:
	 *                                       /plugin-file.php
	 *                                       /plugin-dir/plugin-file.php
	 *                                       /plugin-dir/some-path/some-file.php
	 *
	 * @return array|false Return false if the plugin is not identified.
	 */
	protected function getPluginInfo( $rel_to_plugin_dir_path ) {
		$rel_to_plugin_dir_path = trim( wp_normalize_path( $rel_to_plugin_dir_path ), "/" );

		return Cache::get( __METHOD__ . "[{$rel_to_plugin_dir_path}]",
			function () use ( $rel_to_plugin_dir_path ) {
				/**
				 * If the get_plugins function is not defined require the "wp-admin/includes/plugin.php" file
				 *
				 * @see https://codex.wordpress.org/Function_Reference/get_plugins
				 */
				if ( ! function_exists( "get_plugins" ) ) {
					require_once ABSPATH . "wp-admin/includes/plugin.php";
				}

				// retrieve all plugin "main" files with plugin data
				$all_plugins = get_plugins();

				// check if the source file is one of the plugin "main" files.
				if ( isset( $all_plugins[ $rel_to_plugin_dir_path ] ) ) {
					return array(
						"key"  => $rel_to_plugin_dir_path,
						"info" => $all_plugins[ $rel_to_plugin_dir_path ],
					);
				}

				// split the path and search for the first token
				$tokens = explode( "/", $rel_to_plugin_dir_path );

				/**
				 * get the first token (plugin dir)
				 * if the count( $tokens ) === 1 then there is no directory
				 */
				if ( count( $tokens ) > 1 ) {
					$candidates = array();

					// use the directory or file name to search a candidate plugin
					foreach ( $all_plugins as $key => $info ) {
						if ( strpos( $key, "{$tokens[0]}/" ) === 0 ) {
							$candidates[] = $key;
						}
					}

					// if there is just one candidate ..
					if ( count( $candidates ) === 1 ) {
						return array(
							"key"  => $candidates[0],
							"info" => $all_plugins[ $candidates[0] ],
						);
					}
				}

				// not identified
				return false;
			}
		);
	}


	/**
	 * @param array  $data
	 * @param string $key
	 *
	 * @return array
	 */
	protected function unserializeArray( &$data, $key ) {
		$array = isset( $data[ $key ] )
			? $data[ $key ]
			: "";

		$array = ! empty( $array )
			? @unserialize( $array )
			: array();

		if ( ! is_array( $array ) ) {
			$array = array();
		}

		$data[ $key ] = $array;

		return $array;
	}


	/**
	 * @param int   $post_id
	 * @param array $data
	 */
	protected function save( $post_id, $data ) {
		if ( $post_id !== 0 ) {
			$this->count( $data );

			foreach ( $data as $k => $v ) {
				if ( $k !== "__keys__" ) {
					update_post_meta( $post_id, $k, wp_slash( $v ) );
				}
			}

			$deleted_keys = array_diff( $data["__keys__"], array_keys( $data ) );
			foreach ( $deleted_keys as $k ) {
				delete_post_meta( $post_id, $k );
			}

			wp_update_post( array( "ID" => $post_id, "post_author" => - 1 ) );
		}
	}


	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function count( &$data ) {
		$time_count_keys = self::getTimeCountKeys();

		foreach ( $time_count_keys["__inc__"] as $key ) {
			$data[ $key ] = isset ( $data[ $key ] ) // increase the counter
				? $data[ $key ] + 1
				: 1;
		}

		foreach ( $data as $k => $v ) {
			if ( substr( $k, 0, 6 ) === "count-" && ! in_array( $k, $time_count_keys["__all__"] ) ) {
				unset( $data[ $k ] );
			}
		}

		return $data;
	}


	/**
	 * @return array
	 */
	public static function getTimeCountKeys() {
		return Cache::get( __METHOD__, function () {
			$now = new DateTime();

			$year  = (int) date_i18n( "Y", $now->getTimestamp() );
			$month = (int) date_i18n( "m", $now->getTimestamp() );

			$today     = date_i18n( "Ymd", $now->getTimestamp() );
			$yesterday = date_i18n( "Ymd", $now->add( DateInterval::createfromdatestring( "-1 day" ) )->getTimestamp() );

			$year_old  = $year - 1;
			$month_old = $month - 1;

			$month_txt     = $month > 9 ? "{$month}" : "0{$month}";
			$month_old_txt = $month_old > 9 ? "{$month_old}" : "0{$month_old}";

			$year_month_old = $month_old > 0
				? "{$year}{$month_old_txt}"
				: "{$year_old}12";

			return array(
				"__all__" => array(
					"count",
					"count-{$today}",
					"count-{$yesterday}",
					"count-{$year}{$month_txt}",
					"count-{$year_month_old}",
					"count-{$year}",
					"count-{$year_old}"
				),
				"__inc__" => array(
					"count",
					"count-{$today}",
					"count-{$year}{$month_txt}",
					"count-{$year}",
				),
				"total"   => "count",
				"today"   => "count-{$today}",
				"today-1" => "count-{$yesterday}",
				"month"   => "count-{$year}{$month_txt}",
				"month-1" => "count-{$year_month_old}",
				"year"    => "count-{$year}",
				"year-1"  => "count-{$year_old}",
			);
		} );
	}
}
