<?php defined( "ABSPATH" ) or die();

\siteguard\security\system\Dispatcher::doAction( "event", "reset", function ( $event ) {
	return \siteguard\security\model\EventManager::reset( $event );
}, array(
	"orderby" => "",
	"order"   => "",
	"paged"   => "1",
	"s"       => "",
	"t"       => "",
) );
