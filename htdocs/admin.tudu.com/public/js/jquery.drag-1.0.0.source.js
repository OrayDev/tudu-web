
(function($){ // secure $ jQuery alias

// jquery method
var drag = $.fn.drag = function(opts){

	if (opts === false)
		return this.unbind('mousedown', handler);

	opts = $.extend({ 
		not: ':input',	// don't begin to drag on event.targets that match this selector
		handle: '.handle',
		distance: 0,			// distance dragged before dragstart
		which: 1,				// mouse button pressed to start drag sequence
		constrain: true,		// constrain the window to the viewport, false to allow it to fall outside
		drag: function(event) {
			$( this ).css({
				top: event.offsetY,
				left: event.offsetX
			});
		}
	}, opts || {});
	
	opts.distance = squared( opts.distance ); //  x?+ y?= distance?
	
	this.bind('mousedown', {options: opts}, handler);

	// handle drag-releatd DOM events
	function handler ( event ) {
		var elem = this, returned, data = event.data || {}, opts = data.options || {};

		// mousemove or mouseup
		if ( data.elem ){ 
			// update event properties...
			elem = data.elem; // drag source element
			event.cursorOffsetX = data.pageX - data.left; // mousedown offset
			event.cursorOffsetY = data.pageY - data.top; // mousedown offset
			event.offsetX = event.pageX - event.cursorOffsetX; // element offset
			event.offsetY = event.pageY - event.cursorOffsetY; // element offset
		}
		// mousedown, check some initial props to avoid the switch statement
		else if ( drag.dragging || ( opts.which > 0 && event.which != opts.which ) || 
			$( event.target ).is( opts.not ) ) return;
	
		// handle various events
		switch ( event.type ){
			// mousedown, left click, event.target is not restricted, init dragging
			case 'mousedown':
				//console.log($( elem ).find( opts.handle ).size());
				if ( opts.handle && $( elem ).find( opts.handle ).size() && !($( event.target ).is( opts.handle ) || $( event.target ).parents( opts.handle ).size())) return;
				
				$.extend( data, $( elem ).offset(), { 
					elem: elem,
					target: event.target,
					pageX: event.pageX,
					pageY: event.pageY
				}); // store some initial attributes
				
				$.event.add( document, "mousemove mouseup", handler, data );
				
				selectable( elem, false ); // disable text selection
				return false; // prevents text selection in safari 
			
			// mousemove, check distance, start dragging
			case !drag.dragging && 'mousemove':
				if ( squared( event.pageX-data.pageX ) 
					+ squared( event.pageY-data.pageY ) //  x?+ y?= distance?
					< opts.distance ) break; // distance tolerance not reached
				
				event.target = data.target; // force target from "mousedown" event (fix distance issue)
				
				if (opts.dragstart && opts.dragstart.apply(elem, [event]) === false) {
					break;
				}
				drag.dragging = true;

			// mousemove, dragging
			case drag.dragging && 'mousemove':
				if (opts.dragging && opts.dragging.apply(elem, [event]) === false) {
					break;
				}
				if (opts.constrain) {
					if (event.offsetX < 0) event.offsetX = 0;
					if (event.offsetY < 0) event.offsetY = 0;
					var w = (document.body.scrollWidth - elem.offsetWidth);
					var h = (document.body.offsetHeight - elem.offsetHeight);
					if (event.offsetX > w) event.offsetX = w;
					if (document.body.offsetHeight > elem.offsetHeight) {
						if (event.offsetY > h) event.offsetY = h;
					} else {
						event.offsetY = 0;
					}
				}
				if (opts.drag.apply(elem, [event]) !== false) {
					break;
				}
				event.type = "mouseup"; // helps "drop" handler behave

			// mouseup, stop dragging
			case 'mouseup': 
				$.event.remove( document, "mousemove mouseup", handler ); // remove page events

				if ( drag.dragging && opts.dragend){
					opts.dragend.apply(elem, [event]);
				}
				selectable( elem, true ); // enable text selection
				drag.dragging = data.elem = null; // deactivate element
				break;
		} 
	};
	
	// return the value squared	
	function squared ( value ){
		return Math.pow( value, 2 );
	};
	
	// toggles text selection attributes	
	function selectable ( elem, bool ){ 
		if ( !elem ) return; // maybe element was removed ? 
		elem.unselectable = bool ? "off" : "on"; // IE
		elem.onselectstart = function(){ return bool; }; // IE
		if ( document.selection && document.selection.empty ) document.selection.empty(); // IE
		if ( elem.style ) elem.style.MozUserSelect = bool ? "" : "none"; // FF
	};
};


})( jQuery ); // confine scope