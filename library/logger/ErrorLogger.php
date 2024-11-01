<?php namespace siteguard\security\logger;


use Exception;
use siteguard\security\system\System;


/**
 * Class ErrorLogger
 *
 * @package siteguard\security\logger
 */
class ErrorLogger extends Logger {
	/** @var string|null */
	private $se = null;


	/**
	 * Init the php error logger
	 *
	 * @param ErrorLogger $self This variable is $this ($this is not usable in closure until php 5.4)
	 *
	 * @return callable
	 */
	public function register( $self ) {
		register_shutdown_function( function () use ( $self ) {
			try {
				$error = error_get_last();

				if ( is_array( $error ) && ! $self->isSuppressError( $error ) ) {
					$self->log( $error["type"], $error["message"], $error["file"], $error["line"] );
				}
			}
			catch ( Exception $ignore ) {
			}
		} );

		return set_error_handler( function ( $n, $str, $file, $line ) use ( $self ) {
			try {
				if ( (bool) error_reporting() ) {
					$self->log( $n, $str, $file, $line );
				}

				$self->doNotLog( $file, $line );
			}
			catch ( Exception $ignore ) {
			}

			return false;
		}, E_ALL | E_STRICT );
	}


	/**
	 * @param array $error
	 *
	 * @return bool
	 */
	private function isSuppressError( $error ) {
		return $this->se === "{$error["file"]}({$error["line"]});";
	}


	/**
	 * @param string $file
	 * @param int    $line
	 */
	public function doNotLog( $file, $line ) {
		$this->se = "{$file}($line);";
	}


	/**
	 * @param int    $lev  The level of the error raised.
	 * @param string $msg  The error message.
	 * @param string $file The filename where the error was raised.
	 * @param int    $line The line number where the error was raised.
	 */
	private function log( $lev, $msg, $file, $line ) {
		$path = System::getRelativeToWpRootPath( $file );

		$this->load(
			array( $lev, $msg, $path, $line ),
			"{$msg}", "{$path}:{$line}",
			$post_id, $data
		);

		$this->addBaseInfo( $data );

		$this->addPluginInfo( $data, $file );

		$data["lev"]  = $lev;
		$data["msg"]  = $msg;
		$data["file"] = $path;
		$data["line"] = $line;

		$this->save( $post_id, $data );
	}
}
