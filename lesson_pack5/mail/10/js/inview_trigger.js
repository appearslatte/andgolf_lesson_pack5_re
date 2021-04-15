$(function() {
    
	$('.inviewfadeInLine').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(this).stop().addClass('fadeInLine');
		} 
	});
	
	$('.inviewfadeInUp').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(this).stop().addClass('fadeInUp');
		} 
	});
    
    $('.inviewfadeInRight').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(this).stop().addClass('fadeInRight');
		} 
	});
    
    $('.inviewfadeInDown').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(this).stop().addClass('fadeInDown');
		} 
	});
    
    $('.inviewfadeInLeft').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(this).stop().addClass('fadeInLeft');
		} 
	});
    
    $('.inviewfadeInCta').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(".fixed_cta").stop().addClass('fadeInCta');
		} 
	});
    
    $('.inviewfadeInSpCta').on('inview', function(event, isInView, visiblePartX, visiblePartY) {
		if (isInView) {
			$(".sp_cta").stop().addClass('sp_cta_on');
		} 
	});
    

});