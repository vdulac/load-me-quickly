<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin Name: 	Load Me Quickly
 * Plugin URI: 		http://www.solidwebmedia.com
 * Description: 	Create static HTML copy of selected posts & pages (uses htaccess mod_rewrites).
 * Version: 		1.1.1
 * Author: 			Vincent Dulac
 * Author URI: 		http://www.solidwebmedia.com
 * License:         GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:     load-me-quickly
 * Domain Path:     /languages
 */

require plugin_dir_path( __FILE__ ) . 'includes/class-load-me-quickly.php';

Load_Me_Quickly::init( __FILE__ );