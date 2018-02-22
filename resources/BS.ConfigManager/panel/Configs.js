Ext.define( 'BS.ConfigManager.panel.Configs', {
	extend: 'Ext.Panel',
	html: '',
	header: false,
	border: false,
	initialData: {},
	autoHeight: true,
	$form: null,
	formId: 'bs-configmanager-form',
	htmlClassPrefix: "bs-configmanager-configpanel-",
	dirty: false,
	autoScroll: true,

	initComponent: function() {
		this.addEvents( 'dirty' );
		this.manager.pnlPath.on( 'pathselection', this.pathSelectionChanged, this );
		this.callParent( arguments );
	},

	pathSelectionChanged: function( selectionPanel, selModel, selected ) {
		var activeRecords = [];
		this.store.each( function( rec ) {
			rec.get( 'paths' ).forEach( function( path ) {
				var sections = path.split( '/' );
				for( var i = 0; i < selected.length; i++ ) {
					if( selected[i].get( 'path' ) !== sections[1] ) {
						continue;
					}
					activeRecords.push( rec );
				}
			});
		});
		this.displayConfigs( activeRecords );
	},

	displayConfigs: function( records ) {
		var me = this;
		var contentClass = me.htmlClassPrefix + "content";
		if( records.length < 1 ) {
			contentClass += ' ' + me.htmlClassPrefix + 'noentry';
			me.body.update(
				'<div class="' + contentClass + '">'
				+ '<h4>'
				+ mw.message( 'bs-configmanager-noentry' ).plain()
				+ '</h4>'
				+ '</div>'
			);
			me.resize();
			return;
		}
		if( records.length > 1 ) {
			records.sort( me.sortRecords( me ) );
		}
		var content = '<form id="' + me.formId + '" class="' + contentClass + '">';
		var currentPath = '', sectionClass = '';
		for( var i = 0; i < records.length; i++ ) {
			records[i].get( 'paths' ).forEach( function( path ) {
				var sections = path.split( '/' );
				if( sections[0] !== me.manager.mainPathSelection ) {
					return;
				}
				sections[2] = me.manager.getPathSectionMessage(
					sections[2]
				);
				if( currentPath !== sections[2] ) {
					if( i > 0 ) {
						content += '</fieldset>';
					}
					sectionClass = me.htmlClassPrefix
						+ 'section '
						+ me.htmlClassPrefix
						+ 'section-'
						+ ( sections[2] || 'unknown' );
					content += '<fieldset class="' + sectionClass + '" >';
					if( sections[2] ) {
						currentPath = sections[2];
						content += '<legend>' + currentPath + '</legend>';
					}
				}
			});
			content += '<div>' + records[i].get( 'form' ) + '</div>';
		}
		content += '</fieldset>';
		content += '</form>';
		me.body.update( content );
		$( "#" + me.formId + " :input" ).on( 'input change', function() {
			me.setDirty( true );
		});
		me.resize();
	},

	resize: function() {
		//own resize method, as extjs cant handle its autoHeight
		//stop weired jquery never changing height behavings by re-wrapping
		var height = $( $( '#bs-configmanager-form' )[0] ).height();
		this.setHeight( height );
		this.manager.setHeight( height+100 );
		this.doLayout();
		this.manager.doLayout();
	},

	getData: function() {
		var data = {};
		$( "#" + this.formId )
		.on( 'submit', function( e ) {
			data = $(this).serializeArray();
			e.preventDefault();
			return false;
		}).submit();
		//checkboxes that are not selected will be ignored by html standards
		$('#' + this.formId + ' input[type=checkbox]:not(:checked)' ).each(
			function() {
				data.push( { "name": this.name, "value": false } );
			}
		);
		return data;
	},

	setDirty: function( dirty ) {
		var me = this;
		if( dirty === true ) {
			me.dirty = true;
		} else {
			me.dirty = false;
		}
		me.fireEvent( 'dirty', me, me.dirty );
	},

	isDirty: function() {
		return this.dirty;
	},

	sortRecords: function( cfgPnl ) {
		return function( a, b ) {
			var pathA = '', pathB = '';
			a.get( 'paths' ).forEach( function( path ) {
				var sections = path.split( '/' );
				if( sections[0] !== cfgPnl.manager.mainPathSelection ) {
					return;
				}
				if( !sections[2] ) {
					return;
				}
				pathA = cfgPnl.manager.getPathSectionMessage(
					sections[2]
				);
			});
			b.get( 'paths' ).forEach( function( path ) {
				var sections = path.split( '/' );
				if( sections[0] !== cfgPnl.manager.mainPathSelection ) {
					return;
				}
				if( !sections[2] ) {
					return;
				}
				pathB = cfgPnl.manager.getPathSectionMessage(
					sections[2]
				);
			});
			if( pathA < pathB ) {
				return -1;
			}
			if( pathA > pathB ) {
				return 1;
			}
			return 0;
		}
	}
});
