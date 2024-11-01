<?php namespace siteguard\security\system;


/**
 * Class System
 *
 * @package siteguard\security\system
 */
class System {
	private static $entry_point = null;
	private static $root = null;


	/**
	 * @param string $entry_point
	 */
	public static function init( $entry_point ) {
		self::$entry_point = $entry_point;
		self::$root        = plugin_dir_path( $entry_point );

		add_filter( "set-screen-option", array( "\\siteguard\\security\\system\\System", "setScreenOptions" ), 10, 3 );
	}


	/**
	 * @return string The path of the siteguard-security.php file
	 */
	public static function getEntryPoint() {
		return self::$entry_point;
	}


	/**
	 * @return string The directory of the siteguard-security plugin
	 */
	public static function getRoot() {
		return self::$root;
	}


	/**
	 * @param string $absolute_path
	 *
	 * @return string The path relative to the wp root (ABSPATH) starting with '/'.
	 */
	public static function getRelativeToWpRootPath( $absolute_path ) {
		return self::getRelativeToPath( $absolute_path, ABSPATH );
	}


	/**
	 * @param string $absolute_path
	 * @param string $relative_to
	 *
	 * @return string|false Return the relative path (starting with /) or false on failure
	 */
	public static function getRelativeToPath( $absolute_path, $relative_to ) {
		$source_real_path      = realpath( $absolute_path );
		$relative_to_real_path = realpath( $relative_to );

		// if both exists
		if ( $source_real_path !== false && $relative_to_real_path !== false ) {

			// if the source file is inside the plugin dir
			if ( substr( $source_real_path, 0, strlen( $relative_to_real_path ) ) === $relative_to_real_path ) {
				$relative_path = wp_normalize_path(
					substr( $source_real_path, strlen( $relative_to_real_path ) )
				);

				return substr( $relative_path, 0, 1 ) !== "/"
					? "/{$relative_path}"
					: $relative_path;
			}
		}

		return false;
	}


	/**
	 * @param string $local_path This relative the the siteguard-security plugin dir
	 *                           (the one that contains the siteguard-security.php)
	 *
	 * @return string
	 */
	public static function getAbsolutePath( $local_path ) {
		return self::$root . ltrim( wp_normalize_path( $local_path ), "/" );
	}


	/**
	 * @param string $local_path      This relative the the siteguard-security plugin dir
	 *                                (the one that contains the siteguard-security.php)
	 * @param bool   $check_existence Verify if the file exists?
	 *
	 * @return bool
	 */
	public static function requireLocalPath( $local_path, $check_existence = true ) {
		$absolute_path = System::getAbsolutePath( $local_path );

		if ( ! $check_existence || file_exists( $absolute_path ) ) {
			/** @noinspection PhpIncludeInspection */
			require $absolute_path;

			return true;
		}

		return false;
	}


	/**
	 * Accept all screen options that start for 'siteguard_security_'.
	 *
	 * @param mixed  $result
	 * @param string $option
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function setScreenOptions( $result, $option, $value ) {
		return substr( $option, 0, 19 ) === "siteguard_security_" ? $value : $result;
	}
}
