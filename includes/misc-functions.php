<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Miscellaneous functions for use across the plugin
 *
 * @package Load_Me_Quickly
 */


/**
 * Echo the "checked" value for an option tag if the statement is true.
 *
 * @return null
 */
function swm_checked_if( $statement ) {
	echo ( $statement == true ? 'checked="checked"' : '' );
}
