/**
 * 企业信息、企业实名认证等Javascript
 * 
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com
 * @version    $Id: org.js 2713 2013-01-23 10:17:49Z cutecube $
 */

var Org = {};

/**
 * 企业信息设置
 */
Org.Info = {
	/**
	 * 初始化企业信息设置页面
	 */
	init: function () {
		if (undefined !== _DATA && _DATA.industry) {
			var idu = new UI.SingleSelect({
				options: _DATA.industry,
				defaultText: '请选择所属行业',
				name: 'industry',
				cls: 'select-big',
				menuCls: 'option',
				maxHeight: 250,
				tabIndex: 4,
				selected: $('#ids-val').val()
			});
			
			idu.replace('#idu-replace');
		}
		
		if (undefined !== _DATA && _DATA.province) {
			var prov = [];
			for (var i = 0, c = _DATA.province.length; i < c; i++) {
				prov[prov.length] = {text: _DATA.province[i].name, value: _DATA.province[i].name};
			}
			
			var sprovince = new UI.SingleSelect({
				id: 'select-province',
				options: prov,
				defaultText: '省份',
				name: 'province',
				cls: 'select-big',
				menuCls: 'option',
				maxHeight: 250,
				tabIndex: 2,
				css: {width: '172px'}
			});
			
			var scity = new UI.SingleSelect({
				id: 'select-city',
				defaultText: '城市',
				name: 'city',
				cls: 'select-big',
				menuCls: 'option',
				maxHeight: 250,
				tabIndex: 3,
				css: {width: '172px'}
			});
			
			sprovince.replace('#province-replace');
			scity.replace('#city-replace');
			
			sprovince.bind('change', function() {
				var p = this.getValue(),
					city = UI.get('select-city'),
					data = _DATA.province;

				city.empty();
				var options = []; 
				for (var i = 0, c = data.length; i < c; i++) {
					if (data[i].name == p) {
						var arr = data[i].district.split('|');
						for (var j = 0, l = arr.length; j < l; j++) {
							if (!arr[j]) {
								continue; 
							}
							
							city.addOption(arr[j]);
						}
					}
				}
				city.refreshMenu();
			});
			
			sprovince.select($('#province-val').val());
			scity.select($('#city-val').val());
		}
		
		$('input.text-big, textarea').bind('focus blur mouseover mouseout', function(e) {
			if (e.type == 'focus' || e.type == 'mouseover') {
				$(this).addClass('text-hover');
			} else {
				if (e.type == 'blur' || document.activeElement != this) {
					$(this).removeClass('text-hover');
				}
			}
		});

		$('input[name="entirename"]').bind('blur', function(){
			var orgName = $('input[name="orgname"]').val();
			if (!orgName.length) {
				$('input[name="orgname"]').val($(this).val());
			}
		});
	
		$('#infoform').submit(function(){return false;});
		$('#infoform').submit(function(){
			var form = $(this);
			var data = form.serializeArray();

			$.ajax({
		        type: 'POST',
		        dataType: 'json',
		        url: form.attr('action'),
		        data: data,
		        success: function(ret) {
		            Message.show(ret.message, 5000, ret.success);
		        },
		        error: function(res) {
		        	Message.show(Message.PROCESSING_ERROR);
					return false;
		        }
		    });
		});
		
		var intro = $('#intro').val(),
		c = intro.length,
		l = intro.split(/\n|\r|\r\n/).length;
		var hint = $('#intro-hint'),
			hintText = '还可以输入'+(300-c)+'个字符';
		
		if (l > 13) {
			hintText += '，超出了<span class="red">' + (l-13) + '</span>行，超出部分将无法显示';
		}
		hint.html(hintText);
		$('#intro').bind('keydown keypress', function(e){
			var code = e.keyCode ? e.keyCode : e.which,
				l    = this.value.split(/\n|\r|\r\n/).length;

			if (code == 13 && l >= 12) {
				e.returnValue = false;
				return false;
			}
		});
		$('#intro').bind('keyup', function(){
			var c = 300 - $(this).val().length,
				l = this.value.split(/\n|\r|\r\n/).length,
				hintText = '';

			if (c > 0) {
				hintText = '还可以输入'+c+'个字符';
			} else {
				hintText = '已经超出<span class="red">'+Math.abs(c)+'</span>个字符';
			}
			
			if (l > 12) {
				hintText += '，超出了<span class="red">' + (l-13) + '</span>行，超出部分将无法显示';
			}
			
			hint.html(hintText);
		});
		
		_TOP.switchMod('info');
	}
};

/**
 * 实名认证
 */
