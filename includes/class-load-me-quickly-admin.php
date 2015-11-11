<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Load Me Quickly Admin Area Options
 *
 * @package Load_Me_Quickly
 */
class Load_Me_Quickly_Admin {

	public function __construct() {
		
		//Add the 'Static' header column for list posts & pages
		add_filter( 'manage_post_posts_columns', array( $this, 'show_static_column' ) );
		add_filter( 'manage_pages_columns', array( $this, 'show_static_column' ) );

		// Shows the 'Static' data for posts & pages
		add_action( 'manage_posts_custom_column', array( $this, 'show_static_data_column' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'show_static_data_column' ) );

		//Add 'Static' checkbox option for single posts & pages
		add_action( 'post_submitbox_misc_actions', array( $this, 'add_checkbox_static_field_to_single_posts' ) );
		//Save 'Static' checkbox value
		add_action( 'save_post', array( $this, 'save_checkbox_postdata' ) );
	}


	public function show_static_column($columns) {
	    $columns['static'] = __( 'Load Me Quickly', Load_Me_Quickly::SLUG );
	    return $columns;
	}

	public function show_static_data_column($name) {
	    global $post;
	    switch ($name) {
	        case 'static':
	            $static_field = get_post_meta($post->ID, Load_Me_Quickly::SINGLE_POST_STATIC_FIELD_NAME, true);

	            if ($static_field == 1) {
	            	?>
	            	<img src="<?php echo (WP_PLUGIN_URL . '/' . Load_Me_Quickly::SLUG); ?>/img/check-mark.png" width="16" height="16" alt="Load Me Quickly" title="<?php _e( 'A static HTML copy of this post will be served.', Load_Me_Quickly::SLUG ); ?>" style="margin:4px 0 0 10px">
					<?php

	            } 
	    }
	}
	

	//http://stackoverflow.com/questions/9907858/how-to-add-a-field-in-edit-post-page-inside-publish-box-in-wordpress
	public function add_checkbox_static_field_to_single_posts()
	{
	    global $post;

	    /* check if this is a post, if not then we won't add the custom field */
	    /* change this post type to any type you want to add the custom field to */
	    if (get_post_type($post) != 'page' && get_post_type($post) != 'post') return false;

	    /* get the current value of the custom field */
	    $value = get_post_meta($post->ID, Load_Me_Quickly::SINGLE_POST_STATIC_FIELD_NAME, true);

	    ?>
	        <div class="misc-pub-section">	            
	            <label><input type="checkbox"<?php echo ((!empty($value) && $value == 1) ? ' checked="checked"' : null) ?> value="1" name="static_field_input" /> <?php _e( 'Load Me Quickly', Load_Me_Quickly::SLUG ); ?></label>
	        </div>
	    <?php
	}
	
	public function save_checkbox_postdata( $post_id ) {
		/* check if this is an autosave */
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;

	    /* check if this is a revision */
	    if ( wp_is_post_revision( $post_id ) ) return false;

	    /* check if the user can edit this page */
	    if ( !current_user_can( 'edit_page', $post_id ) ) return false;

	    /* check if there's a post id and check if this is a post */
	    /* make sure this is the same post type as above */
	    if( empty($post_id) || !isset($_POST['post_type']) || ($_POST['post_type'] != 'page' && $_POST['post_type'] != 'post') ) return false;

		/* check if the custom field is submitted (checkboxes that aren't marked, aren't submitted) */
	    if(isset($_POST['static_field_input'])){
	        /* store the value in the database */
	        update_post_meta($post_id, Load_Me_Quickly::SINGLE_POST_STATIC_FIELD_NAME, 1);
	        
	        Load_Me_Quickly::write_file( $post_id );

	    } else {
	        /* not marked? delete the value in the database */
	        update_post_meta($post_id, Load_Me_Quickly::SINGLE_POST_STATIC_FIELD_NAME, 0);
	    }

	    flush_rewrite_rules();

	}
	
}