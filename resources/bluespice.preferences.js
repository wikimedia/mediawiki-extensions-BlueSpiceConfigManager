( function ( mw, bs, $, undefined ) {
	$(function(){
		$('.bs-prefs .bs-prefs-head').click( function() {
			var oPrefsBody = $( this ).parent().find( '.bs-prefs-body' ),
				sCookieKey = $( this ).parent().attr( 'id' )+'-viewstate';
			if ( oPrefsBody.is( ":visible" ) == true ) {
				$(oPrefsBody[0]).slideUp(500);
				$(oPrefsBody[0]).parent().addClass('bs-prefs-viewstate-collapsed');
				$.cookie(sCookieKey, null, {
					path: '/'
				});
			} else {
				$(oPrefsBody[0]).slideDown(500);
				$(oPrefsBody[0]).parent().removeClass('bs-prefs-viewstate-collapsed');
				$.cookie(sCookieKey, 'opened', {
					path: '/',
					expires: 10
				});
			}
		}).each( function() {
			var oPrefsBody = $(this).parent().find('.bs-prefs-body'),
				sCookieKey = $(this).parent().attr('id')+'-viewstate';
			if ( sCookieKey != 'bluespice-viewstate' && ($.cookie( sCookieKey ) == null || $.cookie( sCookieKey ) != 'opened')) {
				oPrefsBody.hide();
				$(oPrefsBody[0]).parent().addClass('bs-prefs-viewstate-collapsed');
			}
		});
	});
}( mediaWiki, blueSpice, jQuery ) );