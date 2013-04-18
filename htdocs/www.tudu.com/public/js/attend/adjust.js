var Attend = Attend || {};

var TOP = typeof(getTop) == 'function' ? getTop() : parent;

Attend.Adjust = {
	
	/**
	 * 排版方案列表下拉菜单
	 * 
	 */
	_scheduleMenu: null,
	
	/**
	 * 排班方案列表
	 */
	_schedules: {},
	
	_lang: {
		missing_user_select: '没有选择关联的用户',
		confirm_delete_adjust: '确认删除该调整记录？',
		params_invalid_adjust_subject: '请输入正确的调整主题'
	},

	/**
     * 设置语言
     * @param {Object} lang
     */
    setLang: function(lang) {
        var _o = this;
        for (var i in lang) {
            _o._lang[i] = lang[i];
        }
    },

	deptIds: null,

    setDepts: function(ids) {
        if (!ids || ids.length <= 0) {
            return ;
        }

        this.deptIds = ids;
    },
	
	/**
	 * 初始化页面
	 */
	init: function(role) {
		var me = this;
		// 保存
		$('#save, #save-leave').bind('click', function() {
			me.save(this.id == 'save');
		});
		
		// 取消
		$('#cancel').bind('click', function() {
			history.go(-1);
		});
		
		// 添加用户
		$('#add-user').bind('click', function() {
			me.userSelectWin(role);
		});
		
		// 移除列表用户
		$('#user-box').bind('click', function(e) {
			var src = e.srcElement ? $(e.srcElement) : $(e.target);
			
			var item = src.closest('p');
			
			item.remove();
		}).bind('mouseover mouseout', function(e) {
			var src = e.srcElement ? $(e.srcElement) : $(e.target);
			src = src.closest('p');
			
			if (e.type == 'mouseover') {
				src.addClass('over');
			} else {
				src.removeClass('over');
			}
		});
		
		
		
		this.initCalander();
	},
	
	initList: function() {
		$('#adjust-list tr').mousemove(function(){
            $(this).addClass("current");
        }).mouseout(function(){
            $(this).removeClass("current");
        });
	},
	
	 /**
	 * 设置用户列表
	 */
	setUsers: function(ids) {
		if (!ids || ids.length <= 0) {
			return ;
		}
		
		var userbox = $('#user-box');
		TOP.Cast.load(function() {
			var users = TOP.Cast.get('users');
			for (var i = 0, l = users.length; i < l; i++) {
				if (TOP.Util.inArray(users[i].uniqueid, ids)) {
					userbox.append('<p><input type="hidden" name="user[]" value="'+users[i].username+'" />'+users[i].truename+'<span class="gray">&lt;'+users[i].username+'&gt;</span></p>');
				}
			}
		});
	},
	
	/**
	 * 保存设置
	 */
	save: function(stay) {
		var form = $('#theform');
		var data = form.serializeArray(),
		    subject = $('input[name="subject"]').val().replace(/\s+/, '');
		
		if (!subject) {
			return TOP.showMessage(this._lang.params_invalid_adjust_subject);
		}
		
		if (!$('input[name="user[]"]').size()) {
			return TOP.showMessage(this._lang.missing_user_select);
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: data,
			url: form.attr('action'),
			success: function(ret) {
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				if (ret.success) {
					location.assign('/app/attend/schedule/adjust');
				}
			},
			error: function(res) {
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	deleteAdjust: function(id) {
		if (!confirm(this._lang.confirm_delete_adjust)) {
			return ;
		}
		
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {adjustid: id},
			url: '/app/attend/schedule/deleteadjust',
			success: function(ret) {
				TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
				if (ret.success) {
					location.assign(location.href);
				}
			},
			error: function(res) {
				TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
			}
		});
	},
	
	initCalander: function() {
		$('#datepicker').datepick({
	        changeMonth: false,
	        changeYear: false,
	        rangeSelect: true, 
	        showOtherMonths: true,
	        selectOtherMonths: true,
	        rangeSeparator: '|',
	        width: 230,
	        firstDay: 0,
	        showDefault: false,
	        gotoCurrent: true,
	        onSelect: function(range){
				var arr = range.split('|');
				$('#starttime').val(arr[0]);
				$('#endtime').val(arr[1]);
	        }
	    });
	},

	/**
	 * 用户选择窗口
	 */
	userSelectWin: function(isGroup) {
		if (!isGroup) {
            isGroup = false;
        }

		var html = '<div class="pop pop_linkman"><div class="pop_header"><strong>'+TOP.TEXT.SELECT_CONTACT+'</strong><a class="icon icon_close close"></a></div><div class="pop_body"></div><div class="pop_footer"><button type="button" name="confirm" class="btn">'+TOP.TEXT.CONFIRM+'</button><button type="button" class="btn close">'+TOP.TEXT.CANCEL+'</button></div></div>';
		var Win = TOP.Frame.TempWindow;
		var userbox = $('#user-box');
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
        
        var selected = [];
        $('input:hidden[name="user[]"]').each(function(){
        	selected.push({_id: this.value});
        });
        
        var selector = new TOP.ContactSelector({appendTo: Win.find('div.pop_body'), enableGroup: isGroup, selected: selected, panels: ['common'], childOf: this.deptIds});
        
        Win.show();
        
        Win.find('button[name="confirm"]').bind('click', function() {
        	var selected = selector.getSelected();
        	for (var i = 0, l = selected.length; i < l; i++) {
        		var se = selected[i];
        		var id = se.groupid ? se.groupid : se.email,
				    email = se.groupid ? TOP.TEXT.GROUP : se.email;
        		userbox.append('<p><input type="hidden" name="user[]" value="'+id+'" />'+se.name+'<span class="gray">&lt;'+email+'&gt;</span></p>');
        	}
        	Win.close();
        });
	}
};
