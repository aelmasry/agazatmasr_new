(function($) { 
    
    const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        var districts = urlParams.get('districts')

        $('#search-hotel-form').validate({
            rules: {
                'country': {
                    required: function() {
                        return ($('#s').length == 0)
                    }
                },
                //                's': {
                //                    required: function () { return (($('#country').length == 0) && ($('#s').length == 0)); }
                //                },
            },
            messages: {
                country: {
                    required: 'هذا الحقل إلزامي'
                },
                cities: {
                    required: 'هذا الحقل إلزامي'
                },
                s: {
                    required: 'هذا الحقل إلزامي'
                },
            },
            errorPlacement: function(error, element) {
                element.css('border', 'solid 2px red');
            },
            submitHandler: function(form) { // for demo
                $("#search-hotel-form :input").each(function(index, obj) {
                    if ($(obj).val() == "") {
                        //                    $(obj).remove();
                        $(obj).attr("disabled", "disabled")
                    }
                });
                form.submit();
                return false;
            }
        });

        $('#search-tour-form').validate({
            rules: {
                //                's': {
                //                    required: true
                //                },
                'tour_types': {
                    required: true
                },
                //                'cities': {
                //                    required: true
                //                },

            },
            messages: {
                tour_types: {
                    required: 'هذا الحقل إلزامي'
                },
                //                cities: {required: 'هذا الحقل إلزامي'},
                //                s: {required: 'هذا الحقل إلزامي'},
            },
            errorPlacement: function(error, element) {
                element.css('border', 'solid 2px red');
            },
            submitHandler: function(form) { // for demo
                $("#search-tour-form :input").each(function(index, obj) {
                    if ($(obj).val() == "") {
                        //                    $(obj).remove();
                        $(obj).attr("disabled", "disabled")
                    }
                });
                form.submit();
                return false;
            }
        });

        if ($('#countries').val() !== "") {
            countryId = $('#countries').val();
            load_cities(countryId)
            $('.filter_type').show()
        }

        // load cities
        $('#countries').change(function() {
            countryId = $(this).val();
            load_cities(countryId)
        })

        function load_cities(countryId) {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "get_districts_byparent",
                    'country_id': countryId
                },
                success: function(response) {
                    var options = '<option value="" selected>اختر المدينة</option>';
                    $.each(response.districts, function(index, item) {
                        if (districts == item['term_id']) {
                            options += '<option value="' + item['term_id'] + '" selected>' + item['name'] + '</option>';
                        } else {
                            options += '<option value="' + item['term_id'] + '">' + item['name'] + '</option>';
                        }
                    });

                    $('#cities').html(options);
                    $('#cities').prop("disabled", false);
                }
            });
        }

        // load cities
        $('#tour_types').change(function() {
            var tour_type = $(this).val();
            getTourCities(tour_type);
        })

        function getTourCities(tour_type) {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: "get_tour_type_cities",
                    'tour_type': tour_type
                },
                success: function(response) {
                    var options = '<option value="" selected>اختر المدينة</option>';
                    if (response.success == 1 && response.cities.length >= 1) {
                        
                        $.each(response.cities, function(index, item) {
                            options += '<option value="' + item['term_id'] + '">' + item['name'] + '</option>';
                        });

                        
                    }

                    $('#tour_city').html(options);
                    $('#tour_city').prop("disabled", false);
                }
            });
        }

        var searchRequest;
        $('#hotel_name').autoComplete({
            minChars: 1,
            delay: 0,
            source: function(term, suggest) {
                try {
                    console.log('search form');
                    searchRequest.abort();
                } catch (e) {}

                countryId = $('#search-hotel-form select[name=country]').val();
                districts = $('#search-hotel-form select[name=districts]').val();

                searchRequest = $.post(ajaxurl, {
                    search: term,
                    country: countryId,
                    districts: districts,
                    action: 'ct_hotel_ajax_search'
                }, function(res) {
                    suggest(res.data);
                });
            }
        });

        $('.open_close').on("click", function() {
            $('.main-menu').toggleClass('show');
        });


        $('#newsletter_2').submit(function(event){
            event.preventDefault();
            var action = $(this).attr('action');

            $("#message-newsletter_2").slideUp(750,function() {
                $('#message-newsletter_2').hide();

                $('#submit-newsletter_2')
                    .after('<i class="icon-spin4 animate-spin loader"></i>')
                    .attr('disabled','disabled');

                $.post(action, {
                        email_newsletter_2: $('#email_newsletter_2').val()
                    },
                    function(data){
                        document.getElementById('message-newsletter_2').innerHTML = data;
                        $('#message-newsletter_2').slideDown('slow');
                        $('#newsletter_2 .loader').fadeOut('slow',function(){$(this).remove()});
                        $('#submit-newsletter_2').removeAttr('disabled');
                        if(data.match('success') != null) $('#newsletter_2').slideUp('slow');

                    }
                );

            });

            return false;

        });

        var $homeIn = $('#hotel-check-in');
        var $homeOut = $('#hotel-check-out');
        if ($homeIn.length && $.fn.datepicker) {
            function homeNextDay(date) {
                var next = new Date(date.getTime());
                next.setDate(next.getDate() + 1);
                return next;
            }

            $homeIn.datepicker({
                format: 'yyyy-mm-dd',
                startDate: 'today',
                language: 'ar',
                autoclose: true,
                todayHighlight: true
            }).on('changeDate', function (event) {
                var earliest = homeNextDay(event.date);
                $homeOut.datepicker('setStartDate', earliest);
                var current = $homeOut.datepicker('getDate');
                if (current && current < earliest) {
                    $homeOut.datepicker('clearDates');
                }
            });

            $homeOut.datepicker({
                format: 'yyyy-mm-dd',
                startDate: '+1d',
                language: 'ar',
                autoclose: true,
                todayHighlight: true
            });
        }

})( jQuery );



document.addEventListener('DOMContentLoaded', function() {
    // Get all <img> elements with the 'sp-image' class
    var imgElements = document.querySelectorAll('img.sp-image, img.sp-thumbnail');
    console.log(imgElements);
    // Iterate through each <img> element
    imgElements.forEach(function(imgElement) {
        // Remove the 'lazyloading' class from the class list
        imgElement.className = imgElement.className.replace(/\blazyloading\b/, '');
    });
});


document.addEventListener('DOMContentLoaded', function () {
    var dateFields = document.getElementsByClassName('date-pick');

    // Check if the screen width is less than a certain threshold (adjust as needed)
    if (window.innerWidth <= 768) {
      // Loop through each date field
      for (var i = 0; i < dateFields.length; i++) {
        // Hide the default browser date picker on iOS devices
        dateFields[i].setAttribute('type', 'text');

        // For example, if your theme provides a date picker function called 'initDatePicker', use it like this:
        // initDatePicker(dateFields[i]);
      }
    }
  });