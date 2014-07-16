<?php

bws_if_options_allow_then_add_filters_of_types( array( 'categories' , 'pages' , 'archives' ) ) ;

function bws_if_options_allow_then_add_filters_of_types( $types ) {
  foreach( $types as $type ) { 
    if ( bws_options_allow_adding_filter_for_widget_type( $type ) ) {
      bws_add_filter_for_widget_type( $type ) ;
    }
  }
}

function bws_options_allow_adding_filter_for_widget_type( $type_of_widget ) {
  $options = get_option( 'bws_plugin_options' ) ;
  $widget_key = 'disable_' . $type_of_widget . '_widget' ; 
  if ( ( isset( $options[ $widget_key ] ) ) && ( '1' === $options[ $widget_key ] ) ) {
    return false ; 
  }
  return true ;
}

function bws_add_filter_for_widget_type( $type ) {
  if ( 'archives' === $type ) {
    add_filter( 'get_archives_link' , array( 'BWS_Filter' , 'filter_html_archives' ) ) ;
  } else { 
    add_filter( 'wp_list_' . $type , array( 'BWS_Filter' , 'filter_html_' . $type ) ) ;
  }
}


// Filter widget markup 
class BWS_Filter {
  private static $instance ;
  static $types_of_widgets_called = array() ;
  private $markup ;
  private $type_of_filter ; 
  
  private function __construct( $html_to_filter , $type_of_filter ) {
    $this->markup = $html_to_filter ;
    $this->type_of_filter = $type_of_filter ;
  }

  function filter_html_categories( $html_to_filter ) {
    return self::instantiate_and_filter( $html_to_filter , 'categories' ) ;
  }
 
  function filter_html_archives( $html_to_filter ) {
    return self::instantiate_and_filter( $html_to_filter , 'archives' ) ;
  }

  function filter_html_pages( $html_to_filter ) {
    return self::instantiate_and_filter( $html_to_filter , 'pages' ) ;
  }

  function instantiate_and_filter( $html_to_filter , $type_of_filter ) {
    self::$instance = new self( $html_to_filter , $type_of_filter ) ;    
    self::$instance->filter() ;
    return self::$instance->markup ;    
  }
  
  function filter() {
    $this->remove_ul_tags_if_filter_type_is_pages() ;    
    $this->close_ul_if_first_call_of_filter() ;
    $this->replace_parenthesized_number_with_badge_number() ;
    $this->remove_li_tags() ; 
    $this->add_list_group_class_to_anchor_tags() ; 
    $this->move_span_inside_anchor_closing_tag() ;
    $this->add_closing_div_depending_on_filter_type() ; 
  }

  function remove_ul_tags_if_filter_type_is_pages() {
    if ( 'pages' === $this->type_of_filter ) {
      $this->markup = preg_replace( '/<[\/]?ul.*?>/' , '' , $this->markup ) ;
    }
  }
  
  function close_ul_if_first_call_of_filter() {
    if ( self::is_first_instance_of( $this->type_of_filter ) ) {
      self::close_ul_and_add_opening_div() ;
    }
  }
  
  function is_first_instance_of( $type ) {
    if ( ! isset( self::$types_of_widgets_called[ $type ] ) ) {
      self::$types_of_widgets_called[ $type ] = true ;
      return true ;
    }
    return false ;
  }

  function close_ul_and_add_opening_div() {
    $this->markup = '</ul><div class="list-group">' . $this->markup ;
  }

  function replace_parenthesized_number_with_badge_number() {
    $regex = '/\((\d{1,3})\)/' ;
    $new_count_markup = "<span class='badge pull-right'>$1</span>" ;
    $this->markup = preg_replace( $regex , $new_count_markup , $this->markup ) ;
  }

  function remove_li_tags() {
    $regex = '/<li.*?>/' ;
    $this->markup =  preg_replace( $regex  , '' , $this->markup ) ;
  }

  function add_list_group_class_to_anchor_tags() {
    $this->markup = str_replace( '<a' , '<a class="list-group-item"' , $this->markup ) ;
  }

  function move_span_inside_anchor_closing_tag() {
    $new_markup = "$2$1" ;
    $regex = "/(<\/a>).*?(<span.+?<\/span>)/" ;
    $this->markup = preg_replace( $regex , $new_markup , $this->markup ) ;
  }

