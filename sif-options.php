<?php
/**
 * Create admin menu entry
 */
function sif_admin_menu() {
	if( is_admin() ) {
		add_menu_page(
			'Shoppable Instagram Feed Settings',
			'Shoppable Instagram Feed',
			'manage_options',
			'shoppable-instagram-feed',
			'sif_options_page'
		);
	}
}
add_action( 'admin_menu', 'sif_admin_menu' );


function sif_options_page() {
	
    /**
	 * Delete all options & settings
	 * 
	 */
    
    //delete_option ( 'sif_options_feed' ); // Delete feed from options table. For debugging only.
    
    global $sif_update_message;
    global $sif_error_message;
    global $sif_pull_full_feed;
    $sif_pull_full_feed = '';
    
    function delete_sif_options( $submitted ) {   
        global $sif_update_message;
        $delete_options_submitted = esc_html( $submitted );
        if ( $delete_options_submitted == 'Y' ) {
            delete_option( 'sif_options_client' );
            delete_option( 'sif_options_access_token' );
            delete_option ( 'sif_options_feed' );
            delete_option ( 'sif_display_feed' );
            $sif_update_message = 'Options and settings deleted.';
        }
    }
    if ( isset( $_POST['delete_options_submitted'] ) ) {  
        $delete_submitted = esc_html($_POST['delete_options_submitted']);
        delete_sif_options( $delete_submitted );
    }
    
     /**
	 * Check that cURL is enabled on the server
	 * 
	 */
    function sif_curl_enabled() {
        return function_exists('curl_version');
    }
    $sif_curl_enabled = sif_curl_enabled();
    
    /**
	 * Set Instagram Redirect URI
	 * 
	 */
	$redirect_uri = admin_url( 'admin.php?page=shoppable-instagram-feed' );
    
    /**
	 * Functions to retreive options from the WP options table
	 * 
	 */
    
    function sif_get_ig_client_info() {
        $sif_options_client = get_option('sif_options_client');
        if ( is_array($sif_options_client) ) {
            $client['client_id'] = esc_html($sif_options_client['client_id']);
            $client['client_secret'] = esc_html($sif_options_client['client_secret']);
            return $client;
        } 
    }
    
    /**
	 * Store Client ID and Client Secret
     * Send user to Intagram for oAuth
	 */
    function sif_get_client_info( $submitted , $redirect_uri ) {   
        global $sif_update_message;
        global $sif_error_message;
        $client_submitted = esc_html( $submitted );
        if ( $client_submitted == 'Y' ) {
            if( isset( $_POST['client_id'] ) && $_POST['client_id'] != '' && isset( $_POST['client_secret'] ) && $_POST['client_secret'] != '' ) {
                $sif_options_client['client_id']     = esc_html($_POST['client_id']);
                $sif_options_client['client_secret'] = esc_html($_POST['client_secret']);
            }
            else {
                $sif_error_message = 'Please enter your Instagram Client ID and Client Secret';
                return;
            }
            update_option( 'sif_options_client' , $sif_options_client );
            //Quick and nasty Javascript redirect to Instagram oAuth
            $url = 'https://api.instagram.com/oauth/authorize/?client_id=' . $sif_options_client['client_id'] . '&redirect_uri=' . $redirect_uri . '&response_type=code';
            $redirect = '<script type="text/javascript">';
            $redirect .= 'window.location = "' . $url . '"';
            $redirect .= '</script>';
            echo $redirect;
        }
    }
    if ( isset( $_POST['client_submitted'] ) ) {  
        $client_submitted = esc_html($_POST['client_submitted']);
        sif_get_client_info( $client_submitted , $redirect_uri );
    }
    
     /**
	 * Show authorization form if reauthorize button submitted
     * 
	 */
    if ( isset( $_POST['sif_reauthorize'] ) ) {  
        $sif_reauthorize = esc_html($_POST['sif_reauthorize']);
    } else $sif_reauthorize = false;
    
    /**
	 * Get code from Instagram after sending for authorization
	 * 
	 */
	function sif_get_ig_code() {
		if ( isset( $_GET['code'] ) ) {
			$code = esc_html($_GET['code']);
		}
        else $code = 'no-code';
        return $code;
	}
	$ig_code = sif_get_ig_code();
    
    
    /**
	 * Request Access Token from Instagram after receiving code
	 * 
	 */
    function sif_request_access_token( $ig_code , $redirect_uri ) {
        global $sif_update_message;
        global $sif_error_message;
        global $sif_pull_full_feed;
        if( isset($ig_code) && $ig_code != 'no-code' ) {
            $url = "https://api.instagram.com/oauth/access_token";
            $client_info = sif_get_ig_client_info();
			$client_id = $client_info['client_id'];
            $client_secret = $client_info['client_secret'];
            $access_token_parameters = array(
                'client_id'                =>     $client_id,
                'client_secret'            =>     $client_secret,
                'grant_type'               =>     'authorization_code',
                'redirect_uri'             =>     $redirect_uri,
                'code'                     =>     $ig_code
            );

            $curl = curl_init($url);
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   // return the transfer as a string of the return value
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            curl_close($curl); 
            $access_token_response = json_decode($result,true);
            if( array_key_exists( 'error_type' , $access_token_response ) ) {
                $sif_error_message = 'There was a problem authenticating. Please try again.';
            }
            else {
                $sif_options_access_token['access_token']    = esc_html($access_token_response['access_token']);
                $sif_options_access_token['username']        = esc_html($access_token_response['user']['username']);
                $sif_options_access_token['bio']             = esc_html($access_token_response['user']['bio']);
                $sif_options_access_token['website']         = esc_html($access_token_response['user']['website']);
                $sif_options_access_token['profile_picture'] = esc_html($access_token_response['user']['profile_picture']);
                $sif_options_access_token['full_name']       = esc_html($access_token_response['user']['full_name']);
                $sif_options_access_token['id']              = esc_html($access_token_response['user']['id']);
                $sif_options_access_token['last_updated']    = time();
                update_option( 'sif_options_access_token' , $sif_options_access_token );
                $sif_update_message = 'Instagram Client successfully authenticated.';
                $sif_pull_full_feed = 'Y';
                //Redirect to admin page with no code appended
                $url = admin_url( 'admin.php?page=shoppable-instagram-feed' );
                $redirect = '<script type="text/javascript">';
                $redirect .= 'window.location = "' . $url . '"';
                $redirect .= '</script>';
                echo $redirect;
                
                return 'authorized';
            }
        }
    }
    $ig_authorized = sif_request_access_token( $ig_code , $redirect_uri );
    
    /**
	 * Get Access Token info for user badge and feed request
	 * 
	 */
    $sif_options_access_token = get_option( 'sif_options_access_token' );
    if( is_array( $sif_options_access_token ) ) {
        $sif_access_token        = $sif_options_access_token['access_token'];
        $sif_access_username     = $sif_options_access_token['username'];
        $sif_access_full_name    = $sif_options_access_token['full_name'];  
        $sif_access_profile      = $sif_options_access_token['profile_picture'];
        $sif_access_id           = $sif_options_access_token['id'];
        $sif_access_last_updated = get_date_from_gmt( date( 'Y-m-d H:i:s' , $sif_options_access_token['last_updated'] ) , 'l, F jS Y h:i:s A' );
    }
    
     /**
	 * Get feed for logged in user
	 * 
	 */
    function sif_get_ig_feed( $id , $token, $url ) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   // return the transfer as a string of the return value
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($curl, CURLOPT_TIMEOUT, 400);
        $result = curl_exec($curl);
        curl_close($curl); 
        $feed = json_decode($result,true);
        return $feed;
    }
    // TO DO: request increased rate limit? Only fetch recent images once feed is stored? Only do the while loop the first time?
    if ( isset ( $sif_access_token ) && $sif_access_token != '' ) {
        $sif_ig_url = 'https://api.instagram.com/v1/users/' . $sif_access_id . '/media/recent/?access_token=' . $sif_access_token;
        $sif_ig_feed_full = sif_get_ig_feed( $sif_access_id , $sif_access_token , $sif_ig_url );
        if( $sif_ig_feed_full['meta']['code'] == 200 ) {
            $sif_next_url = $sif_ig_feed_full['pagination']['next_url']; //For retreiving the next 20 images in the feed from IG
            $sif_ig_feed_images = $sif_ig_feed_full['data'];
            foreach ( $sif_ig_feed_images as $image ) {
                //Array of images in IG feed indexed by time created
                //Can't use integers for associative array, so add string 'id'
                $sif_ig_feed[('id' . $image['created_time'])] = array ( 
                    'image_url_lowres' => $image['images']['low_resolution']['url'],
                    'image_url_highres' => $image['images']['standard_resolution']['url'],
                    'enabled' => '',
                    'link' => ''
                );
            }
        }
        else {
            $sif_ig_error_message = $sif_ig_feed_full['error_message'];
            $sif_error_message = 'There was a problem retrieving your Instagram feed. Instagram says: ';
            $sif_error_message .= $sif_ig_error_message;
            $sif_reauthorize = 'Y';
        }
    }
    
    if( $sif_pull_full_feed == 'Y' ) {
        while ( isset ( $sif_next_url ) && $sif_next_url != '' ) {
            $sif_ig_feed_full = sif_get_ig_feed( $sif_access_id , $sif_access_token , $sif_next_url );
            if( $sif_ig_feed_full['meta']['code'] == 200 ) {
                $next_url_exists = $sif_ig_feed_full['pagination'];
                if ( array_key_exists( 'next_url' , $next_url_exists ) ) {
                    $sif_next_url = $sif_ig_feed_full['pagination']['next_url']; //For retreiving the next 20 images in the feed from IG
                } else $sif_next_url = '';
                $sif_ig_feed_images = $sif_ig_feed_full['data'];
                foreach ( $sif_ig_feed_images as $image ) {
                    //Array of images in IG feed indexed by time created
                    //Can't use integers for associative array, so add string 'id'
                    $sif_ig_feed[('id' . $image['created_time'])] = array ( 
                        'image_url_lowres' => $image['images']['low_resolution']['url'],
                        'image_url_highres' => $image['images']['standard_resolution']['url'],
                        'enabled' => '',
                        'link' => ''
                    );
                }
            }
            else {
                $sif_error_message = 'There was a problem retrieving your Instagram feed. From Instagram: ';
                $sif_error_message .= $sif_ig_feed_full['error_message'];
                $sif_reauthorize = 'Y';
            }
        }
    }
    
    
    /**
	 * Add new images to WP options
	 * @return array updated options
	 */
    function sif_update_options_feed( $ig_feed , $options_feed ) {
        if ( is_array ( $ig_feed ) && is_array ( $options_feed ) ) {
            $new_images = array_diff_key( $ig_feed , $options_feed );
            if ( is_array( $new_images ) ) {
                $options_feed = ( $options_feed + $new_images );
                krsort( $options_feed );
            } 
        } elseif ( is_array ( $ig_feed ) && !is_array ( $options_feed ) ) {
            $options_feed = $ig_feed;
        }
        update_option( 'sif_options_feed' , $options_feed );
        return $options_feed;
    }
    
    /**
	 * Paginate feed display
	 * Also used for updating settings for current page only
     * 
	 */
    $sif_pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; //Get page number, set to 1 if not present
    function sif_paginate_admin( $feed , $pagenum ) {
        $limit = 12;
        $offset = ( $pagenum - 1 ) * $limit;
        $feed = array_slice ( $feed , $offset , $limit );
        return $feed;
    }
    
    /**
	 * Update feed settings
	 * 
	 */
    function sif_submit_feed_options( $post ) {
        global $sif_update_message;
        global $sif_error_message;
        $enabled_options = array();
        $link_options = array();
        foreach($post as $option => $value) {
            $option = esc_html($option);
            $value = esc_html($value);
            if ( $option !='feed_submitted' && $option != 'Submit' && $value != '' ) {
                $id = substr( $option, 0, strpos($option, '_') );
                $option = substr( $option, strpos($option, '_') + 1 );
                if ( $option == 'enabled' ) {
                    $enabled_options[$id] = array( $option => $value );
                } elseif ( $option == 'link' ) {
                    if( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                        $link_options[$id] = array( $option => $value );
                    }
                    else $sif_error_message = 'One or more links not updated. Please enter a valid URL.';
                }
            }
        }
        $options = array_merge_recursive( $enabled_options , $link_options );
        if( is_array( $options ) ) {
            $sif_update_message = 'Feed settings updated.';
        }
        return $options;
    }
    if( isset ($_POST['feed_submitted']) && $_POST['feed_submitted'] == 'Y' ) {
        $sif_submitted_feed_options = sif_submit_feed_options( $_POST );
        $sif_options_feed = get_option( 'sif_options_feed' );
        $sif_options_feed_slice = sif_paginate_admin( $sif_options_feed , $sif_pagenum ); //slice options array to current page
        $sif_options_feed_reset = $sif_options_feed_slice;
        //Set each image to disabled
        foreach ($sif_options_feed_slice as $id => $option ) {
            $sif_options_feed_reset[$id]['enabled'] = '';
            $sif_options_feed_reset[$id]['link'] = '';
        }
        //Enable or re-enable selected images. There should be/probably is a better way to do this.
        foreach ( $sif_submitted_feed_options as $id => $option ) {
            if( array_key_exists( 'enabled' , $option ) ) {
                $sif_options_feed_reset[$id]['enabled'] = '1';
            }
            if( array_key_exists( 'link' , $option ) ) {
                $sif_options_feed_reset[$id]['link'] = $option['link'];
            }
        }
        $sif_options_feed = $sif_options_feed_reset + $sif_options_feed; //Combine current settings with new selections
        update_option( 'sif_options_feed' , $sif_options_feed );
    }
    $sif_options_feed = get_option( 'sif_options_feed' );
    if( isset( $sif_ig_feed ) && isset( $sif_options_feed ) ) {
        $sif_options_feed_updated = sif_update_options_feed( $sif_ig_feed , $sif_options_feed );
    }
    
    /**
	 * Create front-end display feed
	 * Store in WP options
	 */
    function sif_admin_create_display( $feed ) {
        $display = array();
        foreach( $feed  as $id => $option ) {
            if( $option['enabled'] == '1' ) {
                $display[$id] = $option;
            }
        }
        if( !empty($display) ) {
            update_option( 'sif_display_feed' , $display );
            return true;
        } else  {
            update_option( 'sif_display_feed' , '' );
            return false;
        }
    }
    if( isset( $sif_options_feed_updated ) && !empty( $sif_options_feed_updated ) ) {
        sif_admin_create_display ( $sif_options_feed_updated );
    }
    
    /**
	 * Verify display feed exists and show shortcode
	 *
	 */
    function sif_admin_shortcode_info() {
        $display_feed = get_option( 'sif_display_feed' );
        if( is_array( $display_feed ) ) {
            return true;
        } else return false;
    }
    $sif_show_shortcode = sif_admin_shortcode_info();
    
    /**
	 * Create IG feed for admin display
	 * 
	 */
    function sif_admin_feed_display( $feed , $pagenum ) {
        if ( is_array ( $feed ) ) {
            $feed = sif_paginate_admin( $feed , $pagenum );
            foreach ( $feed as $id => $image ) {
                if ( $image['enabled'] == '1' ) {
                    $checked = 'checked ';
                } else $checked = '';
                $link = $image['link'];
                $output = '<div class="sif_admin_image_block">';
                $output .= '<img src="' . $image['image_url_lowres'] . '" alt="" width="320" height="320">';
                $output .= '<label for="' . $id . '_enabled"></label><input name="' . $id . '_enabled" type="checkbox" id="' . $id . '_enabled" value="1" ' . $checked . '/>Show in feed?<br>';
                $output .= '<label for="' . $id . '_link">Link:</label> <input type="text" name="' . $id . '_link" id="' . $id . '_link" value="' . $link . '" class="large-text" />';
                $output .= '</div>';
                echo $output;
            }
        }
    }
    
    function sif_admin_pagination_links( $feed , $pagenum ) {
        $total_images = count( $feed );
        $num_pages = ceil( $total_images / 12 );
        $page_links = paginate_links( array(
            'base' => add_query_arg( 'pagenum', '%#%' ),
            'format' => '',
            'prev_text' => __( '&laquo;', 'aag' ),
            'next_text' => __( '&raquo;', 'aag' ),
            'total' => $num_pages,
            'current' => $pagenum
        ) );
        return $page_links;
    }
    if( isset( $sif_options_feed_updated ) && isset( $sif_pagenum ) ) {
        $sif_page_links = sif_admin_pagination_links( $sif_options_feed_updated , $sif_pagenum );
    }
    
    
    /**
	 * Get Instagram client options from WP options table
	 * 
	 */
    $client_info = sif_get_ig_client_info();
	$client_id = $client_info['client_id'];
    $client_secret = $client_info['client_secret'];
        
	include_once('sif-options-page.php');
	
}
