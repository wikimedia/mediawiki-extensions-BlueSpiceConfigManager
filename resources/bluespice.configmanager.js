( function ( mw, bs, $, undefined ) {
	mw.loader.using( ['ext.bluespice.extjs', 'ext.bluespice'] ).done( function() {
		Ext.onReady( function(){
			Ext.Loader.setPath(
				'BS.ConfigManager',
				bs.em.paths.get( 'BlueSpiceConfigManager' ) + '/resources/BS.ConfigManager'
			);
			Ext.create( 'BS.ConfigManager.panel.Manager', {
				renderTo: 'bs-configmanager'
			});
		});
	});
}( mediaWiki, blueSpice, jQuery ) );
