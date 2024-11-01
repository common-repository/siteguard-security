<?php namespace siteguard\security\logger;


use Exception;
use siteguard\security\system\System;


/**
 * Class ErrorLogger
 *
 * @package siteguard\security\logger
 */
class ExceptionLogger extends Logger {
	/**
	 * Init the php error logger
	 *
	 * @param ExceptionLogger $self This variable is $this ($this is not usable in closure until php 5.4)
	 *
	 * @return callable
	 */
	public function register( $self ) {
		return set_exception_handler( function ( $e ) use ( $self ) {
			/** @var Exception $e */
			try {
				$self->log( $e->getCode(), get_class( $e ), $e->getMessage(), $e->getFile(), $e->getLine() );

				/** @var ErrorLogger $error_logger */
				$error_logger = Logger::getLogger( "Error" );
				if ( ! is_null( $error_logger ) ) {
					$error_logger->doNotLog( $e->getFile(), $e->getLine() );
				}
			}
			catch ( Exception $ignore ) {
			}

			if ( ! is_null( $self->old_hook ) ) {
				call_user_func( $self->old_hook, $e );
			} else {
				throw $e;
			}
		} );
	}


	/**
	 * @param int    $cod  The error code of the exception.
	 * @param string $type The class name of the exception.
	 * @param string $msg  The exception message.
	 * @param string $file The filename where the exception was thrown.
	 * @param int    $line The line number where the exception was thrown.
	 */
	private function log( $cod, $type, $msg, $file, $line ) {
		$path = System::getRelativeToWpRootPath( $file );

		$this->load(
			array( $cod, $type, $path, $line ),
			"{$type}({$cod}): {$msg}", "{$path}:{$line}",
			$post_id, $data
		);

		$this->addBaseInfo( $data );

		$this->addPluginInfo( $data, $file );

		$data["cod"]  = $cod;
		$data["type"] = $type;
		$data["msg"]  = $msg;
		$data["file"] = $path;
		$data["line"] = $line;

		$this->save( $post_id, $data );
	}
}