Org.Realname = {
	
	uploadWin: null,
	
	/**
	 * 初始化企业实名认证页面
	 */
	init: function() {
		var me = this;
		$('#real-upload').bind('click', function(){
			me.initUpload();
		});
		
		_TOP.switchMod('realname');
		
		$('#realname-img').bind('load', function(){
			var w = this.width;
			if (w > 450) {
				this.style.width = '450px';
			}
		});
	},
	
	/**
	 * 初始化上传窗口
	 */
	initUpload: function() {
		var me = this;
		if (this.uploadWin === null) {
			this.uploadWin = Admin.window({
				id: 'real-uploadwin',
				width: 550,
				title:'上传营业执照',
				formid: 'real-uploadform',
				body: ['<div class="realwin"><p class="gray">(请点击“浏览”，在您电脑中选择您要上传的照片。)</p>',
					   '<div>选择照片：<input id="realfile" type="file" name="file" class="btn" size="40" />&nbsp;<input id="uploadfile" type="button" name="upload" class="btn" value="立即上传" /><input id="fileurl" type="hidden" name="fileurl" value="" /></div>',
					   '<div class="line-solid"></div>',
					   '<div id="preview-img" style="display:none;"><p>缩略图</p>',
					   '<div class="preview-box"><img id="real-img" src=""></div></div>',
					   '</div>'
					   ].join(''),
				footer: '<input name="submit" type="submit" disabled="disabled" class="btn" value="确定"><input name="close" type="button" class="btn" value="取消" />',
				action: BASE_PATH + '/org/real/save',
				draggable: true,
				init: function(){
					var form = this.find('form');
					form.submit(function(){return false;});
					form.submit(function(){
						me.save(form);
					});
					
					this.find('input[name="close"]').click(function() {
						me.uploadWin.close();
					});
					
					this.find('input[name="upload"]').bind('click', function() {
						me.uploadFile('#real-uploadform');
					});
					
					Frame.queryParent('#real-img').bind('load', function(){
						var w = this.width;
						if (w > 450) {
							$(this).css({
								width: '450px',
								height: 'auto'
							});
						}
						var h = this.height;
						if (h > 300) {
							$(this).css({
								width: 'auto',
								height: '300px'
							});
						}
						if (me.uploadWin !== null) {
							me.uploadWin.center();
						}
					});
				},
				onShow: function(){
				},
				onClose: function(){
					me.uploadWin.destroy();
					me.uploadWin = null;
				}
			});
		}
		
		this.uploadWin.show();
	},
	
	/**
	 * 上传文件
	 *
	 * @param {Object} form
	 */
	uploadFile: function(form) {
		Frame.queryParent('#fileurl').val('');
		Frame.queryParent('input[name="submit"]').attr('disabled', true);
		
		var file = Frame.queryParent('#realfile');
		var filepath = file.val();
		var extStart = filepath.lastIndexOf('.'); 
		var ext      = filepath.substring(extStart,filepath.length).toUpperCase(); 
		if (ext != ".BMP" && ext != ".PNG" && ext != ".GIF" && ext != ".JPG" && ext != ".JPEG") {
			Message.show('仅限上传jpg、png、gif格式的图片');
			Frame.queryParent('#preview-img').hide();
			file.val('');
			return false;
		}
		
		this.upload(form);
		
	},
	
	/**
	 * 保存
	 */
	save: function(form) {
		var me      = this;
		var form    = $(form);
		var fileurl = form.find('input[name="fileurl"]').val();
		
		if (fileurl.length <= 0) {
			Message.show('获取图片路径失败，请重试');
			return false;
		}

		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: form.attr('action'),
	        data: {fileurl: fileurl},
	        success: function(ret) {
	            Message.show(ret.message, 5000, ret.success);

	            if(ret.success) {
	            	if (me.uploadWin !== null) {
						me.uploadWin.close();
					}
					location.assign(location.href);
	            }
	        },
	        error: function(res) {
	        	Message.show(Message.PROCESSING_ERROR);
				return false;
	        }
	    })
	},
	
	/**
	 * 上传
	 */
	upload: function(form) {
		var me = this,form = $(form);
		
		Frame.getJQ().ajaxUpload({
			url: BASE_PATH + '/org/real/upload',
			file: Frame.queryParent('#realfile'),
			data: {},
			dataType: 'json',
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				
				if (!ret.success) {
					Frame.queryParent('#realfile').val('');
					Frame.queryParent('#fileurl').val('');
					Frame.queryParent('#preview-img').hide();
					Frame.queryParent('input[name="submit"]').attr('disabled', true);
				}
				
				if (ret.success && ret.data.fileurl) {
					Frame.queryParent('#preview-img').show();
					Frame.queryParent('#real-img').attr('src', BASE_PATH + '/org/real/file?hash=' + ret.data.fileurl);
					Frame.queryParent('#fileurl').val(ret.data.fileurl);
					Frame.queryParent('input[name="submit"]').attr('disabled', false);
				}
				
				if (me.uploadWin !== null) {
					me.uploadWin.center();
				}
			},
			error: function(res) {
				Message.show(Message.PROCESSING_ERROR);
				Frame.queryParent('#realfile').val('');
				Frame.queryParent('#fileurl').val('');
				Frame.queryParent('#preview-img').hide();
				Frame.queryParent('input[name="submit"]').attr('disabled', true);
				return false;
			}
		});
	}
};

