/* ==============================================
    Preload
=============================================== */
$ = jQuery.noConflict();
"use strict";

// Array foreach Prototype declare
(function(A) {

    if (!A.forEach) {
        A.forEach = A.forEach || function(action, that) {
            for (var i = 0, l = this.length; i < l; i++) {
                if (i in this) action.call(that, this[i], i, this);
            }
        };
    }

})(Array.prototype);


/* ==============================================
    Activate Pre-loader
=============================================== */
(function($) { 

    $(window).load( function() { // makes sure the whole site is loaded
        $('#status').fadeOut(); // will first fade out the loading animation
        $('#preloader').delay(350).fadeOut('slow'); // will fade out the white DIV that covers the website.
        $('body').delay(350).css({'overflow':'visible'});
    })

})( jQuery );


/* ==============================================
    Sticky nav
=============================================== */
(function($){ 

    $(window).scroll( function() {
        if ( $(this).scrollTop() > 10 ) {  
            $('header').addClass("sticky");
        } else {
            $('header').removeClass("sticky");
        }
    });

})( jQuery );


/* ==============================================
    Menu
=============================================== */
(function($){ 

    $('a.open_close').on( "click", function() {
        $('.main-menu').toggleClass('show');
        $('.layer').toggleClass('layer-is-visible');
    });

    $('.menu-item-has-children > a').on( "click", function(e) {
        // e.preventDefault();
        $(this).next().toggleClass("show_normal");
        // return false;
    });

    $('.menu-item-has-children-mega > a').on( "click", function() {
        $(this).next().toggleClass("show_mega");
    });

    if( $(window).width() <= 480 ) {
        $('a.open_close').on( "click", function() {
            $('.cmn-toggle-switch').removeClass('active')
        });
    }

})( jQuery );


/* ==============================================
    Common
=============================================== */
(function($){ 

    // Tooltip
    $('.tooltip-1').tooltip( {html: true} );

    // Accordion
    function toggleChevron(e) {
        $(e.target)
            .prev('.panel-heading')
            .find("i.indicator")
            .toggleClass('icon-plus icon-minus');
    }
    $('.panel-group').on('hidden.bs.collapse shown.bs.collapse', toggleChevron);

    // Widget Recent Entries
    if ( $('.widget_recent_entries').length ) {
        $( '.widget_recent_entries > ul > li' ).each( function(index) {
            $(this).children('.post-date').after( $(this).children('a') );
        });
    }

    // Animation on scroll
    new WOW().init();

})( jQuery );


/* ==============================================
    Overlay mask form + incrementer
=============================================== */
(function($){ 

    $('.expose').on( "click", function(e) {
        $('#overlay i.animate-spin').hide();
        $(this).css('z-index','100');
        $('#overlay').fadeIn(300);
    });

    $('#overlay').on( "click", function(e) {
        $('#overlay i.animate-spin').show();

        $('#overlay').fadeOut( 300, function() {
            $('.expose').css( 'z-index','1' );
        });
    });


    // Popup box
    $(document).bind( 'keydown', function (e) {
        var key = e.keyCode;

        if ($(".opacity-overlay:visible").length > 0 && key === 27) {
            e.preventDefault();
            $(".opacity-overlay").fadeOut();
        }
    });

    $(document).on( "click", ".opacity-overlay", function(e) {
        if ( !$(e.target).is(".opacity-overlay .popup-content *") ) {
            $(".opacity-overlay").fadeOut();
        }
    });

})( jQuery );


