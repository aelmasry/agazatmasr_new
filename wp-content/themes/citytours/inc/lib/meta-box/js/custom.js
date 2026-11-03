jQuery(function ($)
{
    'use strict';

    $(document).ready(function () {

        if ($('#_tour_charge_child').length) {
            $('#_tour_charge_child').change(function () {
                $('#_tour_price_child').closest('.rwmb-field').toggle(this.checked);
                $('#_tour_price_child_0_2').closest('.rwmb-field').toggle(this.checked);
                $('#_tour_price_child_2_6').closest('.rwmb-field').toggle(this.checked);
                $('#_tour_price_child_6_11').closest('.rwmb-field').toggle(this.checked);
            });
            $('#_tour_charge_child').trigger('change');
            $('#_tour_price_child_0_2').trigger('change');
            $('#_tour_price_child_2_6').trigger('change');
            $('#_tour_charge_child_6_11').trigger('change');
        }

        if ($('input[name="_tour_repeated"]').length) {
            $('input[name="_tour_repeated"]').change(function () {
                toggleDatePicker();
            });
            toggleDatePicker();
        }

        function toggleDatePicker() {
            var flag = true;
            if ($('input[name="_tour_repeated"]:checked').val() == 1)
                flag = false;

            $('input[name="_tour_date"]').closest('.rwmb-field').toggle(flag);
            $('input[name="_tour_start_date"]').closest('.rwmb-field').toggle(!flag);
            $('input[name="_tour_end_date"]').closest('.rwmb-field').toggle(!flag);
            $('select[name="_tour_available_days[]"]').closest('.rwmb-field').toggle(!flag);
        }

        if ($('.ct_datepicker').length) {
            $('.ct_datepicker').datepicker({dateFormat: "yy-mm-dd"});
        }

        if ($('.has_multi_schedules').length) {
            $('.has_multi_schedules').change(function () {
                var $wrapper = $(this).closest('.schedule-wrapper');
                var $check_box = this;
                $wrapper.find('.add-clone, .remove-clone, .schedule-header').toggle(this.checked); // hide add-clone, remove-clone, date fields
                console.log(this.checked);
                $wrapper.find('.rwmb-clone').each(function (index) {
                    if (index != 0)
                        $(this).toggle($check_box.checked);
                }); // hide the other schedules
            });
            $('.has_multi_schedules').trigger('change');
        }
    });
});