/**
 * 超级管理员密码修改
 */
Org.Password = {
	/**
	 * 初始化超级管理员密码修改页面
	 */
	init: function() {
		var pwd = $(':password[name="pwd"]');

		var levelBar = new UI.LevelBar({
			id: 'pwd-lev',
			cls: 'pwd_level_bar',
			readOnly: true,
			levels: {
				1: {text: '弱', value: 1},
				2: {text: '中', value: 2},
				3: {text: '强', value: 3}
			}
		});
		levelBar.replace('#pwdlevel');

		pwd.bind('keyup', function(){
			if (!this.value || this.value.length < 6) {
				return levelBar.setLevel();
			}
			
			var lev = Org.Password.checkLevel(this.value);
			
			levelBar.setLevel(lev)
			.removeClass('pwd_level_0 pwd_level_1 pwd_level_2 pwd_level_3')
			.addClass('pwd_level_' + lev);
		});

		$('input.text-big, textarea').bind('focus blur mouseover mouseout', function(e) {
			if (e.type == 'focus' || e.type == 'mouseover') {
				$(this).addClass('text-hover');
			} else {
				if (e.type == 'blur' || document.activeElement != this) {
					$(this).removeClass('text-hover');
				}
			}
		});

		$('#pwdform').bind('submit', function(){return false;});
		$('#pwdform').bind('submit', function(){
			var form = $(this),
				pwd  = form.find(':password[name="pwd"]').val(),
				repwd= form.find(':password[name="repwd"]').val(),
				userid = $('#userid').val(),
				orgid  = $('#orgid').val();
			var data = form.serializeArray();
			
			if (pwd != repwd) {
				return Message.show('密码与确认密码不一致');
			}
			
			if (pwd == orgid) {
				return Message.show('密码不能与组织ID相同');
			}
			
			if (pwd == userid) {
				return Message.show('密码不能与帐号名相同');
			}

			form.find('input,button').attr('disabled', 'disabled');
			$.ajax({
		        type: 'POST',
		        dataType: 'json',
		        url: form.attr('action'),
		        data: data,
		        success: function(ret) {
		            Message.show(ret.message, 5000, ret.success);
	
		            if(ret.success) {
		            	form.find(':password').val('');
		            	levelBar.setLevel();
		            }
		            
		            form.find('input,button').removeAttr('disabled');
		        },
		        error: function(res) {
		        	Message.show(Message.PROCESSING_ERROR);
		        	form.find('input,button').removeAttr('disabled');
					return false;
		        }
		    });
		});
		
		_TOP.switchMod('pwd');
	},
	
	/**
	 * 检测密码强度
	 */
	checkLevel: function(str) {
		var lev = 0;
		if ((/\d+/).test(str)) lev++; 
		if ((/[a-zA-Z]+/).test(str)) lev++;
		if ((/[^0-9a-zA-Z]+/).test(str)) lev++;
		
		return lev;
	}
};