/* ==============================================
    Video modal dialog + Parallax + Scroll to top + Incrementer
=============================================== */
(function($) {

    $('.video').magnificPopup({type:'iframe'}); /* video modal*/
    $('.parallax-window').parallax({}); /* Parallax modal*/

    // Image popups
    $('.magnific-gallery').each(function() {
        $(this).magnificPopup({
            delegate: 'a', 
            type: 'image',
            gallery: { enabled: true }
        });
    }); 

    /* Top drodown prevent close*/
    $('.dropdown-menu').on( "click",function(e) {
        e.stopPropagation();
    });

    /* Hamburger icon*/
    var toggles = document.querySelectorAll(".cmn-toggle-switch"); 

    for (var i = toggles.length - 1; i >= 0; i--) {
        var toggle = toggles[i];
        toggleHandler(toggle);
    };

    function toggleHandler(toggle) {
        toggle.addEventListener( "click", function(e) {
            e.preventDefault();
            (this.classList.contains("active") === true) ? this.classList.remove("active") : this.classList.add("active");
        });
    };
      
    /* Scroll to top*/
    $(window).scroll( function() {
        if($(this).scrollTop() != 0) {
            $('#toTop').fadeIn();   
        } else {
            $('#toTop').fadeOut();
        }
    });

    $('#toTop').on( "click", function() {
        $('body, html').animate( {scrollTop: 0},500 );
    });

    /* Input incrementer*/
    $('.numbers-row').each( function() { 
        if ( ! $(this).find('.inc.button_inc').length ) { 
            $(this).append( '<div class="inc button_inc">+</div><div class="dec button_inc">-</div>' );
        }
    } );

    $(".numbers-row input").on( "change", function() {
        if ( $(this).parent().attr("data-max") && $(this).val() > $(this).parent().data('max') ) {
            $(this).val( $(this).parent().data('max') );
        }
        if ( $(this).parent().attr("data-min") && $(this).val() < $(this).parent().data('min') ) {
            $(this).val( $(this).parent().data('min') );
        }
    });

    $('body').on( 'click', '.button_inc', function () {

        var $button = $(this);
        var oldValue = $button.parent().find("input").val();

        if ( $button.text() == "+" ) {
            var max_val = 9999;
            if ( $(this).parent().attr("data-max") ) {
                max_val = $(this).parent().data("max");
            }
            if (oldValue < max_val) {
                var newVal = parseFloat(oldValue) + 1;
            } else {
                newVal = max_val;
            }
        } else {
            // Don't allow decrementing below zero
            var min_val = 0;
            if ( $(this).parent().attr("data-min") ) {
                min_val = $(this).parent().data("min");
            }
            if ( oldValue > min_val ) {
                var newVal = parseFloat(oldValue) - 1;
            } else {
                if ( $(this).parent() )
                newVal = min_val;
            }
        }
        if ( ! $button.parent().find("input").attr('disabled') ) { 
            $button.parent().find("input").val(newVal).change();
        }
    });

})( jQuery );

/* ==============================================
    Single Hotel Rooms Gallery Carousel
=============================================== */
(function($){ 

    if ( is_rtl === true ) { 
        is_rtl = true;
    } else {
        is_rtl = false;
    }

    if ( $("#single_hotel_desc .owl-carousel").length ) {
        $("#single_hotel_desc .owl-carousel").owlCarousel({ 
            rtl: is_rtl,
            autoplay: true,
            responsiveClass:true,
            responsive:{
                0:{
                    items:1
                },
                500:{
                    items:2
                },
                768:{
                    items:3
                },
                1200:{
                    items:4
                }
            }
        });
    }

})( jQuery );


/* ==============================================
    Reviews
=============================================== */
(function($){ 

    //reviews ajax loading
    $('.guest-reviews .more-review').click(function() {
        var $this = $(this);

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action': 'get_more_reviews',
                'post_id' : $(this).data('post_id'),
                'last_no' : $('.guest-review').length
            },
            success: function(response){
                if ( response.success == 0 ) {
                    $('.more-review').remove();
                } else {
                    $('.guest-reviews').append( response.html );
                    $('.guest-reviews').append( $this );
                }
                    
                if ( response.more_reviews != 1 ) { 
                    $this.text( response.notice );
                    $this.attr('disabled', 'disabled');
                }
            }
        });

        return false;
    });

    $('#review-form').submit(function() {
        $('#message-review').hide();

        var ajax_data = $(this).serialize();

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: ajax_data,
            success: function(response){
                if (response.success == 1) {
                    $('#review-form').hide();
                    $('#message-review').html(response.result);
                    $('#myReviewLabel').html(response.title);
                    $('#message-review').show();
                } else {
                    $('#message-review').html(response.result);
                    $('#message-review').show();
                }
            }
        });

        return false;
    });

})( jQuery );


