//SMOOTH SCROLL
$(function(){
	$('a[href^=#]').click(function(){
		var speed = 500;
		var href= $(this).attr("href");
		var target = $(href == "#" || href == "" ? 'html' : href);
		var position = target.offset().top;
		$("html, body").animate({scrollTop:position}, speed, "swing");
		return false;
	});
});


//BACK TO TOP
$(function() {
    var topBtn = $('.BackToTop');    
     $(window).scroll(function () {
        if ($(this).scrollTop() > 700) {
            topBtn.fadeIn();
        } else {
            topBtn.fadeOut();
        }
    });
});


//CTA
$(function() {
    var topBtn = $('.fixed_cta');    
     $(window).scroll(function () {
        if ($(this).scrollTop() > 4500) {
            topBtn.addClass('cta_on');
        } else {
            topBtn.removeClass('cta_on');
        }
    });
});


//CTA
$(function() {
    var topBtn = $('.sp_cta');    
     $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            topBtn.addClass('sp_cta_on');
        } else {
            topBtn.removeClass('sp_cta_on');
        }
    });
});