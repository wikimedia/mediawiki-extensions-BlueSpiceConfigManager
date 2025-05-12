( function ( $, bs ) {

	$( function () {
		require( './ui/panel/ConfigManager.js' );
		const path = require( './pathNames.json' );
		const offset = require( './offset.json' );

		const $configManagerCnt = $( '#bs-configmanager' );
		const configManager = new bs.configmanager.ui.panel.ConfigManager( {
			pathnames: path.pathnames
		} );
		$configManagerCnt.append( configManager.$element );

		configManager.connect( this, {
			loaded: function () {
				if ( $( document ).find( '#bs-configManager-skeleton-cnt' ) ) {
					$( '#bs-configManager-skeleton-cnt' ).empty();
				}
				let floatingToolbar = false;
				const offsetHeight = offset.offset;
				const $toolbar = $( '.bs-configmanager-toolbar' );
				const topValue = $( $toolbar ).offset().top;
				const contentWidth = getContentWidth();
				$( window ).on( 'scroll', function () {
					const windowTop = $( this ).scrollTop();
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

}( jQuery, blueSpice ) );