/* ==============================================
    Action for Wishlist button
=============================================== */
(function($){ 

    // load more button click action on search result page
    $("body").on( 'click', '.btn-add-wishlist', function(e) {
        e.preventDefault();

        $('#overlay i.animate-spin').show();
        $('#overlay').show();

        var $t = $(this);

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'add_to_wishlist',
                'post_id' : $(this).data('post-id')
            },
            success: function(response){
                if (response.success == 1) {
                    $t.hide();
                    $t.siblings('.btn-remove-wishlist').show();
                } else {
                    alert(response.result);
                }
                $('#overlay').hide();
            }
        });
        return false;
    });

    // load more button click action on search result page
    $("body").on( 'click', '.btn-remove-wishlist', function(e) {
        e.preventDefault();

        $('#overlay i.animate-spin').show();
        $('#overlay').show();

        var $t = $(this);

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'add_to_wishlist',
                'post_id' : $(this).data('post-id'),
                'remove' : 1
            },
            success: function(response){
                if (response.success == 1) {
                    $t.hide();
                    $t.siblings('.btn-add-wishlist').show();
                } else {
                    alert(response.result);
                }
                $('#overlay').hide();
            }
        });
        return false;
    });

})( jQuery );


/* ==============================================
    Filters on list page
=============================================== */
(function($){ 

    // Toggle Filters container
    $(window).bind( 'load', function() {
        if( $(this).width() < 991 ) {
            $('.collapse#collapseFilters').removeClass('in');
            $('.collapse#collapseFilters').addClass('out');
        } else {
            $('.collapse#collapseFilters').removeClass('out');
            $('.collapse#collapseFilters').addClass('in');
        }
    });

//    $(document).ready( function() {
//
//        $('.list-filter input').on( 'ifToggled', function() {
//            var base_url = $(this).closest('ul').data('base-url').replace(/&amp;/g, '&');
//            var new_url = base_url;
//            var arg = $(this).closest('ul').data('arg');
//            $(this).closest('ul').find('input:checked').each(function(index){
//                if ( $(this).val() == -1 ) {new_url = base_url; return false;}
//                new_url += '&' + arg + '[]=' + $(this).val();
//            });
//            if (new_url.indexOf("?") < 0) { new_url = new_url.replace(/&/, '?'); }
//            window.location.href = new_url;
//            return false;
//        });
//
//        $('#sort_price').change( function() {
//            var base_url = $(this).data('base-url').replace(/&amp;/g, '&');
//            if ( $(this).val() == "lower" ) {
//                base_url += '&order_by=price&order=ASC';
//            } else if ( $(this).val() == "higher" ) {
//                base_url += '&order_by=price&order=DESC';
//            }
//            if (base_url.indexOf("?") < 0) { base_url = base_url.replace(/&/, '?'); }
//            window.location.href = base_url;
//            return false;
//        });
//
//        $('#sort_rating').change( function() {
//            var base_url = $(this).data('base-url').replace( /&amp;/g, '&' );
//            if ( $(this).val() == "lower" ) {
//                base_url += '&order_by=rating&order=ASC';
//            } else if ( $(this).val() == "higher" ) {
//                base_url += '&order_by=rating&order=DESC';
//            }
//            if (base_url.indexOf("?") < 0) { base_url = base_url.replace(/&/, '?'); }
//            window.location.href = base_url;
//            return false;
//        });
//    });

})( jQuery );


/* ==============================================
    SignUp and Login Form
=============================================== */
(function($){ 

    $('.signup-btn').click( function(e) {
        e.preventDefault();

        $('.loginform').hide();
        $('.signupform').show();

        return false;
    });

    $('.login-btn').click( function(e) {
        e.preventDefault();

        $('.loginform').show();
        $('.signupform').hide();

        return false;
    });

})( jQuery );


