<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Load Me Quickly Htaccess Editor
 *
 * @package Load_Me_Quickly
 */
class Load_Me_Quickly_Htaccess {

	public function __construct() {

		add_filter('mod_rewrite_rules', array( $this, 'add_htaccess_redirects' ) );
		add_filter('mod_rewrite_rules', array( $this, 'add_gzip' ) );
		add_filter('mod_rewrite_rules', array( $this, 'add_cache_static_assets' ) );
	}

	public function add_htaccess_redirects( $rules ){
		
		if ( Load_Me_Quickly::get_option( 'plugin_activated' ) == 1 ) {

			$new_rules = '';

			//Check if WPML is installed and activated
			if ( function_exists('icl_object_id') ) {
		     	global $sitepress;

		     	$languages = icl_get_languages('skip_missing=1');

		     	foreach($languages as $l) {
		     		$sitepress->switch_lang($l['code'], true);
		     		$new_rules .= self::add_htaccess_redirects_loop(true);
	    		}
	    		
			} else {
				$new_rules .= self::add_htaccess_redirects_loop(false);
			}

			
			if ($new_rules != '') {
				$rules = '

# Load Me Quickly
<IfModule mod_rewrite.c>
RewriteEngine on' . $new_rules . '
</IfModule>
# /Load Me Quickly

' . $rules;

			}

		}
			
		return $rules;

	}


	public function add_htaccess_redirects_loop($wpml = false) {
		if ($wpml) {
			$args = array('post_type' => array( 'post', 'page'),
						  'posts_per_page' => -1,
						  'meta_key'   => Load_Me_Quickly::SINGLE_POST_STATIC_FIELD_NAME,
		   				  'meta_value' => 1,
		   				  'suppress_filters' => false // Only get current language posts & pages
		   				  );
		} else {
			$args = array('post_type' => array( 'post', 'page'),
						  'posts_per_page' => -1,
						  'meta_key'   => Load_Me_Quickly::SINGLE_POST_STATIC_FIELD_NAME,
		   				  'meta_value' => 1
		   				  );
		}

		$posts_and_pages = get_posts($args);

		$loop_rules = '';

		foreach($posts_and_pages as $post_or_page) : 

			$link = str_replace( home_url() . '/', "", get_permalink( $post_or_page ));

			$loop_rules = $loop_rules . '
RewriteCond %{QUERY_STRING} ^$
RewriteRule ^' . $link . '$ wp-content/plugins/' . Load_Me_Quickly::SLUG . '/' . Load_Me_Quickly::STATIC_FILES_DIR_NAME . '/' . $link . 'index.html [L]';
		endforeach;

    	return $loop_rules;

	}



	public function add_gzip($rules) {
		//$option_gzip = $this->gzip; 
		
		if (Load_Me_Quickly::get_option( 'plugin_activated' ) == 1 && Load_Me_Quickly::get_option( 'gzip' ) == 1) {
			return $rules . "
# Load Me Quickly
<IfModule mod_deflate.c>
AddOutputFilterByType DEFLATE text/plain text/html text/javascript text/css text/xml application/xml application/xhtml+xml application/rss+xml application/javascript application/x-javascript
BrowserMatch ^Mozilla/4 gzip-only-text/html
BrowserMatch ^Mozilla/4\.0[678] no-gzip
BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
BrowserMatch \bMSI[E] !no-gzip !gzip-only-text/html
SetEnvIfNoCase Request_URI \.(?:gif|jpe?g|png|swf)$ no-gzip dont-vary
Header append Vary User-Agent env=!dont-vary
</IfModule>
# /Load Me Quickly

	";
		} else {
			return $rules;
		}
	}
	

	public function add_cache_static_assets($rules) {
		
		if (Load_Me_Quickly::get_option( 'plugin_activated' ) == 1 && Load_Me_Quickly::get_option( 'cache_static_assets' ) == 1) {
			return $rules . '
# Load Me Quickly
#1 Year
<FilesMatch "\.(ico|swf|unity3d)$">
Header set Cache-Control "max-age=29030400, public"
</FilesMatch>
#30 Days
<FilesMatch "\.(mp3|mp4|avi|mov|flv|mpeg|wmv)$">
Header set Cache-Control "max-age=2592000, public"
</FilesMatch>
#7 Days
<FilesMatch "\.(pdf|jpg|jpeg|png|gif)$">
Header set Cache-Control "max-age=604800, public"
</FilesMatch>
#7 Days
<FilesMatch "\.(js|css)$">
Header set Cache-Control "max-age=604800, public, must-revalidate"
</FilesMatch>
# /Load Me Quickly

	';
		} else {
			return $rules;
		}
	}


}