Org.Email = {
	/**
	 * 修改密保邮箱页面
	 */
	init: function() {
		$('input.text-big, textarea').bind('focus blur mouseover mouseout', function(e) {
			if (e.type == 'focus' || e.type == 'mouseover') {
				$(this).addClass('text-hover');
			} else {
				if (e.type == 'blur' || document.activeElement != this) {
					$(this).removeClass('text-hover');
				}
			}
		});
		
		var domains = ["163.com", "126.com", "qq.com", "hotmail.com", "gmail.com", "sina.com", "sina.cn", "sohu.com", "yahoo.com", "139.com", "wo.com.cn", "189.cn"];
		var emailhint = new UI.AutoComplete({
			input: '#email',
			cls: 'autocomplete-menu',
			dataSource: function(callback) {
				var v = this._input.val();
				if (!v.length) {
					return null;
				}
				
				var arr = v.split('@'), u = arr[0], domain = '';
				if (arr.length > 1) {
					domain = arr[1];
				}
				
				var ret = [], e;
				for (var i = 0, c = domains.length; i < c; i++) {
					if (!domain || domains[i].indexOf(domain) === 0) {
						e = u + '@' + domains[i];
						ret[ret.length] = {text: e, value: e};
					}
				}
				
				callback.call(this, ret);
			}
		});
	
		$('#emailform').bind('submit', function(){return false;});
		$('#emailform').bind('submit', function(){
			var form = $('#emailform'),
				data = form.serializeArray();
			
			$.ajax({
		        type: 'POST',
		        dataType: 'json',
		        url: form.attr('action'),
		        data: data,
		        success: function(ret) {
		            Message.show(ret.message, 5000, ret.success);
	
		            if(ret.success && ret.data.authid) {
		            	location = BASE_PATH + '/org/email/sent?authid=' + ret.data.authid;
		            }
		        },
		        error: function(res) {
		        	Message.show(Message.PROCESSING_ERROR);
					return false;
		        }
		    });
		});
	},
	
	send: function(authid) {
		$.ajax({
	        type: 'POST',
	        dataType: 'json',
	        url: BASE_PATH + '/org/email/send',
	        data: {authid: authid},
	        success: function(ret) {
	            Message.show(ret.message, 5000, ret.success);
	        },
	        error: function(res) {
	        	Message.show(Message.PROCESSING_ERROR);
				return false;
	        }
	    });
	}
};

/**
 * Logo 修改
 */
