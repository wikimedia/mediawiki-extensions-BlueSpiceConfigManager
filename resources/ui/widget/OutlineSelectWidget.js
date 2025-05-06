bs.util.registerNamespace( 'bs.configmanager.ui.widget' );

bs.configmanager.ui.widget.OutlineSelectWidget = function ( cfg ) {
	cfg = cfg || {};
	bs.configmanager.ui.widget.OutlineSelectWidget.super.call( this, cfg );
	this.manager = cfg.manager;
};

OO.inheritClass( bs.configmanager.ui.widget.OutlineSelectWidget, OO.ui.OutlineSelectWidget );

bs.configmanager.ui.widget.OutlineSelectWidget.prototype.selectItem = function ( item ) {
	if ( item.isSelected() ) {
		return this;
	}
	if ( this.manager.hasOpenChange() ) {
		this.emit( 'keep', item );
		return this;
	}
	bs.configmanager.ui.widget.OutlineSelectWidget.super.prototype.selectItem.call( this, item );
};
