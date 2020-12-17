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

});