<?php defined( "WP_UNINSTALL_PLUGIN" ) or die();


delete_option( "siteguard-security" );


foreach ( array( "login", "error", "exception" ) as $type ) {
	$posts = get_posts( array(
		"posts_per_page" => - 1,
		"post_type"      => "siteguard_{$type}",
		"post_status"    => "any"
	) );

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
	}
}
