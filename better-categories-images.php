<?php
/**
 * Plugin Name: Better Categories Images
 * Plugin URI: https://namncn.com/plugins/better-categories-images/
 * Description: The Better Categories Images Plugin allow you to add image with any category or taxonomy.
 * Version: 1.0.3
 * Author: Nam Truong
 * Author URI: https://namncn.com
 *
 * Text Domain: better-categories-images
 * Domain Path: /languages/
 *
 * @package Better Categories Images
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define.
define( 'BCI_VERSION', '1.0.3' );
define( 'BCI_FILE', __FILE__ );
define( 'BCI_PATH', plugin_dir_path( BCI_FILE ) );
define( 'BCI_URL', plugin_dir_url( BCI_FILE ) );
define( 'BCI_MODULES_PATH', BCI_PATH . 'modules/' );
define( 'BCI_ASSETS_URL', BCI_URL . 'assets/' );
define( 'BCI_IMAGE_PLACEHOLDER', BCI_ASSETS_URL . 'images/placeholder.png' );

require_once BCI_PATH . '/includes/class-better-categories-images.php';

/**
 * Main instance of Better_Category_Images.
 *
 * Returns the main instance of BCI to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Better_Category_Images
 */
function bci() {
	return Better_Category_Images::instance();
}

// Global for backwards compatibility.
$GLOBALS['bci'] = bci();
