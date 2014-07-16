<?php

add_action( 'admin_menu' , 'bws_plugin_page' ) ;
function bws_plugin_page() {
  add_options_page(
    'Bootstrap Widget Styling Settings' ,
    'Widget Styling' ,	      
    'manage_options' ,
    'bws_options_page' ,
    'bws_plugin_options_page' ) ;
}

function bws_plugin_options_page() {
  ?>
  <div class="wrap">
    <?php screen_icon() ; ?>
    <h2>Bootstrap Widget Styling</h2>
    <form action="options.php" method="post">
      <?php settings_fields( 'bws_plugin_options' ) ; ?>
      <?php do_settings_sections( 'bws_options_page' ) ; ?>
      <input name="Submit" type="submit" value="Save Changes" />
    </form>
  </div>
  <?php
}

add_action( 'admin_init' , 'bws_settings_setup' ) ;
function bws_settings_setup() {
  register_setting( 'bws_plugin_options' , 'bws_plugin_options' , 'bws_plugin_validate_options' ) ;

  $widgets_to_add_to_settings_page = array( 'categories' , 'pages' , 'search' ,  'archives' ) ;  // copied below due to scope issue
  function bws_plugin_validate_options( $input ) {
    $widgets_to_add_to_settings_page = array( 'categories' , 'pages' , 'search' ,  'archives' ) ; // copied from above due to scope issue
    $validated = array() ;
    foreach( $widgets_to_add_to_settings_page as $widget_name ) {
      $widget_key = 'disable_' . $widget_name .'_widget' ;
      $disable_setting = isset( $input[ $widget_key ] ) ? $input[ $widget_key ] : '0' ; 
      if ( is_one_or_zero( $disable_setting ) ) {
	$validated[ $widget_key ] = $disable_setting ;
      }
    }    
    return $validated ;
  }
  
  function is_one_or_zero( $value ) {
    return ( '1' == $value ) || ( '0' == $value ) ;
  }

  add_settings_section( 'bws_plugin_primary' , 'Settings',
			  'bws_plugin_section_text', 'bws_options_page' ) ;

  function bws_plugin_section_text() {
    echo '<h3>This plugin does not work well when the top navbar has a "Categories" or "Pages" widget. </h3>
    	 <h3><em>Disable</em> plugin for: </h3>' ; 
  }

  BWS_Settings_Fields::add_fields_for_widget_types( $widgets_to_add_to_settings_page ) ;

  // Add settings link on the main plugin page
  add_filter( 'plugin_action_links' , 'bws_add_settings_link' , 2 , 2 ) ;
  function bws_add_settings_link( $actions, $file ) {
  if ( false !== strpos( $file, BWS_PLUGIN_SLUG ) ) {
      $options_url = admin_url( 'options-general.php?page=bws_options_page' )  ;
      $actions[ 'settings' ] = "<a href='{$options_url}'>Settings</a>" ;
    }
    return $actions ;
  }
}

class BWS_Settings_Fields {
  private static $instance ; 
  static $input_type = 'checkbox' ;
  private $type ;

  private function __construct( $type ) {
    $this->type = $type ;
  }

  static function add_fields_for_widget_types( $types ) {
    foreach ( $types as $type ) {
      self::instantiate_and_add_setting( $type ) ;
    }
  }
  
  function instantiate_and_add_setting( $type ) {
    self::$instance = new self( $type ) ;
    self::$instance->bws_add_settings_field() ;
  }

  function bws_add_settings_field() {
    $type = ucfirst( $this->type ) ;
    add_settings_field( "bws_plugin_disable_{$type}_widget" , _( '"' . $type . '" widget' ) , array( $this , 'output_callback_for_setting' ) , 'bws_options_page' , 'bws_plugin_primary' ) ;
  }

  function output_callback_for_setting() {
    $disable_widget_setting =  $this->is_filter_disabled_for_this_widget() ;
    $name = 'bws_plugin_options[disable_' . $this->type . '_widget]' ; 
    ?>
      <input type="<?php echo self::$input_type ; ?>" name="<?php echo $name ; ?>" <?php checked( $disable_widget_setting , '1' , true ) ; ?> value="1"/>
    <?php
  }

  function is_filter_disabled_for_this_widget() {
    $options = get_option( 'bws_plugin_options' ) ; 
    $key = 'disable_' . $this->type . '_widget' ;
    $is_disabled = isset( $options[ $key ] ) ? $options[ $key ] : 0 ;
    return $is_disabled ; 
  }
}   

