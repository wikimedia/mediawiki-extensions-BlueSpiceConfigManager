Ext.define( 'BS.ConfigManager.grid.Paths', {
	extend: 'Ext.grid.Panel',
	viewConfig: {
		stripeRows: false
	},
	autoScroll : true,
	hideHeaders: true,
	selectedPaths: null,

	initComponent: function() {
		var me = this;

		this.columns = [{
			dataIndex: 'path',
			renderer: this.renderColumn,
			flex: 1
		}],

		this.selModel = new Ext.selection.CheckboxModel( {
			selectMode: 'MULTIPLE'
		});
		this.selModel.on( 'beforeselect', function( model, record, index ) {
			if( me.manager.pnlConfig.isDirty() ) {
				var updater = function() {
					model.select( record );
				};
				me.manager.confirmDiscardChanges( updater );
				return false;
			}
			return true;
		});
		this.selModel.on( 'beforedeselect', function( model, record, index ) {
			if( me.manager.pnlConfig.isDirty() ) {
				var updater = function() {
					model.deselect( record );
				};
				me.manager.confirmDiscardChanges( updater );
				return false;
			}
			return true;
		});
		this.selModel.on( 'selectionchange', this.selectionChanged, this );

		this.store = new Ext.data.ArrayStore( {
			fields: [
				'path'
			]
		});
		this.store.on( 'datachanged', this.updateStoreSelection, this );

		this.manager.on( 'pathsresolved', this.updateStore, this );
		this.manager.cbMainPath.on( 'change', this.updateStore, this );

		this.callParent( arguments );
	},

	renderColumn: function( val, meta, record ) {
		return this.manager.getPathSectionMessage( val );
	},

	updateStore: function() {
		var me = this;
		var updater = function() {
			var pathData = me.manager.paths[me.manager.mainPathSelection];
			if( pathData ) {
				pathData = pathData.filter( function( value, index, self ) {
					return self.indexOf(value) === index;
				});
				var storeData = [];
				for( var i in pathData ){
					storeData.push( { 'path': pathData[i] } );
				}

				me.store.loadData( storeData );
			} else {
				me.store.loadData( [] );
			}
		};
		if( me.manager.pnlConfig.isDirty() === true ) {
			me.manager.confirmDiscardChanges( updater );
			return;
		}
		updater();
	},

	updateStoreSelection: function( store, eOpts ) {
		var me = this;
		if( store.getCount() < 1 ) {
			//me.selModel.select();
			me.selModel.deselectAll();
			//me.selectionChanged( me.selModel, [] );
			return;
		}
		if( !me.selectedPaths ) {
			me.selectedPaths = [];
			me.selectedPaths.push( store.getAt( 0 ).get( 'path' ) );
		}
		var selection = [];
		var i = 0;
		store.each( function( rec ) {
			if( me.selectedPaths.indexOf( rec.get( 'path' ) ) === -1 ) {
				return;
			};
			selection.push( rec );
			i++;
		});
		if( selection.length < 1 ) {
			me.selectedPaths = [];
			me.selectedPaths.push( store.getAt( 0 ).get( 'path' ) );
			selection.push( store.getAt( 0 ) );
		}
		me.selModel.select( selection, true );
	},

	selectionChanged: function( selModel, selected, eOpts ) {
		var me = this;
		me.selectedPaths = [];
		for( var i = 0; i < selected.length; i++ ) {
			me.selectedPaths.push( selected[i].get( 'path' ) );
		};
		this.fireEvent( 'pathselection', me, selModel, selected );
	}

});
