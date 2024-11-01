<?php namespace siteguard\security\model;


/**
 * Class IssueManager
 *
 * @package siteguard\security\model
 */
class IssueManager {
	/**
	 * Check for security issues
	 *
	 * @return array The collection of security issues
	 */
	public static function getIssues() {
		$issues = array();

		self::checkWpTablePrefix( $issues );
		self::checkAdminUser( $issues );
		self::checkDbPassword( $issues );
		self::checkWpDebug( $issues );

		return $issues;
	}


	/**
	 * @param $issues
	 */
	private static function checkWpTablePrefix( &$issues ) {
		if ( strtolower( $GLOBALS["table_prefix"] ) === "wp_" ) {
			$issues[] = array(
				"html" => __( "Change the default <strong>wp_</strong> table prefix to increase the security.", "siteguard-security" ),
			);
		}
	}


	/**
	 * @param $issues
	 */
	private static function checkAdminUser( &$issues ) {
		if ( get_user_by( "login", "admin" ) !== false ) {
			$issues[] = array(
				"html" => __( "Avoid the use of <strong>admin</strong> username.", "siteguard-security" ),
			);
		}
	}


	/**
	 * @param $issues
	 */
	private static function checkDbPassword( &$issues ) {
		if ( strlen( DB_PASSWORD ) === 0 ) {
			$issues[] = array(
				"html" => __( "Avoid <strong>empty</strong> password to access the database, define one to increase the security.", "siteguard-security" ),
			);
		}

		if ( strtolower( DB_USER ) === strtolower( DB_PASSWORD ) ) {
			$issues[] = array(
				"html" => __( "Avoid to use a password <strong>equals</strong> to the username to access the database, change it to increase the security.", "siteguard-security" ),
			);
		}
	}


	/**
	 * @param $issues
	 */
	private static function checkWpDebug( &$issues ) {
		if ( WP_DEBUG && WP_DEBUG_DISPLAY ) {
			$issues[] = array(
				"html" => __( "Don't show <strong>debug</strong> information to the user. Check the <strong>WP_DEBUG</strong> and <strong>WP_DEBUG_DISPLAY</strong> constants.", "siteguard-security" ),
			);
		}
	}
}
