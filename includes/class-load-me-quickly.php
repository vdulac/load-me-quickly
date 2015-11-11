<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Load Me Quickly Static HTML Generator
 *
 * @package Load_Me_Quickly
 */
class Load_Me_Quickly {
	
	const VERSION = '1.1.1';
	const SLUG = 'load-me-quickly';
	const STATIC_FILES_DIR_NAME = 'static-files';
	const SINGLE_POST_STATIC_FIELD_NAME = 'swm_static_field'; // Also needs to be changed in uninstall.php

	protected static $instance = null; // Singleton instance
	protected $options = null; // Contains all options for this plugin
	protected $view = null; // View object

	protected $admin = null;
	protected $static_files = null;
	protected $htaccess = null;

	protected function __construct() {} // Disable usage of "new"
	protected function __clone() {} // Disable cloning of the class
	public function __wakeup() {} // Disable unserializing of the class

	
	// Return an instance of the Load Me Quickly plugin
	public static function instance() {
		if ( null === self::$instance )	{
			self::$instance = new self();
			self::$instance->includes();
			self::$instance->options = new Load_Me_Quickly_Options( self::SLUG );
			self::$instance->view = new Load_Me_Quickly_View();

			self::$instance->admin = new Load_Me_Quickly_Admin();
			self::$instance->static_files = new Load_Me_Quickly_Static_Files();
			self::$instance->htaccess = new Load_Me_Quickly_Htaccess();

			// Load the text domain for i18n
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
			// Enqueue admin styles
			//add_action( 'admin_enqueue_scripts', array( self::$instance, 'enqueue_admin_styles' ) );
			// Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array( self::$instance, 'enqueue_admin_scripts' ) );
			// Add the options page and menu item.
			add_action( 'admin_menu', array( self::$instance, 'add_plugin_admin_menu' ), 2 );
		}

		return self::$instance;
	}


	//Initialize singleton instance
	public static function init( $bootstrap_file )
	{
		$instance = self::instance();

		// Activation
		register_activation_hook( $bootstrap_file, array( $instance, 'activate' ) );
		// Deactivation
		register_deactivation_hook( $bootstrap_file, array( $instance, 'deactivate' ) );

		return $instance;
	}


	public static function get_option($name) {
		return self::$instance->options->get( $name );
	}
	
	public static function write_file($post_id) {
		self::$instance->static_files->save_post_to_file($post_id);
	}

	//Activate the plugin
	public function activate()
	{
		// Not installed?
		if ( null === $this->options->get( 'version' ) ) {
			$this->options
				->set( 'version', self::VERSION )
				->set( 'gzip', '1' )
				->set( 'cache_static_assets', '1' )
				->save();
		}

		$this->options->set( 'plugin_activated', '1' )->save();

		self::add_all(true);

		flush_rewrite_rules();
	}

	
	//Include required files 
	private function includes() {
		//require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/libraries/phpuri.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-load-me-quickly-options.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-load-me-quickly-view.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-load-me-quickly-static-files.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-load-me-quickly-admin.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-load-me-quickly-htaccess.php';
		require plugin_dir_path( dirname( __FILE__ ) ) . 'includes/misc-functions.php';
	}


	//Enqueue admin-specific style sheets for this plugin's admin pages only
	//Return early if no settings page is registered.
	public function enqueue_admin_styles() {
		// Plugin admin CSS. Tack on plugin version.
		//wp_enqueue_style( self::SLUG . '-admin-styles', plugin_dir_url( dirname( __FILE__ ) ) . 'css/admin.css', array(), self::VERSION );
	}


	//Enqueue admin-specific javascript files for this plugin's admin pages only
	//Return early if no settings page is registered.
	public function enqueue_admin_scripts() {
		// Plugin admin CSS. Tack on plugin version.
		//wp_enqueue_script( self::SLUG . '-admin-styles', plugin_dir_url( dirname( __FILE__ ) ) . 'js/admin.js', array(), self::VERSION );
	}


	//Register the administration menu for this plugin into the WordPress Dashboard menu.
	public function add_plugin_admin_menu() {
		// add_menu_page(page-title, menu-title, capability, menu-slug, function, icon, position)
		add_menu_page(
			__( 'Load Me Quickly Settings', self::SLUG ),
			__( 'Load Me Quickly', self::SLUG ),
			'administrator',
			self::SLUG,
			array( self::$instance, 'load_me_quickly_settings_page' ),
			'dashicons-admin-generic'
			);
	}


	//Render the plugin settings page. 
	public function load_me_quickly_settings_page() {
		// if ( $this->check_system_requirements() ) {
		// 	$this->view->assign( 'system_requirements_check_failed', true );
		// }

		if ( isset( $_POST['save'] ) ) {
			$this->save_options();
			$message = __( 'Settings saved.', self::SLUG );
			$this->view->add_flash( 'updated', $message );
		} else if ( isset( $_POST['add_all'] ) ) {
			$this->add_all();
			$message = __( 'All static posts & pages have been enabled.', self::SLUG );
			$this->view->add_flash( 'updated', $message );
		} else if ( isset( $_POST['remove_all'] ) ) {
			$this->remove_all();
			$message = __( 'All static posts & pages have been disabled.', self::SLUG );
			$this->view->add_flash( 'updated', $message );
		}


		$this->view
			->set_layout( 'admin' )
			->set_template( 'settings' )
			->assign( 'slug', self::SLUG )
			//->assign( 'enable_static_files', $this->options->get( 'enable_static_files' ) )
			->assign( 'gzip', $this->options->get( 'gzip' ) )
			->assign( 'cache_static_assets', $this->options->get( 'cache_static_assets' ) )
			->render();
	}

