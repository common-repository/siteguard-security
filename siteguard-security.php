<?php defined( "ABSPATH" ) or die();


/*
Plugin Name: Siteguard Security
Description: Logs and displays failed login, error and exception to help you, with detailed information, to supervise what happens on your site.
Version:     1.0.0
Author:      Siteguard
Author URI:  http://siteguard.network
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages/
Text Domain: siteguard-security
*/


/* define the class loader for siteguard */
spl_autoload_register( function ( $class_name ) {
	if ( substr( $class_name, 0, 19 ) === "siteguard\\security\\" ) {
		$file = realpath( __DIR__ . "/library/" . substr( $class_name, 19 ) . ".php" );
		if ( $file !== false ) {
			/** @noinspection PhpIncludeInspection */
			require_once $file;
		}
	}
} );


// import used classes
use siteguard\security\logger\Logger;
use siteguard\security\model\EventManager;
use siteguard\security\system\Dispatcher;
use siteguard\security\system\System;


/* init the system and register the loggers */
add_action( "init", "siteguard_security_init", 0 );
function siteguard_security_init() {
	System::init( __FILE__ );
	Logger::init( array(
		"Error",
		"Exception",
		"Login",
	) );
}


/* configure the menu */
add_action( "admin_menu", "siteguard_security_admin_menu" );
function siteguard_security_admin_menu() {
	$today_num     = EventManager::getTodayEventNum();
	$num_info_html = $today_num > 0
		? sprintf( " <span class='update-plugins count-{$today_num}' title='%s'><span class='update-count'>{$today_num}</span></span>",
			esc_html__( "Number of events occurred today", "siteguard-security" )
		)
		: "";


	add_menu_page(
		esc_html__( "Siteguard events", "siteguard-security" ), esc_html__( "Siteguard", "siteguard-security" ) . $num_info_html,
		"manage_options",
		"siteguard-security-events",
		"siteguard_security_view_events_dispatch",
		"dashicons-shield"
	);


	$edit = add_submenu_page(
		"siteguard-security-events",
		esc_html__( "Siteguard events", "siteguard-security" ), esc_html__( "Events", "siteguard-security" ),
		"manage_options",
		"siteguard-security-events",
		"siteguard_security_view_events_dispatch"
	);
	add_action( "load-{$edit}", "siteguard_security_view_events_load" );
}


function siteguard_security_view_events_dispatch() {
	Dispatcher::dispatch( "events" );
}


function siteguard_security_view_events_load() {
	Dispatcher::load( "events" );
}


/* add and configure the siteguard dashboard */
add_action( "admin_enqueue_scripts", "siteguard_security_wp_render_dashboard_widget_load" );
add_action( "wp_dashboard_setup", "siteguard_security_wp_dashboard_setup" );
function siteguard_security_wp_dashboard_setup() {
	wp_add_dashboard_widget(
		"siteguard-security-dashboard",
		esc_html__( "Siteguard Security", "siteguard-security" ),
		"siteguard_security_wp_render_dashboard_widget_dispatch"
	);
}


function siteguard_security_wp_render_dashboard_widget_dispatch() {
	Dispatcher::dispatch( "dashboard" );
}


function siteguard_security_wp_render_dashboard_widget_load() {
	Dispatcher::load( "dashboard" );
}
