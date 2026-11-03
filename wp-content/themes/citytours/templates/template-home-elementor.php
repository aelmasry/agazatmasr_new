<style>
.wp-call-button {
	left:5px !important;
	bottom:70px !important
}

.destination-card img {
    width: 100%;
    height: auto;
    display: block;
    width: 100%;
    height: 100% !important;
}

#slider-pro-458{
width:100%;
max-width:100% !important
}

#search-hotel-form .bottom-part .form-group, #search-hotel-form .top-part .form-group, #search-tour-form .bottom-part .form-group, #search-tour-form .top-part .form-group, .flight-con .form-group {
 
    width: calc(100% / 5) !important;

}

#search .search_con {

    margin-top: -150px !important;
}

#search-hotel-form .bottom-part, #search-hotel-form .top-part, #search-tour-form .bottom-part, #search-tour-form .top-part, .flight-con {
    display: flex
;
    flex-wrap: nowrap !important;

}

#search .search_con {
    margin-top: -150px !important;
    width: 75% !important;
}

#search .tab-content {

    width: 100% !important;

}

#search .nav-tabs {

    display: flex;
    justify-content: center;
    margin-bottom: 2px;
}

.destination-grid {
    display: grid
;
    grid-template-columns: repeat(3, 1fr) !important;

}


@media (max-width:991px){
#search-hotel-form .bottom-part .form-group, #search-hotel-form .top-part .form-group, #search-tour-form .bottom-part .form-group, #search-tour-form .top-part .form-group, .flight-con .form-group {
 
    width: calc(100% / 1) !important;

}

#search-hotel-form .bottom-part, #search-hotel-form .top-part, #search-tour-form .bottom-part, #search-tour-form .top-part, .flight-con {
    display: flex
;
    flex-wrap: wrap !important;

}

#search .search_con {
    margin-top: -330px !important;
    width: 100% !important;
}
	.n2-ss-slider-wrapper-inside{
height:50vh !important
}
	#search .search_con .btn_1 {
width:100% !important
}
.destination-grid {
    display: grid
;
    grid-template-columns: repeat(1, 1fr) !important;

}
.destination-card {
    height: 250px !important;
}

#search .tab-content {

    width: 100% !important;

}
}

#search .search_con {

    left: 0px !important;
    right: 0px !important;
    margin-left: auto !important;
	margin-right: auto !important
}
.destination-card {
 
height:300px !important;
}

.post-comment{
display:none;
}

.blog_sec_home .item .pic img {
    display: block;
    height: 205px !important;
    width: 100%;
}

.overlay {
    background-color: rgba(0, 0, 0, .2) !important;
 
    opacity: 1 !important;
    transition: opacity .3s;
}

	#elementor-panel-state-loading{
display:none !important;
}
</style>
<?php

/*
  Template Name: Template Home Elementor
 */
if (!defined('ABSPATH')) {
    exit;
}

get_header();

?>
<div class="elementor-container elementor-page elementor-page-458">
    <?php the_content(); ?>
    <!-- end post -->
</div>

<script type="text/javascript">
    $ = jQuery.noConflict();

    $(document).ready(function() {

        var lang = '<?php echo get_locale() ?>';

        lang = lang.replace('_', '-');

        $('input.date-pick').datepicker({
            startDate: "today",
            language: lang
        });

        //                $('input[name="date_from"]').datepicker('setDate', 'today');
        //                $('input[name="date_to"]').datepicker('setDate', '+1d');

        $('.way-back').on("click", function() {
            $('.return-date').removeClass('disable');
            $('.return-date').attr('required', true)
        });

        $('.one-way').on("click", function() {
            $('.return-date').addClass('disable');
            $('.return-date').attr('required', false)
        });

        var amadeusSearchRequest;
        var searchResult = new Array()
        $('#amadeus_air_dep_from').autoComplete({
            minChars: 2,
            delay: 0,
            source: function(term, suggest) {
                try {
                    amadeusSearchRequest.abort();
                } catch (e) {}

                amadeusSearchRequest = $.post(ajaxurl, {
                    'term': term,
                    action: 'airports_search'
                }, function(res) {
                    suggest(res)
                })
            },
            onSelect: function(event, ui) {
                event.preventDefault();
                $('#fromCode').val(ui.split(', ')[1])
            },
        })

        $('#amadeus_air_dep_to').autoComplete({
            minChars: 2,
            delay: 0,
            source: function(term, suggest) {
                try {
                    amadeusSearchRequest.abort();
                } catch (e) {}

                amadeusSearchRequest = $.post(ajaxurl, {
                    'term': term,
                    action: 'airports_search'
                }, function(res) {
                    suggest(res)
                })
            },
            onSelect: function(event, ui) {
                event.preventDefault();
                $('#toCode').val(ui.split(', ')[1])
            },
        })

    })
</script>

<?php

get_footer();
