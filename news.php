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

// Load our CSS if option is on
if(get_option('news_style_option') == "on") 
	add_action('wp_enqueue_scripts', 'add_style');

function add_style(){
	wp_register_style('news-style', plugins_url('css/style.css', __FILE__));
	wp_enqueue_style('news-style');
}
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
add_action('save_post', 'news_save');


/*----- API Meta Registration ----*/
add_action( 'rest_api_init', 'api_register_approved' );
add_action( 'rest_api_init', 'api_register_site_name' );
add_action( 'rest_api_init', 'api_register_featured_media' );

function api_register_approved() {
    register_rest_field( 'news',
        'approved',
        array(
            'get_callback'    => 'api_get_approved',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function api_get_approved( $object, $field_name, $request ) {
    if(get_post_meta( $object[ 'id' ], $field_name, true ) == "on")
    	return "yes";
    
    else
    	return "no";
}

function api_register_site_name() {
    register_rest_field( 'news',
        'site_name',
        array(
            'get_callback'    => 'api_get_site_name',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function api_get_site_name( $object, $field_name, $request ) {
    return get_option('blogname');
}

function api_register_featured_media() {
    register_rest_field( 'news',
        'featured_media',
        array(
            'get_callback'    => 'api_get_featured_media',
            'update_callback' => null,
            'schema'          => null,
        )
    );
}

function api_get_featured_media( $object, $field_name, $request ) {
    return wp_get_attachment_url($object["featured_media"]);
}


/*----- Shortcode Functions ------*/
add_shortcode('news', 'news_func');

function news_func($atts){
	$json = array();
	$urls = explode(";", get_option('news_list_option'));

	foreach($urls as $url){
		$file = file_get_contents("https://".$url."/wp-json/wp/v2/news");
		
		if(empty($file))
			return "One of the URLs entered is not a valid Wordpress API instance or does not have the CAH news plugin installed.";

		$result = json_decode($file);

		foreach($result as $post){
			$post->{"date"} = strtotime($post->{"date"});
		}

		$json = array_merge($result, $json);
	}


	usort($json, function($a, $b) {
		    if ($a->{"date"} == $b->{"date"}) {
		        return 0;
		    }
		    return ( $a->{"date"} > $b->{"date"}) ? -1 : 1;
		}
	);

	echo "<div class=\"cah-news\">";

	foreach($json as $post) {

		if($post->{"approved"} != "yes")
			continue;

		$title = $post->{"title"}->{"rendered"};
		$site_name = $post->{"site_name"};
		$excerpt = $post->{"excerpt"}->{"rendered"};
		$thumbnail = $post->{"featured_media"};
		$url = $post->{"link"};

		?>

			<div class="cah-news-article" onclick="location.href='<?=$url?>'">
			<?php
				if(!empty($thumbnail)):
			?>
				<div class="cah-news-thumbnail" style="background-image: url(<?=$thumbnail?>);""></div>
			<?php
				endif;
			?>
				<div class="cah-news-content">
					<h3 class="cah-news-site"><?=$site_name?></h3>
					<h2 class="cah-news-title"><?=$title?></h2>
					<div class="cah-news-excerpt"><?=$excerpt?></div>
				</div>
			</div>

		<?php

	}

	echo "</div>";
}

// add options
function news_list_option_register_settings() {
   add_option( 'news_list_option', 'on');
   register_setting( 'news_option_group', 'news_list_option', 'news_list_option_callback' );
}

function news_style_option_register_settings() {
   add_option( 'news_style_option', '');
   register_setting( 'news_option_group', 'news_style_option', 'news_style_option_callback' );
}

add_action( 'admin_init', 'news_list_option_register_settings' );
add_action( 'admin_init', 'news_style_option_register_settings' );



function news_list_register_option_page() {
  add_options_page('News Configuration', 'News Configuration', 'manage_options', 'news-list', 'news_list_option_page');
}
add_action('admin_menu', 'news_list_register_option_page');



function news_list_option_page() {
?>
  <div>
	  <h2>News Plugin Configuration</h2>
	  <p>Please enter the urls of each Wordpress Site you wish to pull news from.</p>
	  <p>(ex. arts.cah.ucf.edu,floridareview.cah.ucf.edu)</p>
	  <form method="post" action="options.php">
		  <?php settings_fields( 'news_option_group' ); ?>
		  <label for="news_list_option">URLs: </label>
		  <input type="text" id="news_list_option" name="news_list_option" value="<?php echo get_option('news_list_option'); ?>">
		  <br>

		  <label for="news_style_option">Use default style: </label>
		  <input type="checkbox" id="news_style_option" name="news_style_option" <?php 
		  	if(get_option('news_style_option') == "on")
		  		echo "checked=\"checked\"";
		  ?> />
		  <?php  submit_button(); ?>
	  </form>
  </div>
<?php
}



/*------ Metabox Functions --------*/
function news_init() {
	global $current_user;

    if($current_user->roles[0] == 'administrator') {
        add_meta_box("news-admin-meta", "Admin Only", "news_meta_admin", "news", "normal", "high");
    }
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