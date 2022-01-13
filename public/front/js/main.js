(function($) {

	"use strict";

	var fullHeight = function() {

		$('.js-fullheight').css('height', $(window).height());
		$(window).resize(function(){
			$('.js-fullheight').css('height', $(window).height());
		});
		/* ==============================================
        Fixed menu
        =============================================== */

		$(window).on('scroll', function () {
			if ($(window).scrollTop() > 50) {
				$('.header_style').addClass('fixed-menu');
			} else {
				$('.header_style').removeClass('fixed-menu');
			}
		});
	};
	fullHeight();

	var carousel = function() {
		$('.featured-carousel').owlCarousel({
	    loop:true,
	    autoplay: true,
	    margin:30,
	    animateOut: 'fadeOut',
	    animateIn: 'fadeIn',
	    nav:true,
	    dots: true,
	    autoplayHoverPause: false,
	    items: 1,
	    navText : ["<p><small>Prev</small><span class='fa fa-arrow-left'></span></p>","<p><small>Next</small><span class='fa fa-arrow-right'></span></p>"],
	    responsive:{
	      0:{
	        items:1
	      },
	      600:{
	        items:1
	      },
	      1000:{
	        items:1
	      }
	    }
		});

	};
	var thmbxSlider=  function thmbxSlider() {
	if ($('.btc-static-ticker-slider').length) {
		alert("test")
		$('.btc-static-ticker-slider').bxSlider({
			minSlides: 1,
			maxSlides: 7,
			slideWidth: 270,
			slideMargin: 10,
			useCSS: false,
			ticker: true,
			autoHover:true,
			tickerHover:true,
			speed: 100000,
			infiniteLoop: true
		});
	};
	 }
var thmOwlCarousel=	function thmOwlCarousel() {
		if ($('.brand-carousel').length) {
			$('.brand-carousel').owlCarousel({
				loop: true,
				margin: 70,
				nav: false,
				navText: [
					'<i class="fa fa-angle-left"></i>',
					'<i class="fa fa-angle-right"></i>'
				],
				dots: false,
				autoWidth: false,
				autoplay: true,
				autoplayTimeout: 3000,
				autoplayHoverPause: true,
				responsive: {
					0: {
						items: 2
					},
					480: {
						items: 3
					},
					600: {
						items: 4
					},
					1000: {
						items: 5
					}
				}
			});
		};
	}
	carousel();
	thmOwlCarousel();
})(jQuery);