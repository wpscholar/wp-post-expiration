<?php

if ( ! function_exists( 'wpscholar_post_expiration_initialize' ) ) {

	/**
	 * Setup function
	 */
	function wpscholar_post_expiration_initialize() {
		add_action( 'init', [ '\wpscholar\WordPress\PostExpiration', 'initialize' ], 1000 );
	}

	// Only call our setup function automatically if we are in a plugin or theme context
	if ( function_exists( 'add_action' ) ) {
		wpscholar_post_expiration_initialize();
	}

}