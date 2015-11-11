<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Load_Me_Quickly_Options {

	//Options array
	protected $options = array();

	//Defines options record in the wp_options table
	protected $option_key = null;


	//Performs initializion of the options structure
	public function __construct( $option_key ) {
		$options = get_option( $option_key );

		if ( false === $options ) {
			$options = array();
		}

		$this->options = $options;
		$this->option_key = $option_key;
	}


	//Updates the option identified by $name with the value provided in $value
	public function set( $name, $value ) {
		$this->options[$name] = $value;
		return $this;
	}


	//Returns a value of the option identified by $name 
	public function get( $name ) {
		return array_key_exists( $name, $this->options ) ? $this->options[$name] : null;
	}


	//Saves the internal options data to the wp_options table using the stored $option_key value as the key 
	public function save() {
		return update_option( $this->option_key, $this->options );
	}

	public function delete() {
		delete_option( $this->option_key );
		$this->$options = array();
	}
}
