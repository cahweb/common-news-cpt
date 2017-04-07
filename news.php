<?php

/*
 *
 * Plugin Name: Common - News
 * Description: News CPT to be used with all CAH Wordpress Sites for news
 * Author: Alessandro Vecchi
 *
 */

/* Custom Post Type ------------------- */

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Load our CSS
function news_load_plugin_css() {
    wp_enqueue_style( 'news-plugin-style', plugin_dir_url(__FILE__) . 'css/style.css');
}
add_action( 'admin_enqueue_scripts', 'news_load_plugin_css' );

// Add create function to init
add_action('init', 'news_create_type');

// Create the custom post type and register it
function news_create_type() {
	$args = array(
	      'label' => 'News',
	        'public' => true,
	        'show_ui' => true,
	        'capability_type' => 'post',
	        'show_in_rest' => true,
	        'hierarchical' => false,
	        'rewrite' => array('slug' => 'news'),
			'menu_icon'  => 'dashicons-format-aside',
	        'query_var' => true,
	        'taxonomies' => array('category'),
	        'supports' => array(
	            'title',
	            'editor',
	            'excerpt',
	            'thumbnail')
	    );
	register_post_type( 'news' , $args );
}

add_action("admin_init", "news_init");
add_action( 'rest_api_init', 'slug_register_approved' );
add_action('save_post', 'news_save');

// Add the meta boxes to our CPT page
function news_init() {
	global $current_user;

    if($current_user->roles[0] == 'administrator') {
        add_meta_box("news-admin-meta", "Admin Only", "news_meta_admin", "news", "normal", "high");
    }
}

function slug_register_approved() {
    register_rest_field( 'news',
        'approved',
        array(
            'get_callback'    => 'slug_get_approved',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function slug_get_approved( $object, $field_name, $request ) {
    if(get_post_meta( $object[ 'id' ], $field_name, true ) == "on")
    	return "yes";
    
    else
    	return "no";
}

// Meta box functions
function news_meta_admin() {
	global $post; // Get global WP post var
    $custom = get_post_custom($post->ID); // Set our custom values to an array in the global post var

    // Form markup 
    include_once('views/admin.php');
}

// Save our variables
function news_save() {
	global $post;

	update_post_meta($post->ID, "approved", $_POST["approved"]);
}

// Settings array. This is so I can retrieve predefined wp_editor() settings to keep the markup clean
$settings = array (
	'sm' => array('textarea_rows' => 3),
	'md' => array('textarea_rows' => 6),
);


?>