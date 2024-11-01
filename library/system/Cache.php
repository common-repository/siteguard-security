<?php namespace siteguard\security\system;


/**
 * Class Cache
 *
 * @package siteguard\security\system
 */
class Cache {
	private static $cache_map = array();


	/**
	 * @param string   $key
	 * @param callable $builder
	 *
	 * @return mixed
	 */
	public static function get( $key, $builder ) {
		if ( ! isset( self::$cache_map[ $key ] ) ) {
			self::$cache_map[ $key ] = $builder();
		}

		return self::$cache_map[ $key ];
	}
}
