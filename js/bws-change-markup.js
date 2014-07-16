( function( $ ) {
  $( function() {

    // widget markup to be edited
    var recentPostsWidget = $( '.widget_recent_entries' ) ; 
    var recentCommentWidget = $( '.widget_recent_comments' ) ;
    var metaWidget = $( '.widget_meta' ) ;      

    $.map( [ recentPostsWidget , recentCommentWidget , metaWidget ] , formatWidgetForBootstrap ) ;
    function formatWidgetForBootstrap( widget ) {
      var allAnchors = [] ; 	
      widget.find( 'ul li' ).map( function() { // turn each li tag into an anchor, store in allAnchors
	var span = $( this ).find( 'span' ).clone().addClass( 'label label-primary pull-right' ) ; 
	var anchor = $( this ).find( 'a' ).clone() ;
	anchor.addClass( 'list-group-item' ).append( '&nbsp;' ).append( span ) ; ;
	$( this ).remove( 'span' ) ;
	allAnchors.push( anchor ) ; 
      } ) ;

      // Create a new div containing allAnchors from previous function
      newListGroupDiv = $( '<div>' ).addClass( 'list-group' ).append( allAnchors )  ; 
      widget.append( newListGroupDiv ) ; 
      widget.find( 'ul' ).remove() ; // remove the ul that the original li tags were from 
    }

  } ) ;
} ) ( jQuery) 
