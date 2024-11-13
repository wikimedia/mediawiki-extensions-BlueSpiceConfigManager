( function ( mw, $, bs, d, undefined ) {

	$( function () {
		require( './ui/panel/ConfigManager.js' );
		var path = require( './pathNames.json' );
		var offset = require( './offset.json' );

		var $configManagerCnt = $( '#bs-configmanager' );
		var configManager = new bs.configmanager.ui.panel.ConfigManager( {
			pathnames: path.pathnames
		} );
		$configManagerCnt.append( configManager.$element );

		configManager.connect( this, {
			loaded: function () {
				var floatingToolbar = false;
				var offsetHeight = offset.offset;
				var $toolbar = $( '.bs-configmanager-toolbar' );
				var topValue = $( $toolbar ).offset().top;
				var contentWidth = getContentWidth();
				$( window ).on( 'scroll', function () {
					var windowTop = $( this ).scrollTop();
					if ( windowTop > topValue ) {
						if ( !floatingToolbar ) {
							$toolbar.css( 'top', offsetHeight );
							$toolbar.css( 'position', 'fixed' );
							$toolbar.css( 'width', contentWidth );
							$toolbar.css( 'z-index', 5 );
							floatingToolbar = true;
						}
					} else {
						if ( floatingToolbar ) {
							$toolbar.removeAttr( 'style' );
							floatingToolbar = false;
						}
					}
				} );
			}
		} );
	} );

	function getContentWidth() {
		return $( '#mw-content-text' ).innerWidth();
	}

} )( mediaWiki, jQuery, blueSpice, document );