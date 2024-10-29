var $enctype_hack = jQuery.noConflict();

$enctype_hack(document).ready(function($) {

    //issue
	$( '#edittag, #addtag' ).attr( 'enctype','multipart/form-data' );
	$( '#edittag, #addtag' ).attr( 'encoding', 'multipart/form-data' );


	$('.term-description-wrap' ).css( 'display', 'none' );


    $('.term-name-wrap > p').hide();

   $( '#submit' ).click(function() {
        $('#message').css('display','none');
    });


});