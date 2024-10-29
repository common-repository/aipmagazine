<?php
/**
 * Main PHP file used to for initial calls to AipMagazine's classes and functions.
 *
 * @package AipMagazine
 */
 
/*
Plugin Name: AipMagazine
Plugin URI:
Description: A feature rich magazine and newspaper issue manager plugin for WordPress.
Author: Archimede Informatica Development Team
Version: 1.0.0
Author URI: http://www.archicoop.it/
Tags: 
*/

/**
 * Defined constants
 *
 * @since 1.2.0
 */

define( 'AIPMAGAZINE_SLUG', 			'aip_magazine' );
define( 'AIPMAGAZINE_VERSION', 		'2.0.3' );
define( 'AIPMAGAZINE_DB_VERSION', 	'1.0.0' );
define( 'AIPMAGAZINE_URL', 			plugin_dir_url( __FILE__ ) );
define( 'AIPMAGAZINE_PATH', 			plugin_dir_path( __FILE__ ) );
define( 'AIPMAGAZINE_BASENAME', 		plugin_basename( __FILE__ ) );
define( 'AIPMAGAZINE_REL_DIR', 		dirname( AIPMAGAZINE_BASENAME ) );

/**
 * Instantiate AipMagazine class, require helper files
 *
 * @since 1.2.0
 */
function aip_magazine_plugins_loaded() {

	require_once( 'aip_magazine-class.php' );

	// Instantiate the Pigeon Pack class
	if ( class_exists( 'AipMagazine' ) ) {
		
		global $dl_plugin_aip_magazine;
		
		$dl_plugin_aip_magazine = new AipMagazine();
		$aip_magazine_settings = $dl_plugin_aip_magazine->get_settings();


        require_once( 'aip_magazine-post-type.php' );
        require_once( 'aip_magazine-jouls-taxonomy.php' );

        require_once( 'aip_magazine-taxonomy.php' );
        require_once( 'aip_magazine-functions.php' );


	    require_once( 'aip_magazine-cats-taxonomy.php' );
        require_once( 'aip_magazine-tags-taxonomy.php' );


		require_once( 'aip_magazine-shortcodes.php' );
		require_once( 'aip_magazine-widgets.php' );
		require_once( 'aip_magazine-feeds.php' );
			
		//Internationalization
		load_plugin_textdomain( 'aip_magazine', false, AIPMAGAZINE_REL_DIR . '/languages/' );
		
		do_action( 'aip_magazine_loaded' );
			
	}

}
add_action( 'plugins_loaded', 'aip_magazine_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init
