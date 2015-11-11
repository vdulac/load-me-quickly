<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Load Me Quickly Static Files Generator
 *
 * @package Load_Me_Quickly
 */
class Load_Me_Quickly_Static_Files {

	
	const TIMEOUT = 3000;

	public function __construct() {

	}

	public function save_post_to_file($post_id) {
		
		$post_url = get_permalink( $post_id );
		// No need for realpath() since windows/linux all recognize / & \
		$file_path = WP_PLUGIN_DIR . '/' . Load_Me_Quickly::SLUG . '/' . Load_Me_Quickly::STATIC_FILES_DIR_NAME . str_replace( home_url(), "", $post_url);
		
		//file_put_contents( 'D:\Websites\log.txt', $post_url );

		$content = wp_remote_get( $post_url .'?swm_static=false', array(
			'timeout' => self::TIMEOUT,
			'sslverify' => false, // not verifying SSL because all calls are local
			'redirection' => 0 // disable redirection
		) );

		$content = $content['body'] . '<!-- Load Me Quickly Static Version -->';

		if (wp_mkdir_p($file_path)) {
		  file_put_contents( $file_path . 'index.html', $content );
		}
		
		return;

	}

}