/* ==============================================
    Currency & Language Switcher
=============================================== */
(function($){ 

    $('.cl-switcher').change( function() {
        window.location.href = $(this).find(":selected").data('url');

        return false;
    });

})( jQuery );


/* ==============================================
    WooCommerce Single Product Image/Thumb slider
=============================================== */
function selectThumb( $images_slider, $thumbs_slider, index ) { 
    var len = $thumbs_slider.find('.owl-item').length,
        actives = [],
        i = 0,
        duration = 300;

    index = (index + len) % len;

    if ($images_slider) {
        $images_slider.trigger('to.owl.carousel', [index, duration, true]);
    }

    $thumbs_slider.find( '.owl-item' ).removeClass( 'selected' );
    $thumbs_slider.find( '.owl-item:eq(' + index + ')' ).addClass( 'selected' );
    $thumbs_slider.data( 'currentThumb', index );
    $thumbs_slider.find( '.owl-item.active' ).each( function() {
        actives[i++] = $(this).index();
    });

    if ( $.inArray( index, actives ) == -1 ) {
        if ( Math.abs(index - actives[0]) > Math.abs(index - actives[actives.length - 1]) ) {
            $thumbs_slider.trigger( 'to.owl.carousel', [(index - actives.length + 1) % len, duration, true] );
        } else {
            $thumbs_slider.trigger( 'to.owl.carousel', [index % len, duration, true] );
        }
    }
}

function wooProductImageSlider( $slider_container ) { 
    if ( $slider_container == undefined ) { 
        $slider_container = jQuery('.product-images-slider');
    }

    var $images_slider = $slider_container,
        $product = $images_slider.closest('.product-images'),
        $thumbs_slider = $product.find('.product-thumbs-slider'),
        thumbs_count = 4,
        currentSlide = 0,
        count = $images_slider.find('> *').length;

    $thumbs_slider.owlCarousel({ 
        rtl: is_rtl,
        loop : false,
        autoplay : false,
        items : thumbs_count,
        nav: false,
        navText: ["", ""],
        dots: false,
        rewind: true,
        stagePadding: 1,
        onInitialized: function() {
            selectThumb( null, $thumbs_slider, 0 );
            if ( $thumbs_slider.find('.owl-item').length >= thumbs_count )
                $thumbs_slider.append('<div class="thumb-nav"><div class="thumb-prev"></div><div class="thumb-next"></div></div>');
        }
    }).on('click', '.owl-item', function() {
        selectThumb( $images_slider, $thumbs_slider, $(this).index());
    });

    $thumbs_slider.on( 'click', '.thumb-prev', function(e) {
        var currentThumb = $thumbs_slider.data( 'currentThumb' );
        selectThumb( $images_slider, $thumbs_slider, --currentThumb );
    });

    $thumbs_slider.on( 'click', '.thumb-next', function(e) {
        var currentThumb = $thumbs_slider.data( 'currentThumb' );
        selectThumb( $images_slider, $thumbs_slider, ++currentThumb );
    });

    $images_slider.owlCarousel({
        rtl: is_rtl,
        loop : (count > 1) ? true : false,
        autoplay : false,
        items : 1,
        autoHeight : true,
        nav: true,
        navText: ["", ""],
        dots: false,
        rewind: true,
        onTranslate : function(event) {
            currentSlide = event.item.index - $images_slider.find('.cloned').length / 2;
            selectThumb( null, $thumbs_slider, currentSlide );
        }
    });
}

(function($, undefined){ 
    'use strict';

    $(window).load(function() { 
        wooProductImageSlider(); // Initialize Woo Product Image Slider
    });

})( jQuery );