	//Save the options from the options page.
	public function save_options() {
		$this->options
			//->set( 'enable_static_files', filter_input( INPUT_POST, 'enable_static_files' ))
			->set( 'gzip', filter_input( INPUT_POST, 'gzip' ))
			->set( 'cache_static_assets', filter_input( INPUT_POST, 'cache_static_assets' ))
			->save();

			flush_rewrite_rules();
	}


	public function add_all($activation_phase = false) {

		//Check if WPML is installed and activated
		if ( function_exists('icl_object_id') ) {
	     	global $sitepress;

	     	$languages = icl_get_languages('skip_missing=1');

	     	foreach($languages as $l) {
	     		$sitepress->switch_lang($l['code'], true);
	     		self::add_all_loop(true, $activation_phase);
    		}

		} else {
			self::add_all_loop(false, $activation_phase);
		}

		flush_rewrite_rules();
	}


	public function add_all_loop($wpml = false, $activation_phase = false) {
		if ($wpml) {
			if ($activation_phase) {
				$args = array('post_type' => array( 'post', 'page'),
					  		  'posts_per_page' => -1,
					  		  'meta_key'   => self::SINGLE_POST_STATIC_FIELD_NAME,
		   			  		  'meta_value' => 1,
					  		  'suppress_filters' => false // Only get current language posts & pages
		 			  		  );
			} else {
				$args = array('post_type' => array( 'post', 'page'),
					  'posts_per_page' => -1,
					  'suppress_filters' => false // Only get current language posts & pages
		 			  );
			}
		} else {
			if ($activation_phase) {
				$args = array('post_type' => array( 'post', 'page'),
					  		  'posts_per_page' => -1,
					  		  'meta_key'   => self::SINGLE_POST_STATIC_FIELD_NAME,
		   			  		  'meta_value' => 1
		 			  		  );
			} else {
				$args = array('post_type' => array( 'post', 'page'),
					  		  'posts_per_page' => -1
		 			  		  );
			}
		}
		$posts_and_pages = get_posts($args);

		foreach($posts_and_pages as $post_or_page) : 
			update_post_meta($post_or_page->ID, self::SINGLE_POST_STATIC_FIELD_NAME, 1);
			$this->static_files->save_post_to_file($post_or_page->ID);
		endforeach;
	}

	public function remove_all() {
		
		$args = array('post_type'  => array( 'post', 'page'),
					  'posts_per_page' => -1,
					  'meta_key'   => self::SINGLE_POST_STATIC_FIELD_NAME,
		   			  'meta_value' => 1
		   			  );
		$posts_and_pages = get_posts($args);

		foreach($posts_and_pages as $post_or_page) : 
			update_post_meta($post_or_page->ID, self::SINGLE_POST_STATIC_FIELD_NAME, 0);
			//$this->static_files->save_post_to_file($post_or_page->ID);
		endforeach;

		flush_rewrite_rules();
	}






	//Loads the plugin language files
	public function check_system_requirements() {
		$errors = array();

		// $destination_host = $this->options->get( 'destination_host' );
		// if ( strlen( $destination_host ) === 0 ) {
		// 	$errors['destination_host'][] = __( 'Destination URL cannot be blank', self::SLUG );
		// }

		// $temp_files_dir = $this->options->get( 'temp_files_dir' );
		// if ( strlen( $temp_files_dir ) === 0 ) {
		// 	$errors['temp_files_dir'][] = __( 'Temporary Files Directory cannot be blank', self::SLUG );
		// } else {
		// 	if ( file_exists( $temp_files_dir ) ) {
		// 		if ( ! is_writeable( $temp_files_dir ) ) {
		// 			$errors['delivery_method'][] = sprintf( __( 'Temporary Files Directory is not writeable: %s', self::SLUG ), $temp_files_dir );
		// 		}
		// 	} else {
		// 		$errors['delivery_method'][] = sprintf( __( 'Temporary Files Directory does not exist: %s', self::SLUG ), $temp_files_dir );
		// 	}
		// }


		// if ( strlen( get_option( 'permalink_structure' ) ) === 0 ) {
		// 	$errors['permalink_structure'][] = sprintf( __( "Your site does not have a permalink structure set. You can select one on <a href='%s'>the Permalink Settings page</a>.", self::SLUG ), admin_url( '/options-permalink.php' ) );
		// }

		// if ( $this->options->get( 'delivery_method' ) == 'zip' ) {
		// 	if ( ! extension_loaded('zip') ) {
		// 		$errors['delivery_method'][] = __( "Your server does not have the PHP zip extension enabled. Please visit <a href='http://www.php.net/manual/en/book.zip.php'>the PHP zip extension page</a> for more information on how to enable it.", self::SLUG );
		// 	}
		// }

		// if ( $this->options->get( 'delivery_method' ) == 'local' ) {
		// 	$local_dir = $this->options->get( 'local_dir' );

		// 	if ( strlen( $local_dir ) === 0 ) {
		// 		$errors['delivery_method'][] = __( 'Local Directory cannot be blank', self::SLUG );
		// 	} else {
		// 		if ( file_exists( $local_dir ) ) {
		// 			if ( ! is_writeable( $local_dir ) ) {
		// 				$errors['delivery_method'][] = sprintf( __( 'Local Directory is not writeable: %s', self::SLUG ), $local_dir );
		// 			}
		// 		} else {
		// 			$errors['delivery_method'][] = sprintf( __( 'Local Directory does not exist: %s', self::SLUG ), $local_dir );
		// 		}
		// 	}
		// }

		return $errors;
	}


	//Loads the plugin language files 
	public function load_textdomain() {
		load_plugin_textdomain(
			self::SLUG,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	// Deactivate the plugin
	public function deactivate() {
		$this->options->set( 'plugin_activated', '0' )->save();;
		flush_rewrite_rules();
	}


}