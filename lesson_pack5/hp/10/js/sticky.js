$(function(){
	var glovalNav = $('header');　//　グローバルナビゲーションのセレクタ
	var navHeight = glovalNav.outerHeight(true); //　ナビゲーションのheight(marginやpadding,box-shadowも含む)
	var navOffset = glovalNav.offset().top; //　ブラウザの一番上からナビゲーションまでの位置

	// スクロールした時
	$(window).scroll(function () {
		// スクロールがナビゲーション位置に来たら
		if ($(this).scrollTop() > navOffset) {
			// ナビゲーションをfixedさせる
			glovalNav.css({
				'position':'fixed',
				'top':0,
				'width':'100%',
			});
			// ここは重要な部分で、fixedするとその分高さがなくなるので、margin-topを高さ分つけてあげます。
			$('#xxx').css('margin-top','18vw');
		} else {
			// ifのtrueでない場合、つまり、ナビゲーション位置に来ていない時はrelativeで元に戻す。staticでも可
			glovalNav.css('position','relative');
			$('#xxx').css('margin-top','auto');
		}
	});
});