/* ==============================================
    WooCommerce
=============================================== */
(function($, undefined){ 

    // Up-sells, Cross-sells and Related products slider
    if ( $('.related-products .owl-carousel').length > 0 ) { 
        $('.related-products .owl-carousel').each( function() { 

            var slider_options = $(this).data('slider'),
                slider_loop = false;

            if ( slider_options ) { 
                if ( slider_options.items === undefined ) { 
                    slider_options.items = 3;
                }

                if ( slider_options.slide_count === undefined ) { 
                    slider_options.slide_count = slider_options.items + 1;
                }

                slider_loop = ( slider_options.slide_count > slider_options.items )? true : false;

            $(this).owlCarousel({
                rtl : is_rtl,
                items : slider_options.items,
                loop : slider_loop,
                autoplay : true,
                autoplayTimeout : 4000,
                nav : true,
                    navText : ["", ""],
                    responsive : {
                        480 : {
                            items : 1,
                            nav : true
                        },
                        767 : {
                            items : 2,
                            nav : true
                        },
                        991 : {
                            items : slider_options.items,
                            nav : true
                        }
                    }
            });
            }

        });
    }

    $('.product-remove span.edit-product').click( function(e){
        window.open( $(this).attr('href') );
    });

    $(document).on( 'change', '.woocommerce-checkout #billing_country, .woocommerce-checkout #shipping_country, .shipping-calculator-form #calc_shipping_country', function(e) { 
        if ( $('#billing_state_field > input, #billing_state_field > select').length > 0 ) { 
            $('#billing_state_field > input, #billing_state_field > select').addClass( 'form-control' );
        }

        if ( $('#shipping_state_field > input, #shipping_state_field > select').length > 0 ) { 
            $('#shipping_state_field > input, #shipping_state_field > select').addClass( 'form-control' );
        }

        if ( $('#calc_shipping_state_field input, #calc_shipping_state_field select').length > 0 && !$('#calc_shipping_state_field input, #calc_shipping_state_field select').hasClass('form-control') ) {
            $('#calc_shipping_state_field input, #calc_shipping_state_field select').addClass( 'form-control' );
        }
    });

    // QuickView button action
    $(document).on( 'click', '.quickview_disabled', function(e) { 
        e.preventDefault();

        var pid = $(this).data('id');

        if ($('#ct_product_popup').length < 1) {
            $('<div class="opacity-overlay" id="ct_product_popup"><div class="container"><div class="popup-wrapper"><i class="icon-spin3 animate-spin"></i><div class="popup-content"></div></div></div></div>').appendTo('body');
        }

        $("#ct_product_popup").fadeIn();

        var product_content = null;

        $.ajax({
            url: ajaxurl,
            type: "POST",
            data: {
                'action' : 'ct_product_quickview',
                'pid' : pid
            },
            success: function(response){
                if (response.success == 1) {
                    $('#ct_product_popup .popup-content').html(response.output);

                    $popup_slider = $('#ct_product_popup .popup-content').find('.product-images-slider');
                    wooProductImageSlider($popup_slider);

                    $(".popup-content .numbers-row").append('<div class="inc button_inc">+</div><div class="dec button_inc">-</div>');
                } else {
                    alert( 'Please try again later' );
                }
            }
        });
    });

    // Price Filter on archive page
    if ( $('.widget.widget_price_filter .price_slider').length > 0 ) { 
        'use strict';

        var $price_slider = $('.widget.widget_price_filter .price_slider'),
            left = 0,
            width = 0;

        $price_slider.append( '<span class="price_from"></span>' );
        $price_slider.append( '<span class="price_to"></span>' );

        $('.widget.widget_price_filter .price_label .from').on( 'DOMSubtreeModified', function () {
            $price_slider.find('.price_from').text($(this).html());

            width = $price_slider.find('.price_from').width();
            left = $price_slider.find('span:nth-child(4)').css('left');
            left = parseInt( left.substring( 0, left.length - 2 ) ) - width/2;
            $price_slider.find('.price_from').css( 'left', left + 'px' );
        });

        $('.widget.widget_price_filter .price_label .to').on( 'DOMSubtreeModified', function () {
            $price_slider.find('.price_to').text( $(this).html() );

            width = $price_slider.find('.price_to').width();
            left = $price_slider.find('span:last-child').css('left');
            left = parseInt( left.substring( 0, left.length - 2 ) ) - width/2;
            $price_slider.find('.price_to').css( 'left', left + 'px' );
        });
    }

    // Mini Cart Scroll
    $(document).ready( function(){ 
        'use strict';

        if ( $('.dropdown-cart #cart_items').length > 0 ) { 

            var height = $('#cart_items .product_list_widget').height();

            // if ( height >= 150 ) { 
            //     $('#cart_items .product_list_widget').css( 'overflow-y', 'scroll' );
            // }
        }
    });

    // Ajax add-to-cart button
    $(document).on( 'added_to_cart', 'body', function( e, fragments, cart_hash, cart_btn ) { 
        'use strict';

        var $view_btn   = cart_btn.parent().find( '.added_to_cart' ),
            label       = $view_btn.html();

        $view_btn.html('<i class="fa fa-shopping-bag"></i><div class="tool-tip">' + label + '</div>');
        $view_btn.addClass('btn_shop');
        cart_btn.hide();
        
        if ( $('header #cart_items').length > 0 ) { 
            var nonce_value = $('header #cart_items #ajax_mini_cart').val();

            $.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    'action': 'ct_ajax_mini_cart',
                    'nonce' : nonce_value
                },
                success: function( response ){
                    if ( response.success ) {
                        $('header .cart-item-qty').text( response.cart_qty );
                        $('#cart_items').html( response.mini_cart );
                    }
                }
            });
        }
    } );
})( jQuery );


