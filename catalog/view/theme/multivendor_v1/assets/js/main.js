(function($) {
  
  "use strict";  

  $(window).on('load', function() {
	  


/* Add To Cart Tooltip
  ========================================================*/
   $('.btn-cart').tooltip({title: "Add to Cart",});    
   $('.btn-wish').tooltip({title: "Wishlist",});    
   $('.btn-quickview').tooltip({title: "Quick View",});    


  /* slicknav mobile menu active  */
    $('.mobile-menu').slicknav({
        prependTo: '.navbar-header',
        parentTag: 'liner',
        allowParentLinks: true,
        duplicate: true,
        label: '',
        closedSymbol: '<i class="fa fa-angle-right"></i>',
        openedSymbol: '<i class="fa fa-angle-down"></i>',
      });


});  

/* Nav Menu Hover active
========================================================*/
$(".nav > li:has(ul)").addClass("drop");
$(".nav > li.drop > ul").addClass("dropdown");
$(".nav > li.drop > ul.dropdown ul").addClass("sup-dropdown");


/*Page Loader active
========================================================*/
$(window).on('load',function() {
  "use strict";
  $('#loader').fadeOut();
});

/* ==========================================================================
   New Products Owl Carousel
   ========================================================================== */
  $("#new-products").owlCarousel({
      navigation: true,
      pagination: false,
      slideSpeed: 1000,
      stopOnHover: true,
      autoPlay: true,
      items: 4,
      itemsDesktopSmall: [1024, 2],
      itemsTablet: [600, 1],
      itemsMobile: [479, 1]
  });
  $('#new-products').find('.owl-prev').html('<i class="fa fa-angle-left"></i>');
  $('#new-products').find('.owl-next').html('<i class="fa fa-angle-right"></i>');

/* Client Owl Carousel
========================================================*/
$("#client-logo").owlCarousel({
    navigation: false,
    pagination: false,
    slideSpeed: 1000,
    stopOnHover: true,
    autoPlay: true,
    items: 5,
    itemsDesktopSmall: [1024, 3],
    itemsTablet: [600, 1],
    itemsMobile: [479, 1]
});

/* Testimonials Carousel active
========================================================*/
var owl = $(".testimonials-carousel");
  owl.owlCarousel({
    navigation: false,
    pagination: true,
    slideSpeed: 1000,
    stopOnHover: true,
    autoPlay: true,
    items: 1,
    itemsDesktopSmall: [1024, 1],
    itemsTablet: [600, 1],
    itemsMobile: [479, 1]
  });

/* Touch Owl Carousel active
========================================================*/
var owl = $(".touch-slider");
  owl.owlCarousel({
    navigation: true,
    pagination: false,
    slideSpeed: 1000,
    stopOnHover: true,
    autoPlay: true,
    items: 1,
    itemsDesktopSmall: [1024, 1],
    itemsTablet: [600, 1],
    itemsMobile: [479, 1]
  });

$('.touch-slider').find('.owl-prev').html('<i class="fa fa-angle-left"></i>');
$('.touch-slider').find('.owl-next').html('<i class="fa fa-angle-right"></i>');

$('.testimonials-carousel').find('.owl-prev').html('<i class="fa fa-angle-left"></i>');
$('.testimonials-carousel').find('.owl-next').html('<i class="fa fa-angle-right"></i>');

/* owl Carousel active
========================================================*/   
var owl;
$(window).on('load', function() {
    owl = $("#owl-demo");
    owl.owlCarousel({
        navigation: false, // Show next and prev buttons
        slideSpeed: 300,
        paginationSpeed: 400,
        singleItem: true,
        afterInit: afterOWLinit, // do some work after OWL init
        afterUpdate : afterOWLinit
    });

    function afterOWLinit() {
        // adding A to div.owl-page
        $('.owl-controls .owl-page').append('<a class="item-link" />');
        var pafinatorsLink = $('.owl-controls .item-link');
        /**
         * this.owl.userItems - it's your HTML <div class="item"><img src="http://www.ow...t of us"></div>
         */
        $.each(this.owl.userItems, function (i) {
          $(pafinatorsLink[i])
              // i - counter
              // Give some styles and set background image for pagination item
              .css({
                  'background': 'url(' + $(this).find('img').attr('src') + ') center center no-repeat',
                  '-webkit-background-size': 'cover',
                  '-moz-background-size': 'cover',
                  '-o-background-size': 'cover',
                  'background-size': 'cover'
              })
              // set Custom Event for pagination item
              .on('click',function () {
                  owl.trigger('owl.goTo', i);
              });

        });
         // add Custom PREV NEXT controls
        $('.owl-pagination').prepend('<a href="#prev" class="prev-owl"/>');
        $('.owl-pagination').append('<a href="#next" class="next-owl"/>');
        // set Custom event for NEXT custom control
        $(".next-owl").on('click',function () {
            owl.trigger('owl.next');
        });
        // set Custom event for PREV custom control
        $(".prev-owl").on('click',function () {
            owl.trigger('owl.prev');
        });
    }
});

/* Toggle active
========================================================*/
  var o = $('.toggle');
  $(window).on('load', function() {
    $('.toggle').on('click',function (e) {
      e.preventDefault();
      var tmp = $(this);
      o.each(function () {
        if ($(this).hasClass('active') && !$(this).is(tmp)) {
          $(this).parent().find('.toggle_cont').slideToggle();
          $(this).removeClass('active');
        }
      });
      $(this).toggleClass('active');
      $(this).parent().find('.toggle_cont').slideToggle();
    });
    $(window).on('click touchstart', function (e) {
      var container = $(".toggle-wrap");
      if (!container.is(e.target) && container.has(e.target).length === 0 && container.find('.toggle').hasClass('active')) { 
        container.find('.active').toggleClass('active').parent().find('.toggle_cont').slideToggle();
      }
    });
  });
  

/* Back Top Link active
========================================================*/
  var offset = 200;
  var duration = 500;
  $(window).scroll(function() {
    if ($(this).scrollTop() > offset) {
      $('.back-to-top').fadeIn(400);
    } else {
      $('.back-to-top').fadeOut(400);
    }
  });

  $('.back-to-top').on('click',function(event) {
    event.preventDefault();
    $('html, body').animate({
      scrollTop: 0
    }, 600);
    return false;
  })

 /*  Select Color Active
  ========================================================*/
  $("div.color-list .color").click(function(e){
    e.preventDefault();
    $(this).parent().parent().find(".color").removeClass("active");
    $(this).addClass("active");
  })
  

}(jQuery));