var SWFUpload;if(SWFUpload==undefined){SWFUpload=function(A){this.initSWFUpload(A)}}SWFUpload.prototype.initSWFUpload=function(B){try{this.customSettings={};this.settings=B;this.eventQueue=[];this.movieName="SWFUpload_"+SWFUpload.movieCount++;this.movieElement=null;SWFUpload.instances[this.movieName]=this;this.initSettings();this.loadFlash();this.displayDebugInfo()}catch(A){delete SWFUpload.instances[this.movieName];throw A}};SWFUpload.instances={};SWFUpload.movieCount=0;SWFUpload.version="2.2.0 2009-03-25";SWFUpload.QUEUE_ERROR={QUEUE_LIMIT_EXCEEDED:-100,FILE_EXCEEDS_SIZE_LIMIT:-110,ZERO_BYTE_FILE:-120,INVALID_FILETYPE:-130};SWFUpload.UPLOAD_ERROR={HTTP_ERROR:-200,MISSING_UPLOAD_URL:-210,IO_ERROR:-220,SECURITY_ERROR:-230,UPLOAD_LIMIT_EXCEEDED:-240,UPLOAD_FAILED:-250,SPECIFIED_FILE_ID_NOT_FOUND:-260,FILE_VALIDATION_FAILED:-270,FILE_CANCELLED:-280,UPLOAD_STOPPED:-290};SWFUpload.FILE_STATUS={QUEUED:-1,IN_PROGRESS:-2,ERROR:-3,COMPLETE:-4,CANCELLED:-5};SWFUpload.BUTTON_ACTION={SELECT_FILE:-100,SELECT_FILES:-110,START_UPLOAD:-120};SWFUpload.CURSOR={ARROW:-1,HAND:-2};SWFUpload.WINDOW_MODE={WINDOW:"window",TRANSPARENT:"transparent",OPAQUE:"opaque"};SWFUpload.completeURL=function(A){if(typeof(A)!=="string"||A.match(/^https?:\/\//i)||A.match(/^\//)){return A}var C=window.location.protocol+"//"+window.location.hostname+(window.location.port?":"+window.location.port:"");var B=window.location.pathname.lastIndexOf("/");if(B<=0){path="/"}else{path=window.location.pathname.substr(0,B)+"/"}return path+A};SWFUpload.prototype.initSettings=function(){this.ensureDefault=function(B,A){this.settings[B]=(this.settings[B]==undefined)?A:this.settings[B]};this.ensureDefault("upload_url","");this.ensureDefault("preserve_relative_urls",false);this.ensureDefault("file_post_name","Filedata");this.ensureDefault("post_params",{});this.ensureDefault("use_query_string",false);this.ensureDefault("requeue_on_error",false);this.ensureDefault("http_success",[]);this.ensureDefault("assume_success_timeout",0);this.ensureDefault("file_types","*.*");this.ensureDefault("file_types_description","All Files");this.ensureDefault("file_size_limit",0);this.ensureDefault("file_upload_limit",0);this.ensureDefault("file_queue_limit",0);this.ensureDefault("flash_url","swfupload.swf");this.ensureDefault("prevent_swf_caching",true);this.ensureDefault("button_image_url","");this.ensureDefault("button_width",1);this.ensureDefault("button_height",1);this.ensureDefault("button_text","");this.ensureDefault("button_text_style","color: #000000; font-size: 16pt;");this.ensureDefault("button_text_top_padding",0);this.ensureDefault("button_text_left_padding",0);this.ensureDefault("button_action",SWFUpload.BUTTON_ACTION.SELECT_FILES);this.ensureDefault("button_disabled",false);this.ensureDefault("button_placeholder_id","");this.ensureDefault("button_placeholder",null);this.ensureDefault("button_cursor",SWFUpload.CURSOR.ARROW);this.ensureDefault("button_window_mode",SWFUpload.WINDOW_MODE.WINDOW);this.ensureDefault("debug",false);this.settings.debug_enabled=this.settings.debug;this.settings.return_upload_start_handler=this.returnUploadStart;this.ensureDefault("swfupload_loaded_handler",null);this.ensureDefault("file_dialog_start_handler",null);this.ensureDefault("file_queued_handler",null);this.ensureDefault("file_queue_error_handler",null);this.ensureDefault("file_dialog_complete_handler",null);this.ensureDefault("upload_start_handler",null);this.ensureDefault("upload_progress_handler",null);this.ensureDefault("upload_error_handler",null);this.ensureDefault("upload_success_handler",null);this.ensureDefault("upload_complete_handler",null);this.ensureDefault("debug_handler",this.debugMessage);this.ensureDefault("custom_settings",{});this.customSettings=this.settings.custom_settings;if(!!this.settings.prevent_swf_caching){this.settings.flash_url=this.settings.flash_url+(this.settings.flash_url.indexOf("?")<0?"?":"&")+"preventswfcaching="+new Date().getTime()}if(!this.settings.preserve_relative_urls){this.settings.upload_url=SWFUpload.completeURL(this.settings.upload_url);this.settings.button_image_url=SWFUpload.completeURL(this.settings.button_image_url)}delete this.ensureDefault};SWFUpload.prototype.loadFlash=function(){var A,B;if(document.getElementById(this.movieName)!==null){throw"ID "+this.movieName+" is already in use. The Flash Object could not be added"}A=document.getElementById(this.settings.button_placeholder_id)||this.settings.button_placeholder;if(A==undefined){throw"Could not find the placeholder element: "+this.settings.button_placeholder_id}B=document.createElement("div");B.innerHTML=this.getFlashHTML();A.parentNode.replaceChild(B.firstChild,A);if(window[this.movieName]==undefined){window[this.movieName]=this.getMovieElement()}};SWFUpload.prototype.getFlashHTML=function(){return['<object id="',this.movieName,'" type="application/x-shockwave-flash" data="',this.settings.flash_url,'" width="',this.settings.button_width,'" height="',this.settings.button_height,'" class="swfupload">','<param name="wmode" value="',this.settings.button_window_mode,'" />','<param name="movie" value="',this.settings.flash_url,'" />','<param name="quality" value="high" />','<param name="menu" value="false" />','<param name="allowScriptAccess" value="always" />','<param name="flashvars" value="'+this.getFlashVars()+'" />',"</object>"].join("")};SWFUpload.prototype.getFlashVars=function(){var B=this.buildParamString();var A=this.settings.http_success.join(",");return["movieName=",encodeURIComponent(this.movieName),"&amp;uploadURL=",encodeURIComponent(this.settings.upload_url),"&amp;useQueryString=",encodeURIComponent(this.settings.use_query_string),"&amp;requeueOnError=",encodeURIComponent(this.settings.requeue_on_error),"&amp;httpSuccess=",encodeURIComponent(A),"&amp;assumeSuccessTimeout=",encodeURIComponent(this.settings.assume_success_timeout),"&amp;params=",encodeURIComponent(B),"&amp;filePostName=",encodeURIComponent(this.settings.file_post_name),"&amp;fileTypes=",encodeURIComponent(this.settings.file_types),"&amp;fileTypesDescription=",encodeURIComponent(this.settings.file_types_description),"&amp;fileSizeLimit=",encodeURIComponent(this.settings.file_size_limit),"&amp;fileUploadLimit=",encodeURIComponent(this.settings.file_upload_limit),"&amp;fileQueueLimit=",encodeURIComponent(this.settings.file_queue_limit),"&amp;debugEnabled=",encodeURIComponent(this.settings.debug_enabled),"&amp;buttonImageURL=",encodeURIComponent(this.settings.button_image_url),"&amp;buttonWidth=",encodeURIComponent(this.settings.button_width),"&amp;buttonHeight=",encodeURIComponent(this.settings.button_height),"&amp;buttonText=",encodeURIComponent(this.settings.button_text),"&amp;buttonTextTopPadding=",encodeURIComponent(this.settings.button_text_top_padding),"&amp;buttonTextLeftPadding=",encodeURIComponent(this.settings.button_text_left_padding),"&amp;buttonTextStyle=",encodeURIComponent(this.settings.button_text_style),"&amp;buttonAction=",encodeURIComponent(this.settings.button_action),"&amp;buttonDisabled=",encodeURIComponent(this.settings.button_disabled),"&amp;buttonCursor=",encodeURIComponent(this.settings.button_cursor)].join("")};SWFUpload.prototype.getMovieElement=function(){if(this.movieElement==undefined){this.movieElement=document.getElementById(this.movieName)}if(this.movieElement===null){throw"Could not find Flash element"}return this.movieElement};SWFUpload.prototype.buildParamString=function(){var C=this.settings.post_params;var B=[];if(typeof(C)==="object"){for(var A in C){if(C.hasOwnProperty(A)){B.push(encodeURIComponent(A.toString())+"="+encodeURIComponent(C[A].toString()))}}}return B.join("&amp;")};SWFUpload.prototype.destroy=function(){try{this.cancelUpload(null,false);var A=null;A=this.getMovieElement();if(A&&typeof(A.CallFunction)==="unknown"){for(var C in A){try{if(typeof(A[C])==="function"){A[C]=null}}catch(E){}}try{A.parentNode.removeChild(A)}catch(B){}}window[this.movieName]=null;SWFUpload.instances[this.movieName]=null;delete SWFUpload.instances[this.movieName];this.movieElement=null;this.settings=null;this.customSettings=null;this.eventQueue=null;this.movieName=null;return true}catch(D){return false}};SWFUpload.prototype.displayDebugInfo=function(){this.debug(["---SWFUpload Instance Info---\n","Version: ",SWFUpload.version,"\n","Movie Name: ",this.movieName,"\n","Settings:\n","\t","upload_url:               ",this.settings.upload_url,"\n","\t","flash_url:                ",this.settings.flash_url,"\n","\t","use_query_string:         ",this.settings.use_query_string.toString(),"\n","\t","requeue_on_error:         ",this.settings.requeue_on_error.toString(),"\n","\t","http_success:             ",this.settings.http_success.join(", "),"\n","\t","assume_success_timeout:   ",this.settings.assume_success_timeout,"\n","\t","file_post_name:           ",this.settings.file_post_name,"\n","\t","post_params:              ",this.settings.post_params.toString(),"\n","\t","file_types:               ",this.settings.file_types,"\n","\t","file_types_description:   ",this.settings.file_types_description,"\n","\t","file_size_limit:          ",this.settings.file_size_limit,"\n","\t","file_upload_limit:        ",this.settings.file_upload_limit,"\n","\t","file_queue_limit:         ",this.settings.file_queue_limit,"\n","\t","debug:                    ",this.settings.debug.toString(),"\n","\t","prevent_swf_caching:      ",this.settings.prevent_swf_caching.toString(),"\n","\t","button_placeholder_id:    ",this.settings.button_placeholder_id.toString(),"\n","\t","button_placeholder:       ",(this.settings.button_placeholder?"Set":"Not Set"),"\n","\t","button_image_url:         ",this.settings.button_image_url.toString(),"\n","\t","button_width:             ",this.settings.button_width.toString(),"\n","\t","button_height:            ",this.settings.button_height.toString(),"\n","\t","button_text:              ",this.settings.button_text.toString(),"\n","\t","button_text_style:        ",this.settings.button_text_style.toString(),"\n","\t","button_text_top_padding:  ",this.settings.button_text_top_padding.toString(),"\n","\t","button_text_left_padding: ",this.settings.button_text_left_padding.toString(),"\n","\t","button_action:            ",this.settings.button_action.toString(),"\n","\t","button_disabled:          ",this.settings.button_disabled.toString(),"\n","\t","custom_settings:          ",this.settings.custom_settings.toString(),"\n","Event Handlers:\n","\t","swfupload_loaded_handler assigned:  ",(typeof this.settings.swfupload_loaded_handler==="function").toString(),"\n","\t","file_dialog_start_handler assigned: ",(typeof this.settings.file_dialog_start_handler==="function").toString(),"\n","\t","file_queued_handler assigned:       ",(typeof this.settings.file_queued_handler==="function").toString(),"\n","\t","file_queue_error_handler assigned:  ",(typeof this.settings.file_queue_error_handler==="function").toString(),"\n","\t","upload_start_handler assigned:      ",(typeof this.settings.upload_start_handler==="function").toString(),"\n","\t","upload_progress_handler assigned:   ",(typeof this.settings.upload_progress_handler==="function").toString(),"\n","\t","upload_error_handler assigned:      ",(typeof this.settings.upload_error_handler==="function").toString(),"\n","\t","upload_success_handler assigned:    ",(typeof this.settings.upload_success_handler==="function").toString(),"\n","\t","upload_complete_handler assigned:   ",(typeof this.settings.upload_complete_handler==="function").toString(),"\n","\t","debug_handler assigned:             ",(typeof this.settings.debug_handler==="function").toString(),"\n"].join(""))};SWFUpload.prototype.addSetting=function(B,C,A){if(C==undefined){return(this.settings[B]=A)}else{return(this.settings[B]=C)}};SWFUpload.prototype.getSetting=function(A){if(this.settings[A]!=undefined){return this.settings[A]}return""};SWFUpload.prototype.callFlash=function(functionName,argumentArray){argumentArray=argumentArray||[];var movieElement=this.getMovieElement();var returnValue,returnString;try{returnString=movieElement.CallFunction('<invoke name="'+functionName+'" returntype="javascript">'+__flash__argumentsToXML(argumentArray,0)+"</invoke>");returnValue=eval(returnString)}catch(ex){throw"Call to "+functionName+" failed"}if(returnValue!=undefined&&typeof returnValue.post==="object"){returnValue=this.unescapeFilePostParams(returnValue)}return returnValue};SWFUpload.prototype.selectFile=function(){this.callFlash("SelectFile")};SWFUpload.prototype.selectFiles=function(){this.callFlash("SelectFiles")};SWFUpload.prototype.startUpload=function(A){this.callFlash("StartUpload",[A])};SWFUpload.prototype.cancelUpload=function(A,B){if(B!==false){B=true}this.callFlash("CancelUpload",[A,B])};SWFUpload.prototype.stopUpload=function(){this.callFlash("StopUpload")};SWFUpload.prototype.getStats=function(){return this.callFlash("GetStats")};SWFUpload.prototype.setStats=function(A){this.callFlash("SetStats",[A])};SWFUpload.prototype.getFile=function(A){if(typeof(A)==="number"){return this.callFlash("GetFileByIndex",[A])}else{return this.callFlash("GetFile",[A])}};SWFUpload.prototype.addFileParam=function(A,B,C){return this.callFlash("AddFileParam",[A,B,C])};SWFUpload.prototype.removeFileParam=function(A,B){this.callFlash("RemoveFileParam",[A,B])};SWFUpload.prototype.setUploadURL=function(A){this.settings.upload_url=A.toString();this.callFlash("SetUploadURL",[A])};SWFUpload.prototype.setPostParams=function(A){this.settings.post_params=A;this.callFlash("SetPostParams",[A])};SWFUpload.prototype.addPostParam=function(A,B){this.settings.post_params[A]=B;this.callFlash("SetPostParams",[this.settings.post_params])};SWFUpload.prototype.removePostParam=function(A){delete this.settings.post_params[A];this.callFlash("SetPostParams",[this.settings.post_params])};SWFUpload.prototype.setFileTypes=function(A,B){this.settings.file_types=A;this.settings.file_types_description=B;this.callFlash("SetFileTypes",[A,B])};SWFUpload.prototype.setFileSizeLimit=function(A){this.settings.file_size_limit=A;this.callFlash("SetFileSizeLimit",[A])};SWFUpload.prototype.setFileUploadLimit=function(A){this.settings.file_upload_limit=A;this.callFlash("SetFileUploadLimit",[A])};SWFUpload.prototype.setFileQueueLimit=function(A){this.settings.file_queue_limit=A;this.callFlash("SetFileQueueLimit",[A])};SWFUpload.prototype.setFilePostName=function(A){this.settings.file_post_name=A;this.callFlash("SetFilePostName",[A])};SWFUpload.prototype.setUseQueryString=function(A){this.settings.use_query_string=A;this.callFlash("SetUseQueryString",[A])};SWFUpload.prototype.setRequeueOnError=function(A){this.settings.requeue_on_error=A;this.callFlash("SetRequeueOnError",[A])};SWFUpload.prototype.setHTTPSuccess=function(A){if(typeof A==="string"){A=A.replace(" ","").split(",")}this.settings.http_success=A;this.callFlash("SetHTTPSuccess",[A])};SWFUpload.prototype.setAssumeSuccessTimeout=function(A){this.settings.assume_success_timeout=A;this.callFlash("SetAssumeSuccessTimeout",[A])};SWFUpload.prototype.setDebugEnabled=function(A){this.settings.debug_enabled=A;this.callFlash("SetDebugEnabled",[A])};SWFUpload.prototype.setButtonImageURL=function(A){if(A==undefined){A=""}this.settings.button_image_url=A;this.callFlash("SetButtonImageURL",[A])};SWFUpload.prototype.setButtonDimensions=function(C,A){this.settings.button_width=C;this.settings.button_height=A;var B=this.getMovieElement();if(B!=undefined){B.style.width=C+"px";B.style.height=A+"px"}this.callFlash("SetButtonDimensions",[C,A])};SWFUpload.prototype.setButtonText=function(A){this.settings.button_text=A;this.callFlash("SetButtonText",[A])};SWFUpload.prototype.setButtonTextPadding=function(B,A){this.settings.button_text_top_padding=A;this.settings.button_text_left_padding=B;this.callFlash("SetButtonTextPadding",[B,A])};SWFUpload.prototype.setButtonTextStyle=function(A){this.settings.button_text_style=A;this.callFlash("SetButtonTextStyle",[A])};SWFUpload.prototype.setButtonDisabled=function(A){this.settings.button_disabled=A;this.callFlash("SetButtonDisabled",[A])};SWFUpload.prototype.setButtonAction=function(A){this.settings.button_action=A;this.callFlash("SetButtonAction",[A])};SWFUpload.prototype.setButtonCursor=function(A){this.settings.button_cursor=A;this.callFlash("SetButtonCursor",[A])};SWFUpload.prototype.queueEvent=function(B,C){if(C==undefined){C=[]}else{if(!(C instanceof Array)){C=[C]}}var A=this;if(typeof this.settings[B]==="function"){this.eventQueue.push(function(){this.settings[B].apply(this,C)});setTimeout(function(){A.executeNextEvent()},0)}else{if(this.settings[B]!==null){throw"Event handler "+B+" is unknown or is not a function"}}};SWFUpload.prototype.executeNextEvent=function(){var A=this.eventQueue?this.eventQueue.shift():null;if(typeof(A)==="function"){A.apply(this)}};SWFUpload.prototype.unescapeFilePostParams=function(C){var E=/[$]([0-9a-f]{4})/i;var F={};var D;if(C!=undefined){for(var A in C.post){if(C.post.hasOwnProperty(A)){D=A;var B;while((B=E.exec(D))!==null){D=D.replace(B[0],String.fromCharCode(parseInt("0x"+B[1],16)))}F[D]=C.post[A]}}C.post=F}return C};SWFUpload.prototype.testExternalInterface=function(){try{return this.callFlash("TestExternalInterface")}catch(A){return false}};SWFUpload.prototype.flashReady=function(){var A=this.getMovieElement();if(!A){this.debug("Flash called back ready but the flash movie can't be found.");return}this.cleanUp(A);this.queueEvent("swfupload_loaded_handler")};SWFUpload.prototype.cleanUp=function(A){try{if(this.movieElement&&typeof(A.CallFunction)==="unknown"){this.debug("Removing Flash functions hooks (this should only run in IE and should prevent memory leaks)");for(var C in A){try{if(typeof(A[C])==="function"){A[C]=null}}catch(B){}}}}catch(D){}window.__flash__removeCallback=function(E,F){try{if(E){E[F]=null}}catch(G){}}};SWFUpload.prototype.fileDialogStart=function(){this.queueEvent("file_dialog_start_handler")};SWFUpload.prototype.fileQueued=function(A){A=this.unescapeFilePostParams(A);this.queueEvent("file_queued_handler",A)};SWFUpload.prototype.fileQueueError=function(A,C,B){A=this.unescapeFilePostParams(A);this.queueEvent("file_queue_error_handler",[A,C,B])};SWFUpload.prototype.fileDialogComplete=function(B,C,A){this.queueEvent("file_dialog_complete_handler",[B,C,A])};SWFUpload.prototype.uploadStart=function(A){A=this.unescapeFilePostParams(A);this.queueEvent("return_upload_start_handler",A)};SWFUpload.prototype.returnUploadStart=function(A){var B;if(typeof this.settings.upload_start_handler==="function"){A=this.unescapeFilePostParams(A);B=this.settings.upload_start_handler.call(this,A)}else{if(this.settings.upload_start_handler!=undefined){throw"upload_start_handler must be a function"}}if(B===undefined){B=true}B=!!B;this.callFlash("ReturnUploadStart",[B])};SWFUpload.prototype.uploadProgress=function(A,C,B){A=this.unescapeFilePostParams(A);this.queueEvent("upload_progress_handler",[A,C,B])};SWFUpload.prototype.uploadError=function(A,C,B){A=this.unescapeFilePostParams(A);this.queueEvent("upload_error_handler",[A,C,B])};SWFUpload.prototype.uploadSuccess=function(B,A,C){B=this.unescapeFilePostParams(B);this.queueEvent("upload_success_handler",[B,A,C])};SWFUpload.prototype.uploadComplete=function(A){A=this.unescapeFilePostParams(A);this.queueEvent("upload_complete_handler",A)};SWFUpload.prototype.debug=function(A){this.queueEvent("debug_handler",A)};SWFUpload.prototype.debugMessage=function(C){if(this.settings.debug){var A,D=[];if(typeof C==="object"&&typeof C.name==="string"&&typeof C.message==="string"){for(var B in C){if(C.hasOwnProperty(B)){D.push(B+": "+C[B])}}A=D.join("\n")||"";D=A.split("\n");A="EXCEPTION: "+D.join("\nEXCEPTION: ");SWFUpload.Console.writeLine(A)}else{SWFUpload.Console.writeLine(C)}}};SWFUpload.Console={};SWFUpload.Console.writeLine=function(D){var B,A;try{B=document.getElementById("SWFUpload_Console");if(!B){A=document.createElement("form");document.getElementsByTagName("body")[0].appendChild(A);B=document.createElement("textarea");B.id="SWFUpload_Console";B.style.fontFamily="monospace";B.setAttribute("wrap","off");B.wrap="off";B.style.overflow="auto";B.style.width="700px";B.style.height="350px";B.style.margin="5px";A.appendChild(B)}B.value+=D+"\n";B.scrollTop=B.scrollHeight-B.clientHeight}catch(C){alert("Exception: "+C.name+" Message: "+C.message)}};

/**
 * swfupload 文件上传处理
 *
 */
function TuduUpload(opts) {
	this.opts = $.extend({}, TuduUpload.defaults, opts || {});
	
	this.init();
}

TuduUpload.prototype = {
	
	/**
	 * 
	 */
	opts: null,
	
	/**
	 * 事件处理对象实例
	 */
	handlers: {},
	
	/**
	 * swfupload 实例
	 */
	_swfupload: null,
	
	/**
	 * 
	 */
	_ts: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		var params = $.extend({}, TuduUpload.params), key, me = this;
		
		for (var k in this.opts) {
			if (k.indexOf('on') == 0 && typeof(this.opts[k]) == 'function') {
				key = k.replace(/^on/, '');
				this.bind(key, this.opts[k]);
				continue;
			}
			
			if (k.indexOf('_') == 0) {
				continue;
			}
			
			key = k.replace(/([A-Z])/g, '_$1').toLowerCase();
			params[key] = this.opts[k];
		}
		
		// 处理事件
		params.file_dialog_start_handler = function(){me.triggerHandler('file_dialog_start_handler', arguments)};
		params.file_queued_handler = function(){me.triggerHandler('file_queued_handler', arguments)};
		params.file_queue_error_handler = function(){me.triggerHandler('file_queue_error_handler', arguments)};
		params.file_dialog_complete_handler = function(){me.triggerHandler('file_dialog_complete_handler', arguments)};
		params.upload_start_handler = function(){me.triggerHandler('upload_start_handler', arguments)};
		params.upload_progress_handler = function(){me.triggerHandler('upload_progress_handler', arguments)};
		params.upload_error_handler = function(){me.triggerHandler('upload_error_handler', arguments)};
		params.upload_success_handler = function(){me.triggerHandler('upload_success_handler', arguments)};
		params.upload_complete_handler = function(){me.triggerHandler('upload_complete_handler', arguments)};
		params.queue_complete_handler = function(){me.triggerHandler('queue_complete_handler', arguments)};
		
		try {
			this._swfupload = new SWFUpload(params);
		} catch (e){}
	},
	
	bind: function(evt, func) {
		evt = evt.replace(/([A-Z])/g, '_$1').toLowerCase().replace(/^_/, '') + '_handler';
		if (undefined == this.handlers[evt]) {
			this.handlers[evt] = [];
		}
		
		this.handlers[evt].push(func);
		
		return this;
	},
	
	/**
	 * 
	 */
	unbind: function(evt, func) {
		if (undefined == this.handlers[evgt]) {
			return ;
		}
		
		if (typeof (func) == 'function') {
			for (var i = 0, c = this.handlers[evt].length; i < c; i++) {
				if (func == this.handlers[evt][i]) {
					delete this.handlers[evt][i];
				}
			}
		} else {
			delete this.handlers[evt];
		}
		
		return this;
	},
	
	/**
	 * 
	 */
	triggerHandler: function(evt, args) {
		if (undefined != this.handlers[evt]) {
			for (var i = 0, c = this.handlers[evt].length; i < c; i++) {
				if (typeof this.handlers[evt][i] == 'function') {
					this.handlers[evt][i].apply(this, args);
				}
			}
		}
	},
	
	/**
	 * 等待上传的文件数
	 */
	getQueueNum: function() {
		try {
			return this._swfupload.getStats().files_queued;
		} catch (e){return 0;}
	},
	
	getFileNum: function() {
		try {
			var stats = this._swfupload.getStats(),
			complete = stats.successful_uploads + stats.upload_errors + stats.upload_cancelled,
			inprogress = stats.in_progress,
			prepared  = stats.files_queued - inprogress,
			total = complete + prepared + inprogress;
			
			return total;
		} catch (e) {return 0}
	},
	
	/**
	 * 
	 */
	getCompleteNum: function() {
		try {
			var stats = this._swfupload.getStats(),
			complete = stats.successful_uploads + stats.upload_errors + stats.upload_cancelled;
			
			return complete;
		} catch (e) {return 0}
	},
	
	/**
	 * 正在上传文件数量
	 */
	inProgress: function() {
		try {
			return this._swfupload.getStats().in_progress;
		} catch (e) {return false;}
	},
	
	/**
	 * 是否有队列在上传
	 */
	isUploading: function() {
		return (this.getQueueNum() > 0 || this.inProgress() > 0);
	},
	
	/**
	 * 队列总体进度
	 */
	totalProgress: function() {
		if (!this._swfupload) return 0;
		if (!this.inProgress() && this.getQueueNum() <= 0) return 0
		
		try {
			var stats = this._swfupload.getStats(),
				complete = stats.successful_uploads + stats.upload_errors + stats.upload_cancelled,
				inprogress = stats.in_progress,
				prepared  = stats.files_queued - inprogress,
				total = complete + prepared + inprogress;
			//$('#a').html($('#a').html() + '<br>' +(inprogress + '|' + complete + '|' + total));
			return complete / total * 100;
		} catch (e) {return 0}
	},
	
	/**
	 * 更新绑定的事件
	 */
	setParam: function(eventName, func) {
		if (!this._swfupload) return this;
		if (typeof(func) == 'function') {
			this._swfupload.addSetting(eventName, func);
		}
		
		return this;
	},
	
	/**
	 * 设置事件处理
	 */
	setHandler: function(handler) {
		for (var k in handler) {
			this.setParam(k, handler[k]);
		}
	},
	
	/**
	 * 开始上传
	 */
	startUpload: function() {
		this._ts = (new Date()).getTime();
		try {
			this._swfupload.startUpload();
		} catch (e) {
			alert(TOP.TEXT.SWF_INIT_FAILURE);
		}
	},
	
	/**
	 * 取消上传
	 */
	cancelUpload: function(fileId) {
		try {
			this._swfupload.cancelUpload(fileId);
		} catch (e) {
			alert(TOP.TEXT.SWF_INIT_FAILURE);
		}
	},
	
	/**
	 * 获取当前文件上传耗时
	 */
	getUploadTime: function() {
		return (new Date()).getTime() - this._ts;
	},
	
	/**
	 * 激活事件调用
	 */
	execEvent: function(handler, args) {
		try {
			return this._swfupload.queueEvent(handler, args);
		} catch (e) {
			alert(TOP.TEXT.SWF_INIT_FAILURE);
		}
	},
	
	/**
	 * 获取swfupload对象实例
	 */
	getSwfUploadObj: function() {
		return this._swfupload;
	}
};

TuduUpload.defaults = {
	flashUrl: '',
	uploadUrl: '',

	fileSizeLimit: '10 MB',
	fileTypes: '*.*',
	fileTypeDesc: 'All Files',
	fileUploadLimit: 10,
	fileQueryLimit: 0,
	debug: false,
	postParams: {},
	
	buttonImageUrl: '',
	buttonWidth: '65',
	buttonHeight: '29',
	buttonPlaceholderId: '',
	buttonText: '',
	buttonTextStyle: '',
	buttonTextLeftPadding: 0,
	buttonTextTopPadding: 0
};

/**
 * 默认的初始化
 */
TuduUpload.params = {
	flash_url : "",
	upload_url: "",
	post_params: {},
	file_size_limit : "",
	file_types : "*.*",
	file_types_description : "",
	file_upload_limit : 10,
	file_queue_limit : 0,
	custom_settings : {},
	debug: false,

	// Button settings
	button_image_url: "",
	button_width: "",
	button_height: "",
	button_placeholder_id: "",
	button_window_mode: 'transparent',
	button_text: '',
	button_text_style: "",
	button_text_left_padding: 12,
	button_text_top_padding: 3,
	button_cursor: -2
};

TuduUpload.handlerNames = [
'file_dialog_start_handler',
'file_queued_handler',
'file_queue_error_handler',
'file_dialog_complete_handler',
'upload_start_handler',
'upload_progress_handler',
'upload_error_handler',
'upload_success_handler',
'upload_complete_handler',
'queue_complete_handler' 
];

/**
 * 
 */
var UploadQueue = function(param) {
	this._queue = {};
	this._upload = param.upload ? param.upload : null;
	this._jq = param.jq ? param.jq : $;
	
	this.init();
};

UploadQueue.prototype = {
	
	_ele: null,
	
	_upload: null,
	
	_list: null,
	
	_speed: null,
	
	_queue: null,
	
	_jq: null,
	
	/**
	 * 初始化
	 */
	init: function() {
		var me = this;
		this._list = $('<div class="upload_queue"></div>');
		
		this._upload.bind('fileQueued', function(file){
			me.appendItem(file);
		}).bind('uploadProgress', function(file, uploaded, total){
			var costTime = me._upload.getUploadTime();
			
			me._speed = uploaded / costTime;
			
			obj = me.getFileItem(file.id);
			
			obj.setProgress(uploaded / total * 100).setSpeed(me._speed);
			obj.setUploaded(uploaded);
			
		}).bind('uploadSuccess', function(file, data){
			try {
				var ret;
				eval('ret='+data+';');
				
				if (ret.success){
					me.getFileItem(file.id).success(ret);
				} else {
					me.getFileItem(file.id).error(0, ret.message);
				}
				
				this.startUpload();
				
			} catch (ex) {
				this._swfupload.debug(ex);
				this.startUpload();
			}
						
		}).bind('uploadComplete', function(){
			
			this.startUpload();
			
		}).bind('uploadError', function(file, errcode, message){
			me.getFileItem(file.id).error(errcode, message);
		});
	},
	
	/**
	 * 添加文件项目
	 */
	appendItem: function(file) {
		var item = new UploadItem({
			fileId: file.id,
			fileName: file.name,
			fileSize: file.size
		});
		
		item._queue = this;
		this._queue[file.id] = item;
		
		item.appendTo(this._list);
	},
	
	/**
	 * 获取文件
	 */
	getFileItem: function(fileId) {
		return this._queue[fileId];
	},
	
	/**
	 * 
	 */
	cancel: function(fileid) {
		this._upload.cancelUpload(fileid);
		this.getFileItem(fileid).cancel();
	},
	
	/**
	 * 
	 */
	appendTo: function(parent) {
		this._list.appendTo(parent);
	}
};

var UploadItem = function(item, param) {
	this._fileId = item.fileId;
	this._fileName = item.fileName;
	this._fileSize = item.fileSize;
	this._jq = param && param.jq ? param.jq : $;
	this.init();
};

UploadItem.template = [
'<div class="upload_item">',
'<input type="hidden" name="fileid" value="" />',
'<div><a href="javascript:void(0)" class="cancel" style="float:right">['+TOP.TEXT.CANCEL+']</a><span class="filename"></span></div>',
'<div class="item_progress_bar" style="display:none"><div class="item_progress">0%</div><div class="item_progress_inner"></div></div>',
'<p class="item_progress_info">'+TOP.TEXT.UPLOADED+': <span class="item_uploaded">0KB</span>&nbsp;&nbsp;'+TOP.TEXT.SPEED+': <span class="item_speed">0KB</span></p>',
'<p class="item_info">'+TOP.TEXT.WAITING_UPLOAD+'...</p>',
'</div>'
].join('');

UploadItem.prototype = {
	
	/**
	 * DOM元素
	 */
	_el: null,
	
	/**
	 * 文件ID
	 */
	_fileId: null,
	
	/**
	 * 文件名
	 */
	_fileName: null,
	
	/**
	 * 文件大小
	 */
	_fileSize: null,
	
	/**
	 * 上传开始时间
	 */
	_startTime: null,
	
	/**
	 * 
	 */
	_queue: null,
	
	/**
	 * 
	 */
	_jq: null,
	
	/**
	 * 
	 */
	init: function() {
		var me = this;
		this._el = this._jq(UploadItem.template);
		this._el.attr('id', 'upi-' + this._fileId);
		
		this._el.find('.item_progress_info').hide();
		this._el.find('a.cancel').bind('click', function(){
			if (!confirm(TOP.TEXT.UPLOAD_CANCEL_HINT)) {
				return ;
			}
			me._queue.cancel(me._fileId);
		});
		this._el.find('span.filename').html(this._fileName + '<span class="gray">(' + formatFileSize(this._fileSize) + ')</span>');
	},
	
	getEl: function() {
		return this._el;
	},
	
	/**
	 * 设置速度
	 */
	setSpeed: function(speed) {
		this._el.find('.item_speed').text(formatFileSize(speed * 1000) + '/s');
		return this;
	},
	
	/**
	 * 设置进度
	 */
	setProgress: function(progress) {
		this._el.find('.item_progress_info, .item_progress_bar').show();
		this._el.find('.item_info').hide();
		this._el.find('.item_progress').text(parseInt(progress) + '%');
		this._el.find('.item_progress_inner').css('width', parseInt(progress) + '%');
		return this;
	},
	
	setUploaded: function(uploaded) {
		this._el.find('.item_uploaded').text(formatFileSize(uploaded));
	},
	
	/**
	 * 
	 */
	success: function(fileid) {
		this._el.find('.item_progress_info, .item_progress_bar').hide();
		this._el.find('.item_progress').text('100%');
		this._el.addClass('item_success');
		this._el.find('a.cancel').remove();
		this._el.find('input[name="fileid"]').val(fileid);
		this._el.find('.item_info').text(TOP.TEXT.UPLOAD_SUCCESS).show();
	},
	
	/**
	 * 
	 */
	error: function(errno, message) {
		this._el.find('.item_progress_info, .item_progress_bar').hide();
		this._el.find('.item_progress').text('100%');
		this._el.addClass('item_error');
		this._el.find('a.cancel').remove();
		this._el.find('input[name="fileid"]').val(fileid);
		
		var message = this.getMessage(message);
		if (message) {
			message = TOP.TEXT.UPLOAD_ERROR + TOP.TEXT.CLN + message;
		} else {
			message = TOP.TEXT.UPLOAD_ERROR;
		}
		
		this._el.find('.item_info').text(message).show();
	},
	
	/**
	 * 
	 */
	cancel: function() {
		this._el.remove();
	},
	
	/**
	 * 
	 */
	appendTo: function(parent) {
		this._el.appendTo(parent);
	},
	
	getMessage: function(message) {
		if (/enough\s+space/i.test(message)) {
			return TOP.TEXT.SPECE_NOT_ENOUTH;
		}
		
		return '';
	}
};

/**
 * 上传信息显示
 */
var UploadInfo = function(params) {
	this._upload = param.upload ? param.upload : null;
	this._jq = param.jq ? param.jq : $;
	this.init();
};

UploadInfo.prototype = {
	
	_upload: null,
	
	_el: null,
	
	init: function() {
		var me = this;
		
		me._el = $('<div class="upload_info"><span class="upload_info_speed"></span><span class="upload_info_progress"></span></div>');
		
		this._upload.bind('uploadProgress', function(file, uploaded, total){
			var costTime = me._upload.getUploadTime();
			
			var speed = uploaded / costTime * 1000,
				totalprogress = me._upload.totalProgress() + (uploaded / total * 100 / me._upload.getFileNum());
			
			me._el.find('.upload_info_speed').text(formatFileSize(speed) + '/s');
			me._el.find('.upload_info_progress').text(Math.round(totalprogress * 100) / 100 + '%');
		}).bind('uploadComplete', function(){
			if (!me._upload.inProgress() && !me._upload.getQueueNum()) {
				me.hide();
				me._el.find('.upload_info_progress').text('100%');
			}
		});
	},
	
	hide: function() {
		this._el.hide()
	},
	
	appendTo: function(parent) {
		this._el.appendTo(parent);
	}
};

if (getTop == undefined) {
	function getTop() {
		return parent;
	}
}

var Tudu = Tudu || {},
	TOP = getTop();

//
Tudu.Attachment = function(options) {
	
}
Tudu.Attachment.prototype = {
	
	list: null,
	
	/**
	 * 列表项目父元素
	 */
	container: null,
	
	/**
	 * 上传控件实例
	 */
	_upload: null,
	
	/**
	 * 列表项目
	 */
	_items: {},
	
	/**
	 * 上传文件
	 */
	fileCount: 0,
	
	/**
	 * 已完成
	 */
	completes: 0,
	
	/**
	 * 设置附件显示HTML元素父级元素
	 */
	setContainer: function(parent) {
		if (typeof parent == 'string') {
			this._container = $(parent);
			return ;
		}
		
		this._container = parent;
	},
	
	/**
	 * 分配上传控件资源，接管上传控件事件
	 */
	setUpload: function(upload) {
		this._upload = upload;
		this._upload.setHandler(new Tudu.Attachment.handlers(this));
		this._upload.handler = this;
	},
	
	/**
	 * 获取列表元素
	 */
	getItem: function(fileId) {
		if (typeof(this._items[fileId]) != undefined) {
			return this._items[fileId];
		}
		
		return null;
	},
	
	/**
	 * 添加列表附件
	 */
	add: function(fileId, fileName, fileSize, fid) {
		this.list.show();
		
		var item = new Tudu.Attachment.Item(fileId, fileName, fileSize);
		item.setContainer(this);
		item.appendTo(this.container);
		
		if (fid) {
			item.setAttachmentId(fid);
		}
		
		this._items[fileId] = item;
		
		return item;
	},
	
	/**
	 * 删除列表附件
	 */
	remove: function(fileId) {
		this.getItem(fileId).remove();
		this._upload.cancelUpload(fileId);
		if (!this.container.find('div.filecell').size()) {
			this.list.hide();
		}
		
		if (!this._upload.inProgress()) {
			this._upload.startUpload();
		}
		
		this.fileCount--;
	},
	

	/**
	 * 清空附件列表
	 */
	removeAll: function() {
		
		try {
			this.container.find('div.filecell').each(function(){
				this._upload.cancelUpload(this.id);
			});
		} catch (e) {}
		
		this.fileCount = 0;
		this.container.empty();
	}
};

/**
 * 上传事件处理
 */
Tudu.Attachment.handlers = function(obj) {
	
	/**
	 * 文件队列创建事件
	 */
	this.file_queued_handler = function(file) {
		obj.add(file.id, file.name, file.size);
	},
	
	/**
	 * 队列错误触发事件
	 */
	this.file_queue_error_handler = function(file, code, msg) {
		// 文件大小超过限制
		if (code == -110) {
			TOP.showMessage(TOP.formatString(TOP.TEXT.FILESIZE_EXCEED_LIMIT, file.name, this.settings.file_size_limit + 'KB'));
		} else if (code == -120) {
			TOP.showMessage(TOP.formatString(TOP.TEXT.FILESIZE_ZERO_BYTE, file.name));
		}
	},
	
	/**
	 * 文件选择窗口关闭触发事件
	 */
	this.file_dialog_complete_handler = function(fileCount, queryLength) {
		try {
			this.startUpload();
		} catch (e) {}
	},
	
	/**
	 * 开始上传触发事件
	 */
	this.upload_start_handler = function() {
		//getTop().showMessage(getTop().TEXT.FILE_UPLOADING);
	},
	
	/**
	 * 进度更新触发事件
	 */
	this.upload_progress_handler = function(file, uploaded, total) {
		var item = obj.getItem(file.id),
			percent = parseInt((uploaded / total) * 100);
		
		if (item) {
			item.progress(percent);
		}
		
		try {
			var stats = this.getStats(),
				_$ = TOP.getJQ();
			obj.completes = stats.upload_errors + stats.successful_uploads;
			obj.fileCount = stats.files_queued + obj.completes;
			this.totalProgress = parseInt((((obj.completes/obj.fileCount) + obj.completes)/obj.fileCount) * 100);
			
			if (_$('#msg-progress').size()) {
				progress = Math.min(95, this.totalProgress);
				
				_$('#msg-progress div').width(progress + '%');
				_$('#msg-txt-progress').text(progress + '%');
			}
		} catch (e){}
	},
	
	/**
	 * 上传错误触发事件
	 */
	this.upload_error_handler = function(file, code, msg) {
		var item = obj.getItem(file.id);
		if (item) {
			item.removeProgress();
		}
	},
	
	/**
	 * 文件上传成功
	 */
	this.upload_success_handler = function(file, response) {
		try {
			var ret;
			eval('ret='+response+';');
			
			var item = obj.getItem(file.id);
			if (ret.success){
				var fileid = ret.fileid ? ret.fileid : (ret.data ? ret.data.fileid : null);
				item.success(fileid);
			} else {
				getTop().showMessage(ret.message);
				item.error();
			}
			this.startUpload();
			
		} catch (ex) {
			this.debug(ex);
			this.startUpload();
		}
	},
	
	/**
	 * 文件上传完成触发事件
	 */
	this.upload_complete_handler = function() {
	},
	
	/**
	 * 文件队列完成触发事件
	 */
	this.queue_complete_handler = function() {
		getTop().showMessage(getTop().TEXT.FILE_UPLOAD_COMPLETE);
	}
};

/**
 * 上传附件显示
 */
Tudu.Attachment.Item = function(fileId, fileName, fileSize) {
	this.init(fileId, fileName, fileSize);
};
/**
 * HTMl元素模板
 */
Tudu.Attachment.Item.tpl = [
	'<div class="filecell"><input type="hidden" name="attach[]" value="" />'
	,'<div class="attsep"><div class="attsep_file"><span class="icon icon_add"></span><span class="filename"></span>&nbsp;<span class="filesize"></span></div>'
	,'<div class="attsep_bar"><div class="attsep_rate" style="width:0"></div></div>'
    ,'<div class="attsep_del"><a href="javascript:void(0)" name="delete"></a></div>'
    ,'<div class="clear"></div></div></div>'
].join('');
/**
 * @prototype
 * @class AttachItem
 */
Tudu.Attachment.Item.prototype = {
	/**
	 * HTML元素jQuery对象
	 */
	_el: null,
	
	/**
	 * 
	 */
	_attachmentCt: null,
	
	/**
	 * 初始化
	 */
	init: function(fileId, fileName, fileSize) {
		var _this = this;
		this._el = $(Tudu.Attachment.Item.tpl);
		
		this._el.find('a[name="delete"]').text(TOP.TEXT.DELETE);
		
		this._el
		.attr('id', fileId)
		.find('.filename').text(fileName);
		
		fileSize = fileSize > 1024 ? Math.round(fileSize / 1024, 2) + 'KB' : fileSize + 'bytes',
		this._el.find('.filesize').text('(' + fileSize + ')');
		this._el.find('a[name="delete"]').click(function(){
			_this._attachmentCt.remove(fileId);
		});
	},
	
	/**
	 * 设置容器
	 */
	setContainer: function(obj) {
		this._attachmentCt = obj;
	},
	
	/**
	 * 设置附件ID
	 */
	setAttachmentId: function(fid) {
		this._el.find('input[name="attach[]"]').val(fid);
	},
	
	/**
	 * 获取附件ID
	 */
	getAttachmentId: function() {
		return this._el.find('input[name="attach[]"]').val();
	},
	
	/**
	 * 添加到列表
	 */
	appendTo: function(parent) {
		parent.append(this._el);
	},
	
	/**
	 * 删除附件
	 */
	remove: function() {
		this._el.remove();
		this._el = null;
	},
	
	/**
	 * 更新进度
	 */
	progress: function(percent) {
		this._el.find('.attsep_bar div').css('width', Math.min(90, percent) + '%');
	},
	
	/**
	 * 移除进度条
	 */
	removeProgress: function() {
		if (this._el && this._el.find('.attsep_bar').size()) {
			this._el.find('.attsep_bar').remove();
		}
	},
	
	/**
	 * 文件错误
	 */
	error: function() {
		this.removeProgress();
		this._el.addClass('upload_error')
		.find(':hidden[name="attach[]"]')
		.remove();
	},
	
	/**
	 * 上传成功
	 */
	success: function(attachid) {
		this.removeProgress();
		this._el.find(':hidden[name="attach[]"]').val(attachid);
	}
};

function formatFileSize(size) {
	var units = {
		1073741824: 'GB',
		1048576: 'MB',
		1024: 'KB'
	};
	
	for (var s in units) {
		if (size / s >= 1) {
			return Math.round(size / s * 100) / 100 + units[s];
		}
	}
	
	return Math.round(size * 100) / 100 + 'B';
}