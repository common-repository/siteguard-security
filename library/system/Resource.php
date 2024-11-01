<?php namespace siteguard\security\system;


add_action( "admin_head", array( "\\siteguard\\security\\system\\Resource", "includeCss" ) );


/**
 * Class Resource
 *
 * @package siteguard\security\system
 */
class Resource {
	private static $css_list = array();


	/**
	 * @param $local_path
	 */
	public static function addCss( $local_path ) {
		$url = plugins_url( $local_path, System::getEntryPoint() );

		$time = @filemtime( System::getAbsolutePath( $local_path ) );

		self::$css_list[ $url ] = "<link rel='stylesheet' type='text/css' href='{$url}?t={$time}'>";
	}


	/**
	 * Add cee to the header
	 */
	public static function includeCss() {
		echo implode( "", self::$css_list );
	}
}
