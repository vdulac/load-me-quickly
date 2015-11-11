<?php
/**
 * Uninstall Load Me Quickly
 *
 * @package Load Me Quickly
 */

// exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Delete Load Me Quickly's settings
delete_option( 'load-me-quickly' );

// Remove field 'swm_static_field' from database table 'post_meta'
$args = array('post_type' => array( 'post', 'page'),
			  'posts_per_page' => -1,
			  'meta_key'   => 'swm_static_field'
			  );
$posts_and_pages = get_posts($args);

foreach($posts_and_pages as $post_or_page) :
	delete_post_meta($post_or_page->ID, 'swm_static_field');
endforeach;


// Rewrite .htaccess
flush_rewrite_rules();
