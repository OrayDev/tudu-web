var Netdisk = {
	
	windows: {
		fileRename: '<div class="pop pop_linkman"><form id="folderform" method="post" action="/netdisk/file.rename"><div class="pop_header"><strong>'+TOP.TEXT.RENAME+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"><input type="hidden" name="fileid" /><p>'+TOP.TEXT.ND_FILE_TIPS+'</p><p><input type="text" class="input_text" style="width:400px" maxlength="50" name="filename"></p></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></form></div>',
		folderRename: '<div class="pop pop_linkman"><form id="folderform" method="post" action=""><div class="pop_header"><strong>'+TOP.TEXT.RENAME+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"><input type="hidden" name="folderid" /><p>'+TOP.TEXT.ND_FOLDER_TIPS+'</p><p><input type="text" class="input_text" style="width:400px" maxlength="50" name="foldername"></p></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></form></div>',
		upload: '<div class="pop pop_linkman"><div class="pop_header"><strong>上传文件</strong><a class="icon icon_min hide"></a><a class="icon icon_close close"></a></div><div class="pop_body" style="padding:10px"><iframe id="nd-uploadframe" frameborder="0" style="width:100%;height:300px" scrolling="auto" src="javascript:\'\'"></iframe></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button></div></div>'
	},
	
	uploadUrl: null,
	
	folderId: null,
	
	/**
	 * 初始化网盘页面
	 */
	init: function() {
		$('button[name="delete"]').bind('click', function(){
			Netdisk.deleteFile();
		});
		
		$('input[name="checkall"]').click(function(){
			$(':checkbox[name="fileid[]"]:visible').attr('checked', this.checked);
			_checkDeleteEnabled();
		});
		
		$(':checkbox[name="fileid[]"]').click(function(){
			_checkDeleteEnabled();
		});
		
		$('button[name="folder"]').click(function(){
			Netdisk.create();
		});
		
		$('select[name="moveto"]').change(function(){
			Netdisk.moveFile(this.value);
			$(this).val('');
		});
		
		$('button[name="upload"]').click(function(){
			Netdisk.uploadFile();
		});
		
		function _checkDeleteEnabled() {
			if ($(':checkbox[name="fileid[]"]:checked').size()) {
				$('button[name="delete"]').attr('disabled', false);
			} else {
				$('button[name="delete"]').attr('disabled', true);
			}
		}
	},
	
	/**
	 * 调用上传对话框
	 */
	uploadFile: function() {
		TOP.NDUpload.showDialog(this.folderId);
	},
	
	/**
	 * 删除文件
	 */
	deleteFile: function(fileId) {
		if (!fileId) {
			fileId = this.getSelected().join(',');
		}
		
		if (!fileId) {
			TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
		}
		
		if (!confirm(TOP.TEXT.CONFIRM_DELETE_FILE)) {
			return ;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {fileid: fileId},
			url: '/netdisk/file.delete',
			success: function(ret) {
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				if (ret.success) {
					location.reload();
				}
			},
			error: function(res) {
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	/**
	 * 删除目录
	 */
	deleteFolder: function(folderId) {
		
		if (!folderId) {
			TOP.showMessage(TOP.TEXT.NOTHING_SELECTED);
		}
		
		if (!confirm(TOP.TEXT.CONFIRM_DELETE_FOLDER)) {
			return ;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {folderid: folderId},
			url: '/netdisk/folder.delete',
			success: function(ret) {
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				if (ret.success) {
					location.reload();
				}
			},
			error: function(res) {
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	/**
	 * 创建文件夹
	 */
	create: function() {
		var Win = TOP.Frame.TempWindow,
		_o = this;
		
		Win.append(_o.windows.folderRename, {
			width: 450,
			draggable: true,
			onClose: function() {
				Win.destroy();
			}
		});
		
		Win.find('.pop_header strong').text(TOP.TEXT.CREATE_FOLDER);
		Win.find('#folderform').attr('action', '/netdisk/folder.create');
		
		var form = Win.find('#folderform');
		form.submit(function(){return false;});
		form.submit(function(){
			var name = form.find(':text').val().replace(/^\s+|\s+$/, ''),
				input = form.find(':text');
			if (!name.length) {
				//Win.close();
				return TOP.showMessage(TOP.TEXT.ND_FOLDER_TIPS);
			}
			
			var data = form.serializeArray();
			input.attr('disabled', true);
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: form.attr('action'),
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					
					if (ret.success) {
						Win.close();
						location.reload();
					} else {
						input.attr('disabled', false);
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR, 5000);
					input.attr('disabled', false);
				}
			});
		});
		
		Win.show();
	},
	
	/**
	 * 重命名弹出框
	 */
	rename: function(type, id) {
		var Win = TOP.Frame.TempWindow,
			_o = this, html;
		
		if (type == 'file') {
			html = _o.windows.fileRename;
		} else {
			html = _o.windows.folderRename;
		}
		
		Win.append(html, {
			width: 450,
			draggable: true,
			onClose: function() {
				Win.destroy();
			}
		});
		
		if (type != 'file') {
			Win.find('#folderform').attr('action', '/netdisk/folder.rename');
		}
		Win.find(':hidden[name="folderid"],:hidden[name="fileid"]').val(id);
		
		var form = Win.find('form');
		form.submit(function(){return false;});
		form.submit(function(){
			var name = form.find(':text').val().replace(/^\s+|\s+$/, ''),
				input = form.find(':text');
			if (!name.length) {
				//Win.close();
				if (type == 'file') {
					return TOP.showMessage(TOP.TEXT.ND_FILE_TIPS);
				} else {
					return TOP.showMessage(TOP.TEXT.ND_FOLDER_TIPS);
				}
			}
			
			var data = form.serializeArray();
			input.attr('disabled', true);
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: form.attr('action'),
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					
					if (ret.success) {
						Win.close();
						location.reload();
					} else {
						input.attr('disabled', false);
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR, 5000);
					input.attr('disabled', false);
				}
			});
		});
		
		Win.show();
	},
	
	moveFile: function(folderid) {
		var fileid = this.getSelected();
		
		if (!folderid) {
			return ;
		}
		
		if (!fileid.length) {
			TOP.showMessage(TOP.TEXT.NOTHING_SELECTED, 5000);
			return ;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {fileid: fileid.join(','), folderid: folderid},
			url: '/netdisk/file.move',
			success: function(ret) {
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				if (ret.success) {
					location.reload();
				}
			},
			error: function(res) {
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	/**
	 * 共享文件
	 */
	shareFile: function(fileid, isshare) {
		var me = this;
		if (!fileid) {
			return ;
		}
		if (isshare) {
			me.showSelector('file', fileid, isshare, 'create');
		} else {
			if (!confirm(TOP.TEXT.CONFIRM_CANCEL_SHARE_TIPS)) {
				return ;
			}
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {objid: fileid, isshare: isshare},
				url: '/netdisk/file.share',
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					if (ret.success) {
						location.reload();
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				}
			});
		}
	},
	
	/**
	 * 共享文件夹
	 */
	shareFolder: function(folderid, isshare) {
		var me = this;
		if (!folderid) {
			return ;
		}
		if (isshare) {
			me.showSelector('folder', folderid, isshare, 'create');
		} else {
			if (!confirm(TOP.TEXT.CONFIRM_CANCEL_SHARE_TIPS)) {
				return ;
			}
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {objid: folderid, isshare: isshare},
				url: '/netdisk/folder.share',
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					if (ret.success) {
						location.reload();
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				}
			});
		}
	},
	
	/**
	 * 编辑共享人群
	 */
	shareMember: function(objid, objType) {
		var me = this;
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {objid: objid, objtype: objType},
			url: '/netdisk/get.member',
			success: function(ret) {
				if (ret.success) {
					var data = ret.data,
						selected = [];
					if (data) {
						for (var i = 0; i < data.length; i++) {
							selected.push({_id: data[i].targetid});
						}
					}
					me.showSelector(objType, objid, 1, 'update', selected);
				}
			},
			error: function(res) {}
		});
	},
	
	/**
	 * 显示联系人框
	 */
	showSelector: function(objtype, objid, isshare, action, selected) {
		var html = '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.SELECT_CONTACT+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>';
        
        var Win = TOP.Frame.TempWindow;
        Win.append(html, {
        	width:470,
        	draggalbe: true,
        	onShow: function() {
				Win.center();
			},
			onClose: function() {
				Win.destroy();
			}
        });
        
        var selector = new TOP.ContactSelector({appendTo: Win.find('div.pop_body'), enableGroup: true, selected: selected, panels:['common']});
		var panel = TOP.Cookie.get('CONTACT-PANEL');
		if (!panel) {
			panel = 'lastcontact';
		}
		selector.switchPanel(panel);
		
		Win.find('button[name="confirm"]').bind('click', function(){
			var se = selector.getSelected(),
				targetid = [];
			for (var i = 0, c = se.length; i < c; i++) {
				if (se[i].groupid) {
					targetid.push(se[i].groupid);
				} else {
					targetid.push(se[i].email);
				}
			}
			if (action == 'create') {
				var url = '/netdisk/' + objtype + '.share';
			} else if (action == 'update') {
				var url = '/netdisk/update.member';
			} else {
				return ;
			}
			
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: {objid: objid, objtype: objtype, isshare: isshare, targetid: targetid},
				url: url,
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					if (ret.success) {
						location.reload();
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
				}
			});
			
			Win.close();
		});
        
        Win.show();
	},
	
	tpl: {
		savetonetdisk : '<div class="pop pop_linkman"><form id="ndform" method="post" action="/netdisk/to-netdisk"><div class="pop_header"><strong>'+TOP.TEXT.SAVE_TO+'</strong><a class="icon icon_close close"></a></div><div class="pop_body" style="padding: 5px;"><input type="hidden" name="fileid" /><input type="hidden" name="ownerid" /><input type="hidden" name="isattach" /><input type="hidden" name="folderid" /><p class="gray">'+TOP.TEXT.PLEASE_SAVE_TO+':</p><div class="netdisk_panel"></div></div><div class="pop_footer"><button type="submit" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></form></div>'
	},
	
	ndTree : null,
	
	/**
	 * 保存到网盘
	 */
	saveToNd: function(fileId, ownerId, isAttach) {
		var win = TOP.Frame.TempWindow,
			me = this;
		
		win.append(me.tpl.savetonetdisk, {
			width: 500,
			draggable: true,
			onClose: function() {
				win.destroy();
			}
		});
		
		win.find('input[name="fileid"]').val(fileId);
		win.find('input[name="ownerid"]').val(ownerId);
		win.find('input[name="isattach"]').val(isAttach);
		var panel = win.find('.netdisk_panel');
		$.ajax({
			type: 'GET',
			dataType: 'json',
			url: '/netdisk/list',
			success: function(ret) {
				if (ret.success && ret.data) {
					var folders = ret.data.folders;
					
					var _$ = TOP.getJQ();
					
					me.ndTree = new _$.tree({
						id: 'netdisk-tree',
						idKey: 'id',
						idPrefix: 'nd-',
						cls: 'netdisk-tree'
					});
					
					var parent = new _$.treenode({
						data: {
							id: 'fo-_root',
							name: TOP.TEXT.NETDISK
						},
						content: '<span class="icon ficon folder"></span><span class="nd_foldername" name="folderid[]" _folderid="^root">{name}</span>',
						events: {
							click: function(e){
								TOP.stopEventBuddle(e);
							}
						}
					});
					me.ndTree.appendNode(parent);
					for (var folderid in folders) {
						if (folderid == '^root') {
							continue ;
						}
						var node = new _$.treenode({
							data: {
								id: 'fo-' + folderid.replace('^', '_'),
								name: folders[folderid].foldername
							},
							content: '<span class="icon ficon folder"></span><span class="nd_foldername" name="folderid[]" _folderid="'+folderid+'">{name}</span>',
							events: {
								click: function(e){
									TOP.stopEventBuddle(e);
								}
							}
						});
						
						parent = me.ndTree.find('fo-_root', true);
	
		                if (parent) {
		                    parent.appendChild(node);
		                }
					}
					me.ndTree.find('fo-_root', true).expand();
					
					me.ndTree.appendTo(panel);
					
					panel.find('span[name="folderid[]"]').bind('click', function(e){
						panel.find('div.tree-node-el').removeClass('tree-node-selected');
						if ($(this).parent().hasClass('tree-node-selected')) {
							$(this).parent().removeClass('tree-node-selected');
						} else {
							$(this).parent().addClass('tree-node-selected');
						}
						win.find('input[name="folderid"]').val($(this).attr('_folderid'));
						TOP.stopEventBuddle(e);
					});
				}
			},
			error: function(res) {}
		});
		
		var form = win.find('#ndform');
		form.submit(function(){return false;});
		form.submit(function(){
			var folderId = form.find('input[name="folderid"]').val();
			if (!folderId) {
				return TOP.showMessage(TOP.TEXT.SAVE_PATH_NULL, 5000);
			}
			var data = form.serializeArray();
			$.ajax({
				type: 'POST',
				dataType: 'json',
				data: data,
				url: form.attr('action'),
				success: function(ret) {
					TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
					
					if (ret.success) {
						win.close();
					}
				},
				error: function(res) {
					TOP.showMessage(TOP.TEXT.PROCESSING_ERROR, 5000);
				}
			});
		});
		
		win.show();
	},
	
	getSelected: function() {
		var a = [];
		$(':checkbox[name="fileid[]"]:checked').each(function(){
			a.push(this.value);
		});
		
		return a;
	}
};