bs.util.registerNamespace( 'bs.configmanager.ui' );
bs.util.registerNamespace( 'bs.configmanager.ui.toolbar' );

bs.configmanager.ui.toolbar.ConfigToolbar = function ( config ) {
	config = config || {};
	config.classes = [ 'bs-configmanager-toolbar' ];
	bs.configmanager.ui.toolbar.ConfigToolbar.super.call( this,
		new OO.ui.ToolFactory(), new OO.ui.ToolGroupFactory(), config
	);
	this.addNewItemTool();
	this.setup( [
		{
			name: 'modi',
			type: 'menu',
			header: mw.message( 'bs-configmanager-mainpath' ).plain(),
			title: mw.message( 'bs-configmanager-mainpath' ).plain(),
			promote: [ 'feature' ],
			include: [ 'feature', 'extension', 'package' ]
		},
		{
			name: 'save',
			type: 'bar',
			classes: [ 'toolbar-actions' ],
			include: [ 'reset', 'save' ],
			align: 'after'
		}
	] );

	this.initialize();
	this.emit( 'updateState' );
};

OO.inheritClass( bs.configmanager.ui.toolbar.ConfigToolbar, OO.ui.Toolbar );

bs.configmanager.ui.toolbar.ConfigToolbar.prototype.addNewItemTool = function () {
	this.toolFactory.register( bs.configmanager.ui.toolbar.SaveTool );
	this.toolFactory.register( bs.configmanager.ui.toolbar.ResetTool );
	this.toolFactory.register( bs.configmanager.ui.toolbar.ShowFunctionTool );
	this.toolFactory.register( bs.configmanager.ui.toolbar.ShowExtensionTool );
	this.toolFactory.register( bs.configmanager.ui.toolbar.ShowPackageTool );
};

bs.configmanager.ui.toolbar.SaveTool = function () {
	bs.configmanager.ui.toolbar.SaveTool.super.apply( this, arguments );
	this.setDisabled( true );
};

OO.inheritClass( bs.configmanager.ui.toolbar.SaveTool, OO.ui.Tool );
bs.configmanager.ui.toolbar.SaveTool.static.name = 'save';
bs.configmanager.ui.toolbar.SaveTool.static.icon = '';
bs.configmanager.ui.toolbar.SaveTool.static.title = 'Save';
bs.configmanager.ui.toolbar.SaveTool.static.flags = [ 'primary', 'progressive' ];
bs.configmanager.ui.toolbar.SaveTool.static.displayBothIconAndLabel = true;

bs.configmanager.ui.toolbar.SaveTool.prototype.onSelect = function () {
	this.setActive( false );
	this.toolbar.emit( 'save' );
	this.toolbar.emit( 'updateState' );
};
bs.configmanager.ui.toolbar.SaveTool.prototype.onUpdateState = function () {};

bs.configmanager.ui.toolbar.ResetTool = function () {
	bs.configmanager.ui.toolbar.ResetTool.super.apply( this, arguments );
};

OO.inheritClass( bs.configmanager.ui.toolbar.ResetTool, OO.ui.Tool );
bs.configmanager.ui.toolbar.ResetTool.static.name = 'reset';
bs.configmanager.ui.toolbar.ResetTool.static.icon = '';
bs.configmanager.ui.toolbar.ResetTool.static.title = 'Reset';
bs.configmanager.ui.toolbar.ResetTool.static.flags = [];
bs.configmanager.ui.toolbar.ResetTool.static.displayBothIconAndLabel = true;
bs.configmanager.ui.toolbar.ResetTool.prototype.onSelect = function () {
	this.setActive( false );
	this.toolbar.emit( 'reset' );
	this.toolbar.emit( 'updateState' );
};
bs.configmanager.ui.toolbar.ResetTool.prototype.onUpdateState = function () {};

bs.configmanager.ui.toolbar.ShowFunctionTool = function () {
	bs.configmanager.ui.toolbar.ShowFunctionTool.super.apply( this, arguments );
	this.setActive( true );
	this.mode = 'feature';
};

OO.inheritClass( bs.configmanager.ui.toolbar.ShowFunctionTool, OO.ui.Tool );
bs.configmanager.ui.toolbar.ShowFunctionTool.static.name = 'feature';
bs.configmanager.ui.toolbar.ShowFunctionTool.static.icon = '';
bs.configmanager.ui.toolbar.ShowFunctionTool.static.title = mw.message( 'bs-setting-path-feature' ).text();
bs.configmanager.ui.toolbar.ShowFunctionTool.static.flags = [];

bs.configmanager.ui.toolbar.ShowFunctionTool.prototype.onSelect = function () {
	// Set all tools to false otherwise all will be active
	this.toolGroup.items.forEach( ( tool ) => {
		tool.setActive( false );
	} );
	this.setActive( true );
	this.toolbar.emit( 'mode', this.mode );
	this.toolbar.emit( 'updateState' );
};
bs.configmanager.ui.toolbar.ShowFunctionTool.prototype.onUpdateState = function () {};

bs.configmanager.ui.toolbar.ShowExtensionTool = function () {
	bs.configmanager.ui.toolbar.ShowExtensionTool.super.apply( this, arguments );
	this.setActive( false );
	this.mode = 'extension';
};

OO.inheritClass( bs.configmanager.ui.toolbar.ShowExtensionTool, bs.configmanager.ui.toolbar.ShowFunctionTool );
bs.configmanager.ui.toolbar.ShowExtensionTool.static.name = 'extension';
bs.configmanager.ui.toolbar.ShowExtensionTool.static.title = mw.message( 'bs-setting-path-extension' ).text();
bs.configmanager.ui.toolbar.ShowExtensionTool.static.mode = 'extension';
bs.configmanager.ui.toolbar.ShowExtensionTool.prototype.onUpdateState = function () {};

bs.configmanager.ui.toolbar.ShowPackageTool = function () {
	bs.configmanager.ui.toolbar.ShowPackageTool.super.apply( this, arguments );
	this.setActive( false );
	this.mode = 'package';
};

OO.inheritClass( bs.configmanager.ui.toolbar.ShowPackageTool, bs.configmanager.ui.toolbar.ShowFunctionTool );
bs.configmanager.ui.toolbar.ShowPackageTool.static.name = 'package';
bs.configmanager.ui.toolbar.ShowPackageTool.static.title = mw.message( 'bs-setting-path-package' ).text();
bs.configmanager.ui.toolbar.ShowPackageTool.prototype.onUpdateState = function () {};
