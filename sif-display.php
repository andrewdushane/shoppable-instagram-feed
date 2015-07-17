<?php 

/**
 * Create shortcode for feed display
 * 
 */
function sif_create_display() {
    $sif_profile      = get_option( 'sif_options_access_token' );
    $sif_username     = esc_html($sif_profile['username']);
    $sif_full_name    = esc_html($sif_profile['full_name']);  
    $sif_profile_img  = esc_html($sif_profile['profile_picture']);
    $sif_bio          = esc_html($sif_profile['bio']);
    $output  = '<div id="sif-feed-container" class="sif-feed-container">';
    $output .= '<div class="sif-user-profile">';
    $output .= '<img src="' . $sif_profile_img . '" alt="Profile Picture" width="140" height="140">';
    $output .= '<div class="sif-profile-text">';
    $output .= '<p class="sif-full-name">' . $sif_full_name . '<br><span class="sif-username">@' . $sif_username . '</span></p>';
    $output .= '<p class="sif-bio">' . $sif_bio . '</p>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<div class="sif-info">';
    $output .= '<p><span style="font-size: 1.25em;">Just click an image to shop!</span><br>';
    $output .= '<a href="http://instagram.com/_u/' . $sif_username . '/">&#8592; Back to Instagram</a></p>';
    $output .= '</div>';
    $output .= '<div id="sif-feed" class="sif-feed">'; //AJAX IG feed goes here
    $output .= '</div>';
    $output .= '<button class="sif-load-more" id="sif-load-more-button">Load More</button>';
    $output .= '<div class="sif-ajax-loading" style="display:none;">';
    $output .= '<img src="' . plugins_url( "/images/ajax-loader.gif" , __FILE__ ) . '" alt="loading" width="16" height="16"></div>';
    $output .= '</div>';
    return $output;
}
add_shortcode( 'sif_display', 'sif_create_display' );

/**
 * Enqueue AJAX load more script
 * 
 */
function sif_load_more_ajax( $hook ) {
	wp_enqueue_script( 'sif-load-more', plugins_url( '/js/load-more.js', __FILE__ ), array('jquery') );
    $total_slices = ceil( count( get_option( 'sif_display_feed' ) ) / 20 ); // Total images to display divided by 20
	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'sif-load-more', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'total_slices' => $total_slices ) ); // 
}
add_action( 'wp_enqueue_scripts', 'sif_load_more_ajax' );

/**
 * Font!
 * 
 */
function sif_include_montesserat() {
    echo "<link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>";
}
add_action( 'wp_head' , 'sif_include_montesserat' );

/**
 * Take slice of feed
 * 
 */
function sif_slice_display( $feed , $slice ) {
    $limit = 20;
    $offset = $slice * $limit;
    $feed = array_slice( $feed , $offset , $limit );
    return $feed;
}

/**
 * Create HTML for feed to pass to load more script
 * 
 */
function sif_load_feed_slices( $feed , $slice ) {
    if ( is_array ( $feed ) ) {
        $feed = sif_slice_display( $feed , $slice );
        $output = '';
        foreach ( $feed as $image ) {
            if ( $image['enabled'] == '1' ) {
                $link = $image['link'];
                $output  .= '<div class="sif_display_image_block">';
                if( $link != '' ) {
                    $output .= '<a href="' . $link . '">';
                }
                $output .= '<img src="' . $image['image_url_highres'] . '" alt="" width="640" height="640">';
                if( $link != '' ) {
                    $output .= '</a>';
                }
                $output .= '</div>';
            }
        } echo $output;
    }
}

/**
 * AJAX load more callback function
 * 
 */
function sif_ajax_feed() {
	global $wpdb; // Access the WP database through AJAX call
    $feed = get_option( 'sif_display_feed' );
    $slice = 0;
    if( isset( $_POST ) && is_array( $_POST ) && array_key_exists( 'slice' , $_POST ) ) {
        $slice = intval( esc_html( $_POST['slice']) );
    }
    sif_load_feed_slices( $feed , $slice );
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_sif_feed', 'sif_ajax_feed' );
add_action( 'wp_ajax_nopriv_sif_feed', 'sif_ajax_feed' );
