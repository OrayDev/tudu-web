/**
 * Tudutalk Web
 *
 */
var TuduTalk = {
   
/**
 * 是否已经安装Tudutalk客户端
 * 
 * @return boolean
 */
isInstalled: function() {
	/*
	var ret = false;
	try {
		var o = new ActiveXObject('PRTSCRN.PrtScrnCtrl.1');
		if (null != o) {
			ret = true;
		}
	} catch (e) {}
	
	return ret;*/
	
	return true;
},

/**
 * 开始聊天
 * 
 * @param {String}   email           聊天人帐号
 * @param {Function} installCallback 未安装客户端时回调
 */
talk: function(email, installCallback) {
	if (!this.isInstalled()) {
		if (typeof(installCallback) == 'function') {
			return installCallback.call(this);
		}
		
		return ;
	}
	
	var url = 'tdim://chat?jid=' + email;
	
	this.openTalk(url);
},

/**
 * 打开聊天窗口
 * 
 * @param {String} url
 */
openTalk: function(url) {
	window.location = url;
}

};