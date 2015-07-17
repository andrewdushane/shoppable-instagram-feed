<?php
/**
 * Plugin Name: Shoppable Instagram Feed
 * Plugin URI: 
 * Description: Display responsive Instagram feed for logged in user with links to buy a product related to each image
 * Version: 1.0
 * Author: Andrew Dushane
 * Author URI: http://premierprograming.com
 * License: GPL2
 */

/**
 * Enqueue admin CSS
 * 
 */
function sif_admin_style() {
    wp_register_style( 'sif_admin_css', plugins_url( 'css/sif-admin.css', __FILE__ ) );
    wp_enqueue_style( 'sif_admin_css' );
}
add_action( 'admin_enqueue_scripts' , 'sif_admin_style' );

/**
 * Enqueue display CSS
 * 
 */
function sif_display_style() {
    wp_register_style( 'sif_display_css', plugins_url( 'css/sif-display.css', __FILE__ ) );
    wp_enqueue_style( 'sif_display_css' );
}
add_action( 'wp_enqueue_scripts' , 'sif_display_style' );

include_once('sif-options.php');

include_once('sif-display.php');
