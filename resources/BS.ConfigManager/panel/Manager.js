Ext.define( 'BS.ConfigManager.panel.Manager', {
	extend: 'Ext.Panel',
	header: false,
	layout: 'border',
	id: 'bs-configmanager-panel',
	requires: [
		'BS.store.BSApi',
		'BS.ConfigManager.grid.Paths',
		'BS.ConfigManager.panel.Configs'
	],
	minHeight: 600,
	autoHeight: true,
	paths: null,
	mainPathSelection: null,
	updateSearchField: null,

	initComponent: function() {
		var me = this;

		me.setLoading( true );
		this.store = new BS.store.BSApi({
			apiAction: 'bs-configmanager-store',
			proxy: {
				extraParams: {
					limit: 9999 //Bad hack to avoid paging
				}
			},
			fields: [
				's_name',
				's_value',
				'paths',
				'label',
				'form'
			]
		});

		this.storeMainPath = new Ext.data.ArrayStore( {
			fields: [
				'mainpath',
				'mainpathdisplay'
			]
		});

		this.cbMainPath = Ext.create( 'Ext.form.field.ComboBox', {
			store: this.storeMainPath,
			valueField: 'mainpath',
			displayField: 'mainpathdisplay',
			queryMode: 'local',
			forceSelection: true,
			allowBlank: false,
			flex: 2
		} );
		this.cbMainPath.on( 'change', function( combo, value, eOpts ) {
			me.mainPathSelection = value;
		});
		this.cbMainPath.on( 'beforeselect', function( combo, record, index, eOpts ) {
			if( me.pnlConfig.isDirty() === true ) {
				updater = function() {
					combo.select( record );
				};
				me.confirmDiscardChanges( updater );
				return false;
			}
			return true;
		});

		this.tfSearch = Ext.create( 'Ext.form.field.Text', {
			flex: 4
		});
		this.tfSearch.on( 'change', me.onSearchFieldChanged, this );

		this.btnOK = Ext.create( 'Ext.Button', {
			text: mw.message( 'bs-extjs-save' ).plain(),
			id: this.getId() + '-btn-ok',
			cls: 'bs-extjs-btn-ok',
			disabled: true,
			flex: 1
		});
		this.btnOK.on( 'click', me.onBtnOKClick, this );

		this.btnReset = Ext.create( 'Ext.Button', {
			text: mw.message( 'bs-extjs-reset' ).plain(),
			id: this.getId() + '-btn-reset',
			cls: 'bs-extjs-btn-reset',
			disabled: true,
			flex: 1
		});
		this.btnReset.on( 'click', me.onBtnResetClick, this );

		var me = this;
		this.pnlPath = Ext.create( 'BS.ConfigManager.grid.Paths', {
			header: false,
			region: 'west',
			manager: me,
			flex: 2
		});

		this.pnlConfig = Ext.create( 'BS.ConfigManager.panel.Configs', {
			region: 'center',
			store: this.store,
			manager: me,
			flex: 6
		});
		this.pnlConfig.on( 'dirty', me.onConfigDirty, this )

		this.store.on( 'load', this.resolvePaths, this );
		this.on( 'pathsresolved', this.fillMainPathStore, this );

		this.tbar = this.makeTbar();

		this.items = [
			this.pnlPath,
			this.pnlConfig
		];

		this.callParent();

	},

	resolvePaths: function( store, records, successful, operation, eOpts ) {
		var me = this;
		me.pnlConfig.setDirty( false );
		me.paths = {};
		me.btnOK.disable();
		me.btnReset.disable();
		for( var i = 0; i < records.length; i++ ) {
			records[i].get( 'paths' ).forEach( function( path ) {
				var sections = path.split( '/' );
				if( !me.paths[sections[0]] ) {
					me.paths[sections[0]] = [];
				}
				me.paths[sections[0]].push(sections[1]);
			});
		}

		if( !me.mainPathSelection || !me.paths[me.mainPathSelection] ) {
			me.mainPathSelection = Object.keys(me.paths)[0];
		}
		me.pnlConfig.setLoading( false );

		this.fireEvent( 'pathsresolved', me, me.mainPathSelection, me.paths );
	},

	fillMainPathStore: function( manager, mainPathSelection, paths ) {
		var storeData = [];
		var mainpaths = Object.keys( paths );

		if( mainpaths < 1 ) {
			return;
		}
		for( var i = 0; i < mainpaths.length; i++ ) {
			storeData.push( {
				'mainpath': mainpaths[i],
				'mainpathdisplay': this.getPathSectionMessage( mainpaths[i] )
			});
		}
		//binding a new store just simply fails some times in extjs - this then
		//will result in store: null which causes some other random errors. So
		//we just hope that the first call of the main store already included
		//all the selectable paths for the path selection combobox
		//this.cbMainPath.bindStore( null );
		this.storeMainPath.loadData( storeData, false );
		//this.cbMainPath.bindStore( this.storeMainPath );
		if( this.mainPathSelection ) {
			this.cbMainPath.select( this.mainPathSelection );
		}
	},

	makeTbar: function() {
		return new Ext.Toolbar({
			style: {
				backgroundColor: '#FFFFFF',
				backgroundImage: 'none'
			},
			items: this.makeTbarItems()
		});
	},

	makeTbarItems: function() {
		var arrItems = [];
		arrItems.push( this.cbMainPath );
		arrItems.push( '|' );
		arrItems.push( this.tfSearch );
		arrItems.push( '|' );
		arrItems.push( this.btnReset );
		arrItems.push( this.btnOK );
		return arrItems;
	},

	getPathSectionMessage: function( path ) {
		var pathMsgs = mw.config.get( 'ConfigManagerPathMessages', {} );
		return mw.message( pathMsgs[path] || '' ).exists()
			? mw.message( pathMsgs[path] ).plain()
			: path;
	},

	onSearchFieldChanged: function( field, newValue, oldValue, eOpts ) {
		var me = this;
		var updater = function() {
			me.pnlConfig.setLoading( true );
			me.store.proxy.extraParams.query = newValue;
			me.store.reload();
		};
		if( me.pnlConfig.isDirty() === true ) {
			me.confirmDiscardChanges( updater );
			return false;
		}
		if ( this.updateSearchField ) {
			clearTimeout( this.updateSearchField );
		}
		this.updateSearchField = setTimeout( updater, 500 );
		return true;
	},

	onBtnOKClick: function( oButton, oEvent ) {
		var data = this.getData();
		var taskData = {};
		for( var i in data ) {
			if (data[i].name.endsWith("[]")) {
				short=data[i].name.slice(0, -2);
				(taskData[short] || (taskData[short] = [])).push(data[i].value);
			} else {
				taskData[data[i].name] = data[i].value;
			}
		}
		this.save( taskData );
	},

	onBtnResetClick: function( oButton, oEvent ) {
		this.pnlConfig.setLoading( true );
		this.store.reload();
	},

	getData: function() {
		return this.pnlConfig.getData();
	},

	save: function( taskData ) {
		var me = this;
		this.pnlConfig.setLoading( true );
		var $dfd = $.Deferred();

		bs.api.tasks.execSilent( 'configmanager', 'save', taskData )
		.done( function( response ) {
			me.pnlConfig.setLoading( false );
			if( response.message && response.message !== '' ) {
				bs.util.alert(
					'configmanager-save-fail',
					{
						titleMsg: 'bs-extjs-title-warning',
						text: response.message
					},
					{
						ok: function() {
							$dfd.reject( response );
						}
					}
				);
			}
			me.store.reload();
			return $dfd.resolve( me );
		});
	},

	onConfigDirty: function( cfgPnl, dirty ) {
		if( dirty === true ) {
			this.btnOK.enable();
			this.btnReset.enable();
		} else {
			this.btnOK.disable();
			this.btnReset.disable();
		}
	},

	confirmDiscardChanges: function( updater ) {
		var me = this;
		bs.util.confirm(
			'configmanager-discardchanges',
			{
				titleMsg: 'bs-extjs-confirm',
				text: mw.message( 'bs-configmanager-discardchanges' ).plain()
			},
			{
				ok: function() {
					me.pnlConfig.setDirty( false );
					updater();
				}
			}
		);
	}
});
