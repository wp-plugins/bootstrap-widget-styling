<?php

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

  static function filter_html_categories( $html_to_filter ) {
    return self::instantiate_and_filter( $html_to_filter , 'categories' ) ;
  }
 
  static function filter_html_archives( $html_to_filter ) {
    return self::instantiate_and_filter( $html_to_filter , 'archives' ) ;
  }

  static function filter_html_pages( $html_to_filter ) {
    return self::instantiate_and_filter( $html_to_filter , 'pages' ) ;
  }

  static function instantiate_and_filter( $html_to_filter , $type_of_filter ) {
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
