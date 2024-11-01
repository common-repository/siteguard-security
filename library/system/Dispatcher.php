<?php namespace siteguard\security\system;


/**
 * Class Dispatcher
 *
 * @package siteguard\security\system
 */
class Dispatcher {
	/**
	 * @param string $page_name The name of the page
	 * @param bool   $call_load Indicate if load must be "manual" called
	 */
	public static function dispatch( $page_name, $call_load = false ) {
		$page_type = Input::read( "type", $page_name );

		if ( $call_load ) {
			Dispatcher::load( $page_name );
		}

		System::requireLocalPath( "/views/{$page_name}/{$page_type}.html.php" );
	}


	/**
	 * @param string $page_name The name of the page
	 */
	public static function load( $page_name ) {
		$page_type = Input::read( "type", $page_name );
		$action    = self::getAction();

		if ( $action !== "none" || isset( $_POST["page"] ) ) {
			if ( ! System::requireLocalPath( "/views/{$page_name}/{$page_type}.exec.{$action}.php" ) ) {
				System::requireLocalPath( "/views/_generic/_generic.exec.{$action}.php" );
			}
		} else {
			if ( ! System::requireLocalPath( "/views/{$page_name}/{$page_type}.help.php" ) ) {
				System::requireLocalPath( "/views/_generic/_generic.help.php" );
			}

			if ( ! System::requireLocalPath( "/views/{$page_name}/{$page_type}.load.php" ) ) {
				System::requireLocalPath( "/views/_generic/_generic.load.php" );
			}
		}
	}


	/**
	 * @return string The action associated to the request.
	 */
	public static function getAction() {
		foreach ( array( "action", "action2" ) as $name ) {
			$action = (string) Input::read( $name, "-1" );
			if ( $action !== "-1" ) {
				return $action;
			}
		}

		return "none";
	}


	/**
	 * @param string   $key    The name of the id container
	 * @param string   $name   The name of the bulk action.
	 * @param callable $action The callable action
	 * @param array    $args   The input args that will be passed to the redirect url
	 * @param int      $done   N° of done items
	 * @param int      $skip   N° of skip item
	 */
	public static function doAction( $key, $name, $action, $args = array(), &$done = 0, &$skip = 0 ) {

		$ids = Input::read( $key, array() );
		if ( empty( $ids ) ) {
			$ids = array( (int) Input::read( "id" ) );
		}

		$done = 0;
		$skip = 0;

		foreach ( $ids as $id ) {
			if ( $action( get_post( $id ) ) ) {
				$done += 1;
			} else {
				$skip += 1;
			}
		}

		Dispatcher::redirectToCurrentPage( Input::readArray( $args, array(
			"message" => "{$name}:{$done}:{$skip}",
		) ) );
	}


	/**
	 * @param array $query
	 */
	public static function redirectToCurrentPage( $query = array() ) {
		self::redirectToPage( $_REQUEST["page"], $query );
	}


	/**
	 * @param string $page
	 * @param array  $query
	 */
	public static function redirectToPage( $page, $query = array() ) {
		$redirect_to = add_query_arg( $query, menu_page_url( $page, false ) );
		wp_safe_redirect( $redirect_to );
		exit();
	}
}
