jQuery.fn.extend({
	serializeFileArray: function() {
		return this.map(function(){
			return jQuery.nodeName(this, "form") ?
				jQuery.makeArray(this.elements) : this;
		})
		.filter(function(){
			return this.name && !this.disabled && (this.type == "file");
		})
		.map(function(i, elem){
			var val = jQuery(this).val();
			return val == null ? null :
				val.constructor == Array ?
					jQuery.map( val, function(val, i){
						return {name: elem.name, value: val};
					}) :
					{name: elem.name, value: val};
		}).get();
	}
});


jQuery.extend({
	createUploadIframe: function(id, uri) {
		//create frame
		//var frameId = 'jUploadFrame' + id;
		var frameId = id;
		if(window.ActiveXObject) {
			var io;
			if (jQuery.browser.version >= '9.0') {
				io = document.createElement('iframe');
				io.id = frameId;
				io.name = frameId;
			} else {
				io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
			}
				if(typeof uri== 'boolean'){
					io.src = 'javascript:false';
				}
				else if(typeof uri== 'string'){
					io.src = uri;
				}
		} else {
			var io = document.createElement('iframe');
			io.id = frameId;
			io.name = frameId;
		}
		io.style.position = 'absolute';
		io.style.top = '-1000px';
		io.style.left = '-1000px';
		document.body.appendChild(io);
		//alert(document.body.innerHTML.substr(10000));
		return io;
	},
	createUploadForm: function(id, file, data) {
		//create form
		//var formId = 'jUploadForm' + id;
		var fileId = 'jUploadFile' + id;
		var formId = id;
		var form = jQuery('<form action="" method="POST" name="' + formId + '" id="' + formId + '" enctype="multipart/form-data"></form>');	

		//file
		jQuery(file).each(function(){
			//jQuery('<input type="file" name="' + this.name + '" filename="' + this.value + '" />').appendTo(form);
			var newElement = jQuery(this).clone();
			jQuery(this).before(newElement);
			jQuery(this).appendTo(form);
		});

		//parameter
		jQuery(data).each(function(){
			jQuery('<input type="hidden" name="' + this.name + '" value="' + jQuery.HTMLEncodeLite(this.value) + '" />').appendTo(form);
		});

		//var oldElement = jQuery('#' + fileElementId);
		//var newElement = jQuery(oldElement).clone();
		//jQuery(oldElement).attr('id', fileId);
		//jQuery(oldElement).before(newElement);
		//jQuery(oldElement).appendTo(form);
		
		//set attributes
		jQuery(form).css('position', 'absolute');
		jQuery(form).css('top', '-1200px');
		jQuery(form).css('left', '-1200px');
		jQuery(form).appendTo('body');
		//alert(jQuery(form).html());
		return form;
	},

	ajaxUpload: function(s) {
		// TODO introduce global settings, allowing the client to modify them for all requests, not only timeout		
		s = jQuery.extend({}, jQuery.ajaxSettings, s);
		var id = new Date().getTime();
		var frameId = 'jUploadFrame' + id;
		var formId = 'jUploadForm' + id;
		var form = jQuery.createUploadForm(formId, s.file, s.data);
		var io = jQuery.createUploadIframe(frameId, s.secureuri);

		// Watch for a new set of requests
		if ( s.global && ! jQuery.active++ ) {
			jQuery.event.trigger( "ajaxStart" );
		}			
		var requestDone = false;
		
		// Create the request object
		var xml = {};
		if ( s.global )
			jQuery.event.trigger("ajaxSend", [xml, s]);
		
		// Wait for a response to come back
		var uploadCallback = function(isTimeout) {			
			var io = document.getElementById(frameId);
			try {
				if(io.contentWindow) {
					xml.responseText = io.contentWindow.document.body?io.contentWindow.document.body.innerHTML:null;
					xml.responseXML = io.contentWindow.document.XMLDocument?io.contentWindow.document.XMLDocument:io.contentWindow.document;
				} else if(io.contentDocument) {
					xml.responseText = io.contentDocument.document.body?io.contentDocument.document.body.innerHTML:null;
					xml.responseXML = io.contentDocument.document.XMLDocument?io.contentDocument.document.XMLDocument:io.contentDocument.document;
				}
			} catch(e) {
				jQuery.handleError(s, xml, null, e);
			}
			
			if ( xml || isTimeout == "timeout") {				
				requestDone = true;
				var status;
				try {
					status = isTimeout != "timeout" ? "success" : "error";
					// Make sure that the request was successful or notmodified
					if ( status != "error" ) {
						// process the data (runs the xml through httpData regardless of callback)
						var data = jQuery.uploadHttpData( xml, s.dataType );	
						// If a local callback was specified, fire it and pass it the data
						if ( s.success )
							s.success( data, status );
	
						// Fire the global callback
						if( s.global )
							jQuery.event.trigger( "ajaxSuccess", [xml, s] );
					} else
						jQuery.handleError(s, xml, status);
				} catch(e) {
					status = "error";
					jQuery.handleError(s, xml, status, e);
				}

				// The request was completed
				if( s.global )
					jQuery.event.trigger( "ajaxComplete", [xml, s] );

				// Handle the global AJAX counter
				if ( s.global && ! --jQuery.active )
					jQuery.event.trigger( "ajaxStop" );

				// Process result
				if ( s.complete )
					s.complete(xml, status);

				jQuery(io).unbind();

				setTimeout(function(){
					try{
						jQuery(io).remove();
						jQuery(form).remove();	
					} catch(e) {
						jQuery.handleError(s, xml, null, e);
					}
				}, 100);
				xml = null;
			}
		};
		// Timeout checker
		if ( s.timeout > 0 ) {
			setTimeout(function(){
				// Check to see if the request is still happening
				if( !requestDone ) uploadCallback( "timeout" );
			}, s.timeout);
		}
		try {
		   // var io = jQuery('#' + frameId);
			var form = jQuery('#' + formId);
			jQuery(form).attr('action', s.url);
			jQuery(form).attr('method', 'POST');
			jQuery(form).attr('target', frameId);
			if(form.encoding) {
				form.encoding = 'multipart/form-data';				
			} else {				
				form.enctype = 'multipart/form-data';
			}
			jQuery(form).submit();

		} catch(e) {			
			jQuery.handleError(s, xml, null, e);
		}
		
		if( window.addEventListener ){
			document.getElementById(frameId).addEventListener('load', uploadCallback, false);
		} else {
			document.getElementById(frameId).attachEvent('onload', uploadCallback);
		}
		
		//if( window.attachEvent ){
		//	document.getElementById(frameId).attachEvent('onload', uploadCallback);
		//} else {
		//	document.getElementById(frameId).addEventListener('load', uploadCallback, false);
		//} 		
		return {abort: function () {}};
	},

	uploadHttpData: function( r, type ) {
		var data = !type;
		data = type == "xml" || data ? r.responseXML : r.responseText;
		// If the type is "script", eval it in global context
		if ( type == "script" )
			jQuery.globalEval( data );
		// Get the JavaScript object, if JSON is used.
		if ( type == "json" )
			eval( "data = " + data );
		// evaluate scripts within html
		if ( type == "html" )
			jQuery("<div>").html(data).evalScripts();
			//alert(jQuery('param', data).each(function(){alert(jQuery(this).attr('value'));}));
		return data;
	},
	
	// HTMLEncode For Textarea
	HTMLEncodeLite: function( str ){
		if( str==undefined ){ return "" };
		str = str.replace(/\&/g, "&amp;");
		str = str.replace(/\>/g, "&gt;");
		str = str.replace(/\</g, "&lt;");
		str = str.replace(/\"/g, "&quot;");
		str = str.replace(/\'/g, "&#39;");
		return str;
	}
})