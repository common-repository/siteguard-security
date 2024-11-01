<?php namespace siteguard\security\system;


/**
 * Class Input
 *
 * @package siteguard\security\system
 */
class Input {

	/**
	 * @param string $name
	 * @param mixed  $def
	 *
	 * @return mixed
	 */
	public static function read( $name, $def = null ) {
		$value = null;

		if ( isset( $_POST[ $name ] ) ) {
			$value = $_POST[ $name ];
		} else {
			if ( isset( $_GET[ $name ] ) ) {
				$value = $_GET[ $name ];
			}
		}

		return ! empty( $value ) ? $value : $def;
	}


	/**
	 * @param array $names
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function readArray( $names, $extra = array() ) {
		$result = array();

		foreach ( $names as $name => $def ) {
			$value = self::read( $name );
			if ( ! is_null( $value ) && $value != $def ) {
				$result[ $name ] = $value;
			}
		}

		return array_merge( $result, $extra );
	}
}
