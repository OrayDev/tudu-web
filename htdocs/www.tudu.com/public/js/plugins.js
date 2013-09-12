var ScreenCapture = function(configs) {
	this.configs = $.extend(ScreenCapture.defaults, configs || {});
};

ScreenCapture.activex = {
	'screencapture': {'name': 'OrayFileControl.ScreenCapture.1', 'version' : '2.2.9.18706', 'classid': '05B7C812-0B49-4626-AF71-8082013509A7', 'codebase': '#version=2,2,9,18706'},
	'uploadmanager': {'name': 'OrayFileControl.UploaderManager.1', 'version': '2.2.9.18706', 'classid': '86EDE142-7BC9-48F2-91E4-E3FE9F598484', 'codebase': '#version=2,2,9,18706'},
	'npffapdapter': {'classid': '0CFECBBF-CC45-4EF4-97D9-984963438F01', 'codebase': '#version=2,1,8,9218'}
};

ScreenCapture.defaults = {
	onStart: function() {},
	onCaptured: function() {},
	onUploaded: function(id, success){},
	host: 'upload.tudu.com',
	path: '',
	sid: ''
};

ScreenCapture.prototype = {

	configs: null,
	
	nffadapter: null,
	
	screencapture: null,
	
	uploadmanager: null,
	
	uploader: {},
	
	init: function() {
		var me = this;
		
		try {
			//if (TOP.Browser.isFF) {
				//this.nffadapter = this.createActiveX('npffapdapter');
			//}

			this.screencapture = this.createActiveX('screencapture');
			
			if (!this.screencapture.getVersion()) {
				return false;
			}

			this.uploadmanager = this.createActiveX('uploadmanager');
			
			if (this.uploadmanager.addEventListener && !TOP.Browser.isIE) {
				this.uploadmanager.addEventListener('OnComplete', function(id){me.configs.onUploaded(me.uploader[id]);}, false);
			} else {
				this.uploadmanager.attachEvent('OnComplete', function(id){me.configs.onUploaded(me.uploader[id]);});
			}
			
			if (this.screencapture.addEventListener && !TOP.Browser.isIE) {
				this.screencapture.addEventListener('CaptureEnd', function(path, res){me.captured(path, res);}, false);
			} else {
				this.screencapture.attachEvent('CaptureEnd', function(path, res){me.captured(path, res);});
			}
			
		} catch (e) {
			return false;
		}
		return true;
	},
	
	destroy: function() {
		/*var doc = $(document.body);
		
		doc.remove(this.screencapture);
		doc.remove(this.uploadmanager);
		
		if (TOP.Browser.isFF && this.nffadapter) {
			doc.remove(this.nffadapter);
		}*/
		
		this.screencapture = null;
		this.uploadmanager = null;
		this.nffadapter    = null;
		
		return null;
	},
	
	startCapture: function() {
		try {
			var ret = this.screencapture.WebScreenCapture("");
			
			this.configs.onStart();
		} catch (e) {}
	},
	
	captured: function(path, code) {
		if (code == 3) {
			var localFile = path;
			
			if (localFile) {
				this.configs.onCaptured();
				
				var uploader = this.uploadmanager.CreateUploader();
				
				var url = TOP._FILECGI.upload;
				
				this.uploader[uploader.ObjID] = uploader;

				uploader.Start(this.configs.uploadUrl, localFile);
			} else {
				alert(TOP.TEXT.CAPTURE_FAILURE);
			}
		}
	},
	
	createActiveX: function(key, install) {
		var protocol = location.protocol;
		
		if (TOP.Browser.isIE) {
			//if (key == 'uploadmanager') {
			var obj = $('<object classid="CLSID:'+ScreenCapture.activex[key].classid+'" codebase="'+ScreenCapture.activex[key].codebase+'" width="1" height="1"></object>');
			
			obj.appendTo(document.body);
			
			return obj[0];
			//} else {
				//var ret = new ActiveXObject(ScreenCapture.activex[key].name);
				//return ret;
			//}
		} else if (TOP.Browser.isFF) {
			var obj = document.createElement('embed');
			
			var obj = $('<embed type="application/x-oray-npffadapter" codebase="'+ScreenCapture.activex[key].codebase+'" classid="CLSID:'+ScreenCapture.activex[key].classid+'" pluginspage="'+protocol+'//www.tudu.com/download/npffadapter.xpi" width="1" height="1"></embed>');
			
			obj.appendTo(document.body);
			
			return obj[0];
		}
	}
};