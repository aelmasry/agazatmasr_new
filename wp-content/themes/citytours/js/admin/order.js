$ = jQuery
jQuery(document).ready(function($) {
	"use strict";
        
        
        $('#user_filter').select2({
            placeholder: "Filter by Operator",
            allowClear: true,
            width: "140px"
        });
	// order list page
	$('#hotel_filter').select2({
		placeholder: "Filter by Hotel",
		allowClear: true,
		width: "240px"
	});
	$('#date_from_filter').datepicker({ dateFormat: "yy-mm-dd" });
	$('#date_to_filter').datepicker({ dateFormat: "yy-mm-dd" });

	$('#hotel-order-filter').click(function(){
		var userId = $('#user_filter').val();
		var hotelId = $('#hotel_filter').val();
		var dateFrom = $('#date_from_filter').val();
		var dateTo = $('#date_to_filter').val();
		var booking_no = $('#booking_no_filter').val();
		var status = $('#status_filter').val();
		var loc_url = 'edit.php?post_type=hotel&page=orders';
		if (userId) loc_url += '&userid=' + userId;
		if (hotelId) loc_url += '&post_id=' + hotelId;
		if (dateFrom) loc_url += '&date_from=' + dateFrom;
		if (dateTo) loc_url += '&date_to=' + dateTo;
		if (booking_no) loc_url += '&booking_no=' + booking_no;
		if (status) loc_url += '&status=' + status;
		document.location = loc_url;
	});

	// order list page
	$('#tour_filter').select2({
		placeholder: "Filter by Tour",
		allowClear: true,
		width: "240px"
	});
	$('#date_filter').datepicker({ dateFormat: "yy-mm-dd" });

	$('#tour-order-filter').click(function(){
		var userId = $('#user_filter').val();
		var tourId = $('#tour_filter').val();
		var _date = $('#date_filter').val();
		var booking_no = $('#booking_no_filter').val();
		var status = $('#status_filter').val();
		var loc_url = 'edit.php?post_type=tour&page=tour_orders';
		if (userId) loc_url += '&userid=' + userId;
		if (tourId) loc_url += '&post_id=' + tourId;
		if (_date) loc_url += '&date=' + _date;
		if (booking_no) loc_url += '&booking_no=' + booking_no;
		if (status) loc_url += '&status=' + status;
		document.location = loc_url;
	});

	$('.row-actions .delete a').click(function(){
		var r = confirm("It will be deleted permanetly. Do you want to delete it?");
		if(r == false) {
			return false;
		}
	});

	// order manage(add/edit) page
	$('.hotel-order-form #post_id').select2({
		placeholder: "Select a Hotel",
		width: "250px"
	});
	$('.tour-order-form #post_id').select2({
		placeholder: "Select a Tour",
		width: "250px"
	});
	$('#date_from').datepicker({ dateFormat: "yy-mm-dd" });
	$('#date_to').datepicker({ dateFormat: "yy-mm-dd" });
	$('#date').datepicker({ dateFormat: "yy-mm-dd" });
	$('#post_id').change(function(){
		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: {
				'action': 'hotel_order_postid_change',
				'post_id' : $(this).val()
			},
			success: function(response){
				if ( response.success == 1 ) {
					$( '.room_hotel_id_select' ).each(function(index){
						var value = $(this).val();
						$(this).html(response.room_list);
						$(this).val(value);
					});
					$( '.service_id_select' ).each(function(index){
						var value = $(this).val();
						$(this).html(response.service_list);
						$(this).val(value);
					});
				}
			}
		});
	});

	toggle_remove_buttons();

	// Add more clones
	$( 'body' ).on( 'click', '.add-clone', function(e){
		e.stopPropagation();
		var clone_last = $(this).closest('.clone-wrapper').find( '.clone-field:last' );
		var clone_obj = clone_last.clone();
		clone_obj.insertAfter( clone_last );
		var input_obj = clone_obj.find( 'input' );

		// Reset value
		input_obj.val( '' );

		// Get the field name, and increment
		input_obj.each( function(index) {
			var name = $(this).attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 )
			{
				return '[' + ( parseInt( p1 ) + 1 ) + ']';
			} );

			// Update the "name" attribute
			$(this).attr( 'name', name );
		});

		var select_obj = clone_obj.find( 'select' );
		select_obj.each( function(index) {
			var name = select_obj.attr( 'name' ).replace( /\[(\d+)\]/, function( match, p1 )
			{
				return '[' + ( parseInt( p1 ) + 1 ) + ']';
			} );
			// Update the "name" attribute
			$(this).attr( 'name', name );
			$(this).find("option:selected").prop("selected", false);
		});

		toggle_remove_buttons();
		return false;
	} );

	// Remove clones
	$( 'body' ).on( 'click', '.remove-clone', function(){
		// Remove clone only if there're 2 or more of them
		if ( $(this).closest('.clone-wrapper').find('.clone-field').length <= 1 ) return false;

		$(this).closest('.clone-field').remove();
		toggle_remove_buttons();
		return false;
	});

	function toggle_remove_buttons(){
		if ( $('.clone-wrapper').length ) {
			$('.clone-wrapper').each(function(index) {
				var button = $(this).find( '.clone-field .remove-clone' );
				button.length < 2 ? button.hide() : button.show();
			});
		}
	}
        
        $('#operator_id').select2({
		placeholder: "Filter by Operators",
		allowClear: true,
		width: "240px"
	});
});

var submitting = false;
function manage_order_validateForm() {
	"use strict";
	if ( submitting == true ) return false;
	if( '' == $('#post_id').val()){
		alert($('#order-form').data('message'));
		return false;
	}
	submitting = true;
	return true;
}