$ = jQuery
jQuery(document).ready(function($) {
    "use strict";
    // vacancies manage(add/edit) page
    $('#hotel_id').select2({
        placeholder: "Select a Hotel",
        width: "250px"
    });
    $('#room_type_id').select2({
        placeholder: "Select a Room Type",
        width: "250px"
    });
    $('#date_from').datepicker({ dateFormat: "yy-mm-dd" });
    $('#date_to').datepicker({ dateFormat: "yy-mm-dd" });

    $('#hotel_id').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'hotel_get_hotel_room_list',
                'hotel_id' : $(this).val()
            },
            success: function(response){
                if ( response ) {
                    var room_type_id = $('#room_type_id').val();
                    $('#room_type_id').html(response);
                    $('#room_type_id').val(room_type_id);
                    $('#room_type_id').select2({
                        placeholder: "Select a Room Type",
                        width: "250px",
                    });
                }
            }
        });
    });

    $('#room_type_id').change(function(){
        if ( ! $('#hotel_id').val() ) {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'hotel_get_room_hotel_id',
                    'room_id' : $(this).val()
                },
                success: function(response){
                    if ( response ) {
                        $('#hotel_id').val(response).change();
                    }
                }
            });
        }
    });

    
    // vacancies list page
    $('#hotel_filter').select2({
        placeholder: "Filter by Hotel",
        allowClear: true,
        width: "240px"
    });
    $('#room_type_filter').select2({
        placeholder: "Filter by Room Type",
        allowClear: true,
        width: "240px"
    });
    $('#date_filter').datepicker({ dateFormat: "yy-mm-dd" });

    $('#hotel_filter').change(function(){
        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'hotel_get_hotel_room_list',
                'hotel_id' : $(this).val()
            },
            success: function(response){
                if ( response ) {
                    var room_type_id = $('#room_type_filter').val();
                    $('#room_type_filter').html(response);
                    $('#room_type_filter').val(room_type_id);
                    $('#room_type_filter').select2({
                        placeholder: "Filter by Room Type",
                        allowClear: true,
                        width: "240px",
                    });
                }
            }
        });
    });

    $('#room_type_filter').change(function(){
        if ( ! $('#hotel_filter').val() ) {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'hotel_get_room_hotel_id',
                    'room_id' : $(this).val()
                },
                success: function(response){
                    if ( response ) {
                        $('#hotel_filter').val(response).change();
                    }
                }
            });
        }
    });

    $('#vacancy-filter').click(function(){
        var hotelId = $('#hotel_filter').val();
        var roomTypeId = $('#room_type_filter').val();
        var filter_date = $('#date_filter').val();
        var loc_url = 'edit.php?post_type=hotel&page=vacancies';
        if (hotelId) loc_url += '&hotel_id=' + hotelId;
        if (roomTypeId) loc_url += '&room_type_id=' + roomTypeId;
        if (filter_date) loc_url += '&date=' + filter_date;
        document.location = loc_url;
    });

    $('.row-actions .delete a').click(function(){
        var r = confirm("It will be deleted permanetly. Do you want to delete it?");
        if(r == false) {
            return false;
        }
    });

});

var submitting = false;
function manage_vacancy_validateForm() {
    "use strict";
    if ( submitting == true ) return false;
    if( '' == $('#hotel_id').val()){
        alert('Please select a hotel');
        return false;
    } else if( '' == $('#room_type_id').val()){
        alert('Please select a room type');
        return false;
    }
    submitting = true;
    return true;
}