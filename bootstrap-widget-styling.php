<?php
/*
Plugin Name: Bootstrap Widget Styling
Plugin URI: www.ryankienstra.com/bootstrap-widget-styling
Description: Make widgets mobile. Bigger click area and better styling. Only one small file sent to the browser. Disable this for selected widgets by clicking "Settings." Must have Bootstrap 3.
Version: 1.0.0
Author: Ryan Kienstra
Author URI: www.ryankienstra.com
License: GPL2

*/

if ( ! defined( 'WPINC' )  ) {
 die ;
}

define( 'BWS_PLUGIN_SLUG' , 'bootstrap-widget-styling' ) ;
define( 'BWS_PLUGIN_VERSION' , '1.0.0' ) ;

register_activation_hook( __FILE__ , 'bws_activate_with_default_options' ) ;
function bws_activate_with_default_options() {
  $bws_plugin_options = array(
    'disable_pages_widget' => 0 , 
    'disable_search_widget' => 0 , 
    'disable_categories_widget' => 0 ,
    'disable_archives_widget' => 0 ,
  ) ;
  update_option( 'bws_plugin_options' , $bws_plugin_options ) ;
}

add_action( 'plugins_loaded' , 'bws_get_required_files' ) ;
function bws_get_required_files() {
  require_once( plugin_dir_path( __FILE__ ) . 'includes/bws-options.php' ) ;
  require_once( plugin_dir_path( __FILE__ ) . 'includes/bws-widget-filters.php' ) ;
}


