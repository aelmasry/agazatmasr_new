jQuery(document).ready(function($) {
        // Initialize Slick Slider for mobile devices
        function initSlider() {
            if (window.innerWidth <= 768) {
                if (!$(".taxonomy-selection-items").hasClass("slick-initialized")) {
                    $(".taxonomy-selection-items").slick({
                        dots: true,
                        arrows: true,
                        infinite: true,
                        speed: 300,
                        slidesToShow: 1,
                        adaptiveHeight: true,
                        autoplay: true,
                        autoplaySpeed: 5000
                    });
                }
            } else {
                if ($(".taxonomy-selection-items").hasClass("slick-initialized")) {
                    $(".taxonomy-selection-items").slick("unslick");
                }
            }
        }
        
        // Initialize on load
        initSlider();
        
        // Reinitialize on window resize
        $(window).on("resize", function() {
            initSlider();
        });
    });