<?php defined( "ABSPATH" ) or die();

\siteguard\security\system\Dispatcher::doAction( "event", "delete", function ( $event ) {
	return \siteguard\security\model\EventManager::delete( $event );
}, array(
	"orderby" => "",
	"order"   => "",
	"paged"   => "1",
	"s"       => "",
	"t"       => "",
) );