  function add_closing_div_depending_on_filter_type() {
    if ( 'archives' !== $this->type_of_filter )  {
      $this->markup .= '</div>' ;
    }
  }
}

add_filter( 'dynamic_sidebar_params' , 'bws_params_dynamic_sidebar' ) ;
function bws_params_dynamic_sidebar( $params ) {
  if ( isset( $params[ 0 ][ 'widget_name' ] ) && 'Archives' == $params[ 0 ][ 'widget_name' ] ) {
    $params[ 0 ][ 'after_widget' ] = '</div>' . $params[ 0 ][ 'after_widget' ] ; 
  }
  return $params ; 
} 

// if there' a "Recent Posts" widget, enqueue bws-change-markup.js 
add_filter( 'widget_posts_args' , 'bws_posts_enqueue_javascript' ) ; 
function bws_posts_enqueue_javascript( $args ) {
  wp_enqueue_script( BWS_PLUGIN_SLUG . '-script' , plugins_url( '/' . BWS_PLUGIN_SLUG . '/js/bws-change-markup.js' ) , array( 'jquery' ) ) ;  
  return $args ; 
}

// if there' a "Meta" widget, enqueue bws-change-markup.js 
add_filter( 'widget_meta_poweredby' , 'bws_meta_enqueue_javascript' ) ;
function bws_meta_enqueue_javascript( $args ) {
  wp_enqueue_script( BWS_PLUGIN_SLUG . '-script' , plugins_url( '/' . BWS_PLUGIN_SLUG . '/js/bws-change-markup.js' ) , array( 'jquery' ) ) ;  
  return $args ; 
}

// if there' a "Comments" widget, enqueue bws-change-markup.js 
add_filter( 'widget_comments_args' , 'bws_comments_enqueue_javascript' ) ;
function bws_comments_enqueue_javascript( $args ) {
  wp_enqueue_script( BWS_PLUGIN_SLUG . '-script' , plugins_url( '/' . BWS_PLUGIN_SLUG . '/js/bws-change-markup.js' ) , array( 'jquery' ) ) ;  
  return $args ; 
}

bws_add_search_form_filter_if_option_allows() ;

function bws_add_search_form_filter_if_option_allows() {
  $options = get_option( 'bws_plugin_options' ) ;
  if ( ( isset( $options[ 'disable_search_widget' ] ) ) && ( '1' === $options[ 'disable_search_widget' ] ) ) {
    return ;
  } else {
    add_filter( 'get_search_form' , 'bws_search_filter' , '1' ) ;
  }
}

function bws_search_filter( $markup ) {
  $search_template = locate_template( 'searchform.php' );
  if ( '' == $search_template ) {
    // there's no search form template in the theme, so filter the markup
    $markup = bws_add_input_group_class_to_opening_div( $markup ) ;
    $markup = bws_remove_label( $markup ) ;
    $markup = bws_add_form_control_class_to_text_input( $markup ) ;
    $markup = bws_add_class_to_submit_button( $markup ) ; // btn btn-primary btn-med
    $markup = bws_wrap_submit_button_in_div( $markup ) ;
  }
  return $markup ; 
}

function bws_add_input_group_class_to_opening_div( $html ) {
  $filtered_html = str_replace( '<div>' , '<div class="input-group">' , $html ) ;
  return $filtered_html ;
}

function bws_remove_label( $html ) {
  $filtered_html = preg_replace( '/<label.*?<\/label>/' , '' , $html ) ;
  return $filtered_html ;
}

function bws_add_form_control_class_to_text_input( $html ) {
  $filtered_html = str_replace( '<input type="text"' , '<input type="text" class="form-control"' , $html ) ;
  return $filtered_html ;
}

function bws_add_class_to_submit_button( $html ) { 
  $filtered_html = str_replace( '<input type="submit"' , '<input type="submit" class="btn btn-primary btn-med"' , $html ) ;
  return $filtered_html ;
}

function bws_wrap_submit_button_in_div( $html ) {
  $filtered_html = preg_replace( '/(<input type="submit".*?>)/' , '<div class="input-group-btn">$1</div>' , $html ) ;
  return $filtered_html ;
}

  
  
  








  
