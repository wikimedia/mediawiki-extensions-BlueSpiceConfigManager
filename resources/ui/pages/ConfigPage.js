bs.util.registerNamespace( 'bs.configmanager.ui' );
bs.util.registerNamespace( 'bs.configmanager.ui.pages' );

bs.configmanager.ui.pages.ConfigPage = function ( name, label, pathnames, configs ) {
	bs.configmanager.ui.pages.ConfigPage.parent.call( this, name, {} );
	this.label = label;
	this.key = name;
	this.configs = configs;
	this.pathNames = pathnames;
	this.htmlClassPrefix = 'bs-configmanager-configpanel-';
	this.$element = $( '<div>' );
	this.setupWidget();
};

OO.inheritClass( bs.configmanager.ui.pages.ConfigPage, OO.ui.PageLayout );

bs.configmanager.ui.pages.ConfigPage.prototype.setupOutlineItem = function () {
	this.outlineItem.setLabel( this.label );
};

bs.configmanager.ui.pages.ConfigPage.prototype.setupWidget = function () {
	this.oouiWidgets = {};

	var contentClass = this.htmlClassPrefix + "content";
	if( this.configs.length < 1 ) {
		contentClass += ' ' + this.htmlClassPrefix + 'noentry';
		this.$element.append(
			'<div class="' + contentClass + '">'
			+ '<h4>'
			+ mw.message( 'bs-configmanager-noentry' ).plain()
			+ '</h4>'
			+ '</div>'
		);
		return;
	}

	var content = '<form id="bs-configmanager-form" class="' + contentClass + '">';
	var currentPath = '', sectionClass = '';
	for( var i = 0; i < this.configs.length; i++ ) {
		this.configs[i].paths.forEach( function( path ) {
			var sections = path.split( '/' );
			if ( this.key !== sections[ 1 ] ) {
				return;
			}
			if( currentPath !== sections[2] ) {
				if( i > 0 ) {
					content += '</fieldset>';
				}
				sectionClass = this.htmlClassPrefix
					+ 'section '
					+ this.htmlClassPrefix
					+ 'section-'
					+ ( sections[2] || 'unknown' );
				content += '<fieldset class="' + sectionClass + '" >';
				if( sections[2] ) {
					currentPath = sections[2];
					if ( this.pathNames[ currentPath ] ) {
						var label = mw.message( this.pathNames[ currentPath ] ).text();
						content += '<legend>' + label + '</legend>';
					} else {
						content += '<legend>' + currentPath + '</legend>';
					}
				}
			}
		}.bind( this ) );
		content += '<div>' + this.configs[i].form + '</div>';

		// Check if html contain infusable OOUI element
		var widgetId = this.getOOUIWidgetElementId(
			this.configs[i].var_name,
			this.configs[i].form
		);
		if( widgetId ) {
			// At this point, we can only get widget ID, not the instance
			this.oouiWidgets[ this.configs[i].s_name ] = widgetId;
		}
	}
	content += '</fieldset>';
	content += '</form>';
	this.$element.append( content );
};

bs.configmanager.ui.pages.ConfigPage.prototype.infuseWidgets = function( content ) {
	for ( let field in this.oouiWidgets ) {
		let widgetId = 'unknown';
		try {
			widgetId = this.oouiWidgets[field];
			let widget = OO.ui.infuse( $( content ).find( '#' + widgetId ) );

			const parent = document.querySelectorAll(
				".bs-configmanager-configpanel-section"
			);

			// Now we are able to replace widget id with an instance
			this.oouiWidgets[ field ] = widget;
			this.oouiWidgets[ field ].on( 'change', function() {
				this.emit( 'change' );
			}.bind( this ) );
		 } catch( e ) {
			console.error( 'Widget with id "' + widgetId + '" could not be infused', e );
			continue;
		}
	}
};

bs.configmanager.ui.pages.ConfigPage.prototype.getOOUIWidgetElementId = function( configVar, html ) {
	var $widget = $( html ).find( '#' + configVar + '.oo-ui-widget' );
	if( $widget.length === 0 ) {
		return false;
	}

	if( ! $widget.data( 'ooui' ) ) {
		// No infusion data
		return false;
	}
	return $widget.attr( 'id' );
};

bs.configmanager.ui.pages.ConfigPage.prototype.getData = function() {
	var data = {};
	for ( let field in this.oouiWidgets ) {
		var value = this.oouiWidgets[ field ].getValue();
		if( this.oouiWidgets[ field ] instanceof OO.ui.CheckboxInputWidget ) {
			value = this.oouiWidgets[ field ].isSelected();
		}
		data[ field ] = value;
	}
	return data;
};