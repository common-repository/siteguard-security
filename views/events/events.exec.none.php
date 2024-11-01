<?php defined( "ABSPATH" ) or die();

\siteguard\security\system\Dispatcher::redirectToCurrentPage( \siteguard\security\system\Input::readArray( array(
	"orderby" => "",
	"order"   => "",
	"paged"   => "1",
	"s"       => "",
	"t"       => "",
) ) );
