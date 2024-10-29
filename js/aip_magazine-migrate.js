var $aip_magazine_migrate = jQuery.noConflict();

$aip_magazine_migrate(document).ready(function($) {
	$( '.checkall' ).click(function () {
		
		$( this ).parents( 'table:eq(0)' ).find( ':checkbox' ).attr( 'checked', this.checked );
	
	});
	
});