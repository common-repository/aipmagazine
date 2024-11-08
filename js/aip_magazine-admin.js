var $aip_magazine_admin = jQuery.noConflict();

$aip_magazine_admin(document).ready(function($) {
	
	$('input#verify').live('click', function() {
		
		var aip_magazine_API = $( 'input#api' ).val();
		var error = false;
		
		if ( "" == aip_magazine_API ) {
			
			$( 'input#api' ).css( 'background-color', 'red' );
			return false;
			
		} else {
		
			$( 'input#api' ).css( 'background-color', 'white' );
			
		}
	
		var data = {
			action: 	'verify',
			aip_magazine_API: aip_magazine_API,
			_wpnonce: 	$( 'input#aip_magazine_verify_wpnonce' ).val()
		};
		
		ajax_response( data );
		
	});
	
	function ajax_response( data ) {
		
		var style = 'position: fixed; ' +
					'display: none; ' +
					'z-index: 1000; ' +
					'top: 50%; ' +
					'left: 50%; ' +
					'background-color: #E8E8E8; ' +
					'border: 1px solid #555; ' +
					'padding: 15px; ' +
					'width: 500px; ' +
					'min-height: 80px; ' +
					'margin-left: -250px; ' + 
					'margin-top: -150px;' +
					'text-align: center;' +
					'vertical-align: middle;';
					
		$( 'body' ).append( '<div id="results" style="' + style + '"></div>' );
		$( '#results' ).html( '<p>Sending data to Magazine</p><p><img src="/wp-includes/js/thickbox/loadingAnimation.gif" /></p>' );
		$( '#results' ).show();
		
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$aip_magazine_admin.post( ajaxurl, data, function( response ) {
			
			$( '#results' ).html( '<p>' + response + '</p>' +
									'<input type="button" class="button" name="results_ok_button" id="results_ok_button" value="OK" />' );
			$( '#results_ok_button' ).click( remove_results );
			
		});
		
	}
		
	function remove_results() {
		
		$aip_magazine_admin( '#results_ok_button' ).unbind( 'click' );
		$aip_magazine_admin( '#results' ).remove();
		
		if ( typeof document.body.style.maxHeight == 'undefined' ) {//if IE 6
		
			$aip_magazine_admin( 'body', 'html' ).css( { height: 'auto', width: 'auto' } );
			$aip_magazine_admin( 'html' ).css( 'overflow', '' );
			
		}
		
		document.onkeydown = '';
		document.onkeyup = '';
		
	}

	// custom media uploader for settings page
	
	var custom_uploader;
 
 
    $('#upload_image_button').click(function(e) {
 
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#default_issue_image').val(attachment.url);
        });
 
        //Open the uploader dialog
        custom_uploader.open();
 
    });


    $('#aip_magazine-tabs').find('a').click(function() {
		$('#aip_magazine-tabs').find('a').removeClass('nav-tab-active');
		$('.aip_magazinetab').removeClass('active');

		var id = $(this).attr('id').replace('-tab','');
		$('#' + id).addClass('active');
		$(this).addClass('nav-tab-active');
	});

	// init
	var active_tab = window.location.hash.replace('#top#','');

	// default to first tab
	if ( active_tab == '' || active_tab == '#_=_') {
		active_tab = $('.aip_magazinetab').attr('id');
	}

	$('#' + active_tab).addClass('active');
	$('#' + active_tab + '-tab').addClass('nav-tab-active');
	
	$('.color-field').wpColorPicker();
    
});