Org.Logo = {
	_cookies: null,
	
	_flashUrl: null,
	
	_jcrop: null,
	
	setCookies: function(cookies) {
		this._cookies = cookies;
		return this;
	},
	
	setFlashUrl: function(url) {
		this._flashUrl = url;
		return this;
	},
	
	init: function() {
		var swfupload = new SWFUpload({
			upload_url: _TOP.SITES.tudu + BASE_PATH + "/org/info/upload",
			flash_url: this._flashUrl,
			button_placeholder_id : "swfplace",
			post_params: {'cookies': this._cookies},
			file_types: "*.jpg;*.gif;*.png",
			file_types_description: "图片文件",
			file_queue_limit: "1",
			button_width: "80",
			button_height: "25",
			button_text_left_padding: 20,
			button_text_top_padding: 1,
			button_window_mode: 'transparent',
			file_queued_handler: function(file){},
			file_queue_error_handler: function(file, errorCode, message){
				try {
					switch (errorCode) {
					case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
						Message.show('只能上传单个文件');
						break;
					case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
						Message.show('文件大小超过限制');
						break;
					case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
						Message.show('不能上传0字节文件');
						break;
					case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
						Message.show('文件类型错误');
						break;
					default:
						Message.show('Error' + errorCode);
						break;
					}
				} catch (ex) {
			        this.debug(ex);
			    }
			},
			file_dialog_complete_handler: function(numFilesSelected, numFilesQueued){
				try {
					if (numFilesSelected > 0 && numFilesSelected <= 1) {
						Message.show('正在上传文件，请稍后...', 5000, true);
						$('#input').attr('disabled', true);
						this.startUpload();
					}
				} catch (ex)  {
			        this.debug(ex);
				}
			},
			upload_success_handler: function(file, response){
				var ret;
				eval('ret='+response+';');
				
				if (ret.success && ret.data.logo) {
					$('#hash').val(ret.data.logo);
					$('#logo-img')
					.attr('src', BASE_PATH + '/org/info/logo-img?hash=' + ret.data.logo)
					.bind('load', function(){
						$('#logo-preview')
						.attr('src', BASE_PATH + '/org/info/logo-img?hash=' + ret.data.logo);
						
						Org.Logo.initJcrop(this);
						
						$('#logo-preview-div').show();
					})
					.parent().removeClass('logo-add');
					
				} else {
					Message.show(ret.message);
				}
				
				$('#btn-bar').show();
			},
			upload_complete_handler: function(file){
				if (this.getStats().files_queued === 0) {
					$('input').removeAttr('disabled');
				}
			}
		});
		
		$('#logoform').bind('submit', function(){return false;});
		$('#logoform').bind('submit', function(){
			var form = $(this);
			
			var data = form.serializeArray();
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: form.attr('action'),
				data: data,
				success: function(ret) {
					Message.show(ret.message, 5000, ret.success);
					if (ret.success) {
						location.reload(true);
					}
				},
				error: function() {
					return false;
				}
			});
		});
		
		_TOP.switchMod('logo');
	},
	
	initJcrop: function(img) {
		var w = img.width;
		var h = img.height;
		
		var scale = 1;
		if (w > 375 || h > 500) {
			$('#edit-box-big')
            .css({width: 375 + 'px', height: 500 + 'px'});
			
			if (w > h) {
				scale = 375 / w;
			} else {
				scale = 500 / h;
			}
			
			img.style.width  = w * scale;
			img.style.height = h * scale;
		}
		
		var width  = w * scale,
			height = h * scale;

		$('#logo-img')
        .css({width: width + 'px', height: height + 'px'});
		$('#edit-box-big')
        .css({width: width + 'px', height: height + 'px'});
		
        if ((170 / 50 - width / height) > 0) {
			var w = width * (50 / height);
			var x = (170 - w) / 2;
			$('#logo-preview').css({
				'width': w + 'px',
				'height': 'auto',
				'margin': '0 ' + x + 'px'
			});
		} else if ((170 / 50 - width / height) < 0) {
			var h = height * (170 / width);
			var y = (50 - h) / 2;
			$('#logo-preview').css({
				'width': 'auto',
				'height': h + 'px',
				'margin': y + 'px 0'
			});
		}
		
		if (Org.Logo._jcrop) {
			Org.Logo._jcrop.destroy();
		}
		
		Org.Logo._jcrop = $.Jcrop('#logo-img', {
			onChang: _sizeChange,
			onSelect: _sizeChange
		});
		
		$('#logo-img').unbind('load');
		
		function _sizeChange(coords) {
			var form = $('#logoform');
			if (parseInt(coords.w) > 0)
			{
				var rx = 170 / coords.w;
				var ry = 50 / coords.h;
				var src_width = $('#logo-img').width();
				var src_height = $('#logo-img').height();
				
				if ((170 / 50 - coords.w / coords.h) > 0) {
					var h = Math.round(50/coords.h*src_height);
					$('#logo-preview').css({
						width: 'auto',
						height: h + 'px',
						margin: '0'
					});
					
					var logow = $('#logo-preview').width();
					var w = Math.round(coords.w*logow/src_width);
					$('#logo-preview').parent().css({
						width: w + 'px',
						height: '50px',
						margin: '0 ' + Math.round((170 - w) / 2) + 'px'
					});
					
					$('#logo-preview').css({
						marginLeft: '-' + Math.round((logow / src_width) * coords.x) + 'px',
						marginTop: '-' + Math.round((h / src_height) * coords.y) + 'px'
					});
				} else if ((170 / 50 - coords.w / coords.h) < 0) {
					var h = Math.round(coords.h * (170 / coords.w ));
					var w = Math.round(rx * src_width);
					$('#logo-preview').css({
						width: w + 'px',
						height: 'auto',
						margin: '0',
						marginLeft: '-' + Math.round(rx * coords.x) + 'px'
					});
					
					var logoh = $('#logo-preview').height();
					var top = Math.round((logoh / src_height) * coords.y);
					$('#logo-preview').css({
						marginTop: '-' + top + 'px'
					});
					var y = Math.round((50 - h) / 2);
					$('#logo-preview').parent().css({
						width: '170px',
						height: h + 'px',
						margin: y + 'px 0'
					});
				}
				
				form.find('input[name="x"]').val(Math.round(coords.x / scale));
				form.find('input[name="y"]').val(Math.round(coords.y / scale));
				form.find('input[name="width"]').val(Math.round(coords.w / scale));
				form.find('input[name="height"]').val(Math.round(coords.h / scale));
			} else {
				form.find('input[name!="hash"]').val('');
				var width = $('#logo-img').width(),
					height = $('#logo-img').height();
				
				$('#logo-preview').parent().css({
                    width: 'auto',
                    height: 'auto',
                    margin: '0'
                });
				
				if ((170 / 50 - width / height) > 0) {
					var w = width * (50 / height);
					var x = (170 - w) / 2;
					$('#logo-preview').css({
						'width': w + 'px',
						'height': 'auto',
						'margin': '0 ' + x + 'px'
					});
				} else if ((170 / 50 - width / height) < 0) {
					var h = height * (170 / width);
					var y = (50 - h) / 2;
					$('#logo-preview').css({
						'width': 'auto',
						'height': h + 'px',
						'margin': y + 'px 0'
					});
				}
			}
		}
	},
	
	revert: function() {
		if (!confirm('确定要删除当前Logo并还原为默认Logo？')) {
			return ;
		}
		
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: BASE_PATH + '/org/info/logo-delete',
			success: function(ret) {
				Message.show(ret.message, 5000, ret.success);
				location.reload();
			},
			error: function(res) {}
		});
	}
};