/* ==============================================
    Smooth Page Scroll to finding section
=============================================== */
(function($){ 
    $('#faq_box a[href*=#]:not([href=#])').click(function() {
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
            
            if (target.length) {
                $('html,body').animate({
                    scrollTop: target.offset().top - 110
                }, 500);
                return false;
            }
        }
    });
})( jQuery );

$('.filter_type select').change(function(){
	if($(this).val() !== 'empty'){
	  $(this).closest('.filter_type').next('.filter_type').slideDown();
	}
  });

/* ==============================================
	Preload
=============================================== */
$(window).load(function () { // makes sure the whole site is loaded
	$('#status').fadeOut(); // will first fade out the loading animation
	$('#preloader').delay(350).fadeOut('slow'); // will fade out the white DIV that covers the website.
	$('body').delay(350).css({
		'overflow': 'visible'
	});
	$(window).scroll();
})

/* ==============================================
	Sticky nav
=============================================== */
$(window).scroll(function () {
	'use strict';
	if ($(this).scrollTop() > 100) {
		$('header').addClass("sticky");
	} else {
		$('header').removeClass("sticky");
	}
});

/* ==============================================
	Menu
=============================================== */
$('a.open_close').on("click", function () {
	$('.main-menu').toggleClass('show');
	$('.layer').toggleClass('layer-is-visible');
});
$('a.show-submenu').on("click", function () {
	$(this).next().toggleClass("show_normal");
});
$('a.show-submenu-mega').on("click", function () {
	$(this).next().toggleClass("show_mega");
});
if ($(window).width() <= 480) {
	$('a.open_close').on("click", function () {
		$('.cmn-toggle-switch').removeClass('active')
	});
}

$(window).bind('resize load', function () {
	if ($(this).width() < 991) {
		$('.collapse#collapseFilters').removeClass('in');
		$('.collapse#collapseFilters').addClass('out');
	} else {
		$('.collapse#collapseFilters').removeClass('out');
		$('.collapse#collapseFilters').addClass('in');
	}
});

/* ==============================================
	Overaly mask form + incrementer
=============================================== */
$('.expose').on("click", function (e) {
	"use strict";
	$(this).css('z-index', '4');
	$('#overlay').fadeIn(300);
});
$('#overlay').click(function (e) {
	"use strict";
	$('#overlay').fadeOut(300, function () {
		$('.expose').css('z-index', '3');
	});
});

/* ==============================================
	Common
=============================================== */
/* Tooltip */
$('.tooltip-1').tooltip({
	html: true
});

/* Accordion */
function toggleChevron(e) {
	$(e.target)
		.prev('.panel-heading')
		.find("i.indicator")
		.toggleClass('icon-plus icon-minus');
}
$('.panel-group').on('hidden.bs.collapse shown.bs.collapse', toggleChevron);

