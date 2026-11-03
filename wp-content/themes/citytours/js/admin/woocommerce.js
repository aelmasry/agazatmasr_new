jQuery( document ).ready( function() {
	jQuery( '.options_group.pricing' ).addClass( 'show_if_simple_hotel show_if_simple_tour' ).show();
	jQuery( '.form-field._manage_stock_field' ).addClass( 'show_if_simple_hotel show_if_simple_tour' ).show();

	if ( jQuery('#product-type').val() == 'simple_hotel' || jQuery('#product-type').val() == 'simple_tour' ) { 
		jQuery('.general_options.general_tab').show();
		jQuery('.general_options.general_tab a').trigger( 'click' );
	}
});