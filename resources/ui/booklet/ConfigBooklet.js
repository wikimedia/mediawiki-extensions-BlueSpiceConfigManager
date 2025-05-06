bs.util.registerNamespace( 'bs.configmanager.ui.booklet' );

require( '../widget/OutlineSelectWidget.js' );

bs.configmanager.ui.booklet.ConfigBooklet = function ( cfg ) {
	cfg = cfg || {};
	bs.configmanager.ui.booklet.ConfigBooklet.super.call( this, cfg );
	this.manager = cfg.manager;
	this.outlineSelectWidget = new bs.configmanager.ui.widget.OutlineSelectWidget( {
		manager: this.manager
	} );
	this.outlineSelectWidget.connect( this, {
		select: 'onOutlineSelectWidgetSelect',
		keep: function ( item ) {
			this.emit( 'keep', item );
		}
	} );
	this.outlinePanel.$element.append( this.outlineSelectWidget.$element );
};

OO.inheritClass( bs.configmanager.ui.booklet.ConfigBooklet, OO.ui.BookletLayout );
