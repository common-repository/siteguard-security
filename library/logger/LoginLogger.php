<?php namespace siteguard\security\logger;


use Exception;


/**
 * Class LoginLogger
 *
 * @package siteguard\security\logger
 */
class LoginLogger extends Logger {

	/**
	 * Register the required hooks
	 *
	 * @param LoginLogger $self
	 *
	 * @return callable
	 */
	public function register( $self ) {
		add_action( "wp_login_failed", function ( $user_login ) use ( $self ) {
			try {
				$self->log( $user_login );
			}
			catch ( Exception $ignore ) {
			}
		} );
	}


	/**
	 * @param string $user_login The login that fails to login.
	 */
	private function log( $user_login ) {
		$script_file = $this->getScriptFileName();

		$this->load(
			array( $user_login, $script_file ),
			"Login failed, username: {$user_login} at {$script_file}", "{$user_login}",
			$post_id, $data
		);

		$this->addBaseInfo( $data, false );

		$data["user_login"] = $user_login;

		$current_ip = $_SERVER["REMOTE_ADDR"];
		$ip_map     = $this->unserializeArray( $data, "ip_map" );

		if ( ! isset( $ip_map[ $current_ip ] ) ) { // ip not present in the map
			if ( count( $ip_map ) > 9 ) { // keep max 10 records
				unset( $ip_map[ $this->getOldestIp( $ip_map ) ] ); // remove the oldest counter
			}

			$ip_map[ $current_ip ] = array(); // create empty data
		}

		$ip_map[ $current_ip ]["time"] = microtime( true ); // set now time
		$this->count( $ip_map[ $current_ip ] ); // increase the counter

		$data["ip_map"] = $ip_map;

		$this->save( $post_id, $data );
	}


	/**
	 * @param array $ip_map
	 *
	 * @return null|string
	 */
	private function getOldestIp( $ip_map ) {
		$oldest_time = null;
		$oldest_ip   = null;

		foreach ( $ip_map as $ip => $info ) {
			if ( is_null( $oldest_time ) || $info["time"] < $oldest_time ) {
				$oldest_time = $info["time"];
				$oldest_ip   = $ip;
			}
		}

		return $oldest_ip;
	}
}