/* Button show/hide map */
$(".btn_map").on("click", function () {
	var el = $(this);
	el.text() == el.data("text-swap") ? el.text(el.data("text-original")) : el.text(el.data("text-swap"));
});

/* Animation on scroll */
// new WOW().init();

/* Video modal dialog + Parallax + Scroll to top + Incrementer */
$(function () {
	'use strict';
	$('.video').magnificPopup({
		type: 'iframe'
	}); /* video modal*/
	$('.parallax-window').parallax({}); /* Parallax modal*/
	// Image popups

	$('.magnific-gallery').each(function () {
		$(this).magnificPopup({
			delegate: 'a',
			type: 'image',
			gallery: {
				enabled: true
			}
		});
	});

	$('.dropdown-menu').on("click", function (e) {
		e.stopPropagation();
	}); /* top drodown prevent close*/

	$('ul#top_tools li .dropdown').hover(function () {
		$(this).find('.dropdown-menu').stop(true, true).delay(50).fadeIn(300);
	}, function () {
		$(this).find('.dropdown-menu').stop(true, true).delay(50).fadeOut(300);
	});

	/* Hamburger icon */
	var toggles = document.querySelectorAll(".cmn-toggle-switch");

	for (var i = toggles.length - 1; i >= 0; i--) {
		var toggle = toggles[i];
		toggleHandler(toggle);
	};

	function toggleHandler(toggle) {
		toggle.addEventListener("click", function (e) {
			e.preventDefault();
			(this.classList.contains("active") === true) ? this.classList.remove("active"): this.classList.add("active");
		});
	};

	/* Scroll to top*/
	$(window).scroll(function () {
		if ($(this).scrollTop() != 0) {
			$('#toTop').fadeIn();
		} else {
			$('#toTop').fadeOut();
		}
	});
	$('#toTop').on("click", function () {
		$('body,html').animate({
			scrollTop: 0
		}, 500);
	});

	/* Input incrementer*/
//	$(".numbers-row").append('<div class="inc button_inc">+</div><div class="dec button_inc">-</div>');
//	$(".button_inc").on("click", function () {
//
//		var $button = $(this);
//		var oldValue = $button.parent().find("input").val();
//
//		if ($button.text() == "+") {
//			var newVal = parseFloat(oldValue) + 1;
//		} else {
//			// Don't allow decrementing below zero
//			if (oldValue > 1) {
//				var newVal = parseFloat(oldValue) - 1;
//			} else {
//				newVal = 0;
//			}
//		}
//		$button.parent().find("input").val(newVal);
//	});
});

/* Cat nav onclick active */
$('ul#cat_nav li a').on('click', function () {
	$('ul#cat_nav li a.active').removeClass('active');
	$(this).addClass('active');
});

/* Map filter onclick active */
$('#map_filter ul li a').on('click', function () {
	$('#map_filter ul li a.active').removeClass('active');
	$(this).addClass('active');
});

/* Input range slider */
$(function () {
	'use strict';
	$("#range").ionRangeSlider({
		hide_min_max: true,
		keyboard: true,
		min: 0,
		max: 150,
		from: 30,
		to: 100,
		type: 'double',
		step: 1,
		// prefix: "$",
		grid: true
	});

});

/* Footer reveal */
$('footer.revealed').footerReveal({
	shadow: false,
	opacity:0.6,
	zIndex: 0
});

/* Search */
$(".search-overlay-menu-btn").on("click", function (a) {
	$(".search-overlay-menu").addClass("open"), $('.search-overlay-menu > form > input[type="search"]').focus()
}), $(".search-overlay-close").on("click", function (a) {
	$(".search-overlay-menu").removeClass("open")
}), $(".search-overlay-menu, .search-overlay-menu .search-overlay-close").on("click keyup", function (a) {
	(a.target == this || "search-overlay-close" == a.target.className || 27 == a.keyCode) && $(this).removeClass("open")
});
