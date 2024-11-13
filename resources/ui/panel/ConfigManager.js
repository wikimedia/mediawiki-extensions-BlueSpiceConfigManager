bs.util.registerNamespace( 'bs.configmanager.ui' );
bs.util.registerNamespace( 'bs.configmanager.ui.panel' );

require( '../pages/ConfigPage.js' );
require( '../toolbar/ConfigToolbar.js' );
require( '../booklet/ConfigBooklet.js' );

bs.configmanager.ui.panel.ConfigManager = function ( cfg ) {
	cfg = cfg || {};
	this.pathnames = cfg.pathnames;
	bs.configmanager.ui.panel.ConfigManager.super.call( this, cfg );
	this.paths = {};
	this.mainPaths = [];
	this.pageNames = [];
	this.openChanges = false;
	this.store = new OOJSPlus.ui.data.store.RemoteStore( {
		action: 'bs-configmanager-store',
		pageSize: 999
	} );
	this.$element = $( '<div>' ).addClass( 'bs-configmanager-panel' );
	this.$content = $( '<div>' ).addClass( 'bs-configmanager-content' );

	this.store.connect( this, {
		loaded: function ( data ) {
			this.openChanges = false;
			this.paths = {};
			this.data = data;
			this.setupMainPaths();
			this.setup();
			this.emit( 'loaded' );
		}
	} );
	this.store.load();
	this.$element.append( this.$content );
};

OO.inheritClass( bs.configmanager.ui.panel.ConfigManager, OO.ui.Widget );

bs.configmanager.ui.panel.ConfigManager.prototype.setupMainPaths = function() {
	for ( var i in this.data ) {
		this.data[ i ].paths.forEach( function( path ) {
			var sections = path.split( '/' );
			if ( !this.paths[ sections[ 0 ] ] ) {
				this.paths[ sections[ 0 ] ] = [];
			}
			if ( this.paths[ sections[ 0 ] ].includes( sections[ 1 ] ) ) {
				return;
			}
			this.paths[ sections[ 0 ] ].push( sections[ 1 ] );
		}.bind( this ) );
	}

	this.activePath = Object.keys( this.paths )[ 0 ];
	for ( var key in this.paths ) {
		this.mainPaths.push( key );
	}
};

bs.configmanager.ui.panel.ConfigManager.prototype.setup = function() {
	if ( !this.search ) {
		this.setupSearch();
	}
	if ( !this.toolbar ) {
		this.setupToolbar();
	}
	this.setupBooklet();
};

bs.configmanager.ui.panel.ConfigManager.prototype.setupBooklet = function () {
	$( this.$content ).empty();

	var pageData = this.paths[ this.activePath ];
	var pages = [];
	for ( var page in pageData ) {
		var label = pageData[ page ];
		if ( this.pathnames[ pageData[ page ] ] ) {
			label = mw.message( this.pathnames[ pageData[ page ] ] ).text();
		}
		pages.push( {
			key: pageData[ page ],
			label: label
		} );
	};
	pages.sort( ( a, b ) => a.label.localeCompare( b.label ) );
	this.bookletLayout = new bs.configmanager.ui.booklet.ConfigBooklet( {
		outlined: true,
		expanded: false,
		manager: this,
		classes: [ 'bs-configmanager-panel-booklet' ]
	} );
	this.bookletLayout.connect( this, {
		keep: function ( item ) {
			OO.ui.confirm( mw.message( 'bs-configmanager-discard-open-changes' ).text() )
			.done( function ( confirmed ) {
				if ( confirmed ) {
					this.openChanges = false;
					this.bookletLayout.setPage( item.data );
				}
			}.bind( this ) );
		},
		set: function () {
			this.openChanges = false;
			this.toolbar.tools.save.setDisabled( true );
		}
	} );
	var configPages = [];
	for ( var page in pages ) {
		var key = pages[ page ].key;
		activeRecords = [];
		for ( var i in this.data ) {
			this.data[ i ].paths.forEach( function( path ) {
				var sections = path.split( '/' );
				if( key !== sections[1] ) {
					return;
				}
				activeRecords.push( this.data[ i ] );
			}.bind( this ) );
		}
		var label = pages[ page ].label;

		let configPage = new bs.configmanager.ui.pages.ConfigPage(
			key, label, activeRecords
		);
		configPage.connect( this, {
			change: 'onOpenChange'
		} );
		configPages.push( configPage );
	}
	this.bookletLayout.addPages( configPages );
	this.bookletLayout.selectFirstSelectablePage();
	this.selectedPage = this.bookletLayout.getCurrentPage();
	this.$content.append( this.bookletLayout.$element );
	for ( var i in configPages ) {
		var currentPage = configPages[i];
		currentPage.infuseWidgets( currentPage.$element );
	}
};

bs.configmanager.ui.panel.ConfigManager.prototype.onOpenChange = function () {
	this.openChanges = true;
	this.toolbar.tools.save.setDisabled( false );
};

bs.configmanager.ui.panel.ConfigManager.prototype.hasOpenChange = function () {
	return this.openChanges;
};

bs.configmanager.ui.panel.ConfigManager.prototype.setupSearch = function() {
	this.search = new OO.ui.SearchInputWidget();
	this.search.connect( this, {
		change: function ( value ) {
			this.store.query( value )
		}
	} );

	this.$element.prepend(
		new OO.ui.FieldLayout( this.search, {
			label: mw.message( 'bs-configmanager-search' ).text(),
			classes: [ 'bs-configmanager-search' ]
	} ).$element );
};

bs.configmanager.ui.panel.ConfigManager.prototype.setupToolbar = function() {
	this.toolbar = new bs.configmanager.ui.toolbar.ConfigToolbar();
	this.toolbar.connect( this, {
		reset: function() {
			this.store.reload();
		},
		mode: function ( mode ) {
			this.activePath = mode;
			this.setupBooklet();
		},
		save: function() {
			var page = this.bookletLayout.getCurrentPage();
			var saveData = page.getData();
			var $dfd = $.Deferred();
			bs.api.tasks.execSilent( 'configmanager', 'save', saveData )
			.done( function( response ) {
				if ( response.error ) {
					bs.util.alert(
						'configmanager-save-fail',
						{
							titleMsg: 'Error',
							text: response.error.info
						},
						{
							ok: function() {
								$dfd.reject( response );
							}
						}
					);
				} else {
					mw.notify( mw.message( 'bs-configmanager-notify-configuration-saved' ).text() );
					this.toolbar.tools.save.setDisabled( true );
				}
				this.store.reload();
				return $dfd.resolve( this );
			}.bind( this ) );
		}
	} );
	this.$element.prepend( this.toolbar.$element );
};