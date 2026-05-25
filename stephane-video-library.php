<?php
/**
 * Plugin Name:       Stephane Video Library
 * Plugin URI:        https://github.com/
 * Description:       Adds a video content type and Elementor widgets for Stephane's video gallery.
 * Version:           1.0.0
 * Author:            myDev
 * Text Domain:       stephane-video-library
 * Domain Path:       /languages
 *
 * @package StephaneVideoLibrary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STVL_VERSION', '1.0.0' );
define( 'STVL_FILE', __FILE__ );
define( 'STVL_PATH', plugin_dir_path( __FILE__ ) );
define( 'STVL_URL', plugin_dir_url( __FILE__ ) );
define( 'STVL_BASENAME', plugin_basename( __FILE__ ) );
define( 'STVL_CPT', 'st_video' );
define( 'STVL_TAX_CATEGORY', 'st_video_category' );
define( 'STVL_TAX_TOPIC', 'st_video_topic' );

require_once STVL_PATH . 'includes/class-loader.php';
require_once STVL_PATH . 'includes/class-helpers.php';
require_once STVL_PATH . 'includes/class-plugin.php';

/**
 * Bootstraps the plugin singleton.
 *
 * @return STVL_Plugin
 */
function stvl() {
	return STVL_Plugin::instance();
}

register_activation_hook( STVL_FILE, array( 'STVL_Plugin', 'activate' ) );
register_deactivation_hook( STVL_FILE, array( 'STVL_Plugin', 'deactivate' ) );

stvl();
