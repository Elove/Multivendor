	$(document).ready(function() {


	// Product List
	$('#list-view').click(function() {
		//alert("sup");
		//alert('hey');
		//$('#product-layout > .col-md-4 col-sm-6 col-xs-12').remove();<div class="shop-list">
		//$('#product-layout').removeClass('col-md-4 col-sm-6 col-xs-12');

		$('#product-layout').addClass('shop-list'); 
		$(".shop-product").addClass("clearfix");
		// $(".product-title").addClass("pull-left");
		$(".the-price").removeClass("pull-left fix mb-10");
		$(".common-align-items").removeClass("align-items").addClass("fix");
		$(".common-reviews-icon").detach().appendTo('.for-list-view');
		//$(".the-price").detach().appendTo('.list-view-price').addClass("fix mb-10");
		// $('.the-price').each(function() {
		// 	var currentPrice = $(this);
		//    //var currentPrice = $(this).detach().appendTo('.list-view-price').addClass("fix mb-10");
		//     	$('.list-view-price').each(function() {
		//     		currentPrice.detach().appendTo('.list-view-price').addClass("fix mb-10");
		//     	    //$(this).detach().appendTo('.list-view-price').addClass("fix mb-10");
		//     	});
		// });
		// $(".the-price").addClass("fix mb-10");
		$(".common-reviews-icon").addClass("star-rating pull-right");
		$('.product-description,common-price').show();
		$('.list-view-price').addClass("fix mb-10").show();
		$('.the-price').hide();
		$(".add-cart-btn").show();
		$(".pull-right-review").removeClass("pull-left");
		$(".product-common").addClass("col-md-12").removeClass("col-md-4 col-sm-6 col-xs-12");
		// var cusid_ele = document.getElementsById('single-product');
		// for (var i = 0; i < cusid_ele.length; ++i) {
		//     var item = cusid_ele[i]; 
		//     item.addClass('active');
		//     console.log(item);
		//     //item.removeClass('col-md-4 col-sm-6 col-xs-12');
		// }
		//.removeClass('col-md-4 col-sm-6 col-xs-12'); 

		//$('#content .row > .product-grid').attr('class', 'shop-list col-md-12');
		$('#grid-view').removeClass('active');
		$('#list-view').addClass('active');

		localStorage.setItem('display', 'list');
	});

	// Product Grid
	$('#grid-view').click(function() {
		// What a shame bootstrap does not take into account dynamically loaded columns
		$('#product-layout').removeClass('shop-list'); 
		$(".shop-product").removeClass("clearfix");
		// $(".product-title").addClass("pull-left");
		$(".the-price").addClass("pull-left fix mb-10");
		$(".common-align-items").addClass("align-items").removeClass("fix");

		$(".common-reviews-icon").removeClass("star-rating pull-right");
		$('.product-description,common-price').hide();
		$('.list-view-price').removeClass("fix mb-10").hide();
		$('.the-price').show();
		$(".pull-right-review").addClass("pull-left");
		$(".add-cart-btn").hide();
		$(".product-common").removeClass("col-md-12").addClass("col-md-4 col-sm-6 col-xs-12");

		$('#list-view').removeClass('active');
		$('#grid-view').addClass('active');

		localStorage.setItem('display', 'grid');
	});

	if (localStorage.getItem('display') == 'list') {
		$('#list-view').trigger('click');
		$('#list-view').addClass('active');
	} else {
		$('#grid-view').trigger('click');
		$('#grid-view').addClass('active');
	}
});