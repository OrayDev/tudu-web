if (typeof(getTop) != 'function') {
	function getTop() {
	    return parent;
	}
}

var TOP = TOP || getTop();

/**
 * 用户名片
 */
var Card = function() {
	this.init();
};
Card.prototype = {
	// 模板
	ele: null,
	tpl: ['<div class="user_info" style="position:absolute;">',
	     '<table width="100%" cellspacing="0" cellpadding="0" border="0" class="todo_body">',
	     '<tr><td><div class="todo_bd_tl"><div class="todo_bd_tr"><div class="todo_bd_tc"></div></div></div></td></tr>',
	     '<tr><td><div class="todo_body_con">',
	     '<table border="0" cellpadding="0" cellspacing="0"><tr>',
	     '<td width="35" valign="top"><div class="user_pic"><img name="avatar" src="/logo?unid=&rnd=' + parseInt(Math.random()*99999) + '" width="80" height="80"></div></td>',
	     '<td valign="top"><p><strong class="f14"><span name="truename"></span></strong></p>',
	     '<p class="gray"><span name="deptname"></span>&nbsp;&nbsp;<span name="position"></span></p>',
		 '<p class="gray">'+TOP.TEXT.MOBILE+'：<span name="mobile"></span></p>',
		 '<p class="gray">'+TOP.TEXT.TEL+'：<span name="tel"></span></p></td></tr></table>',
		 '<div id="link" class="link"><table width="100%" border="0" cellpadding="0" cellspacing="0">',
		 '<tr><td><a name="sendmsg" href="javascript:void(0)"><span class="icon icon_sendmsg"></span>&nbsp;'+TOP.TEXT.SEND_MSG+'</a></td>',
		 '<td><a name="sendtudu" href="#"><span class="icon icon_sendtudu"></span>&nbsp;'+TOP.TEXT.SEND_TUDU+'</a></td>',
		 '<td><a name="info" href="#" class="last"><span class="icon icon_userinfo"></span>&nbsp;'+TOP.TEXT.USER_CARD_INFO+'</a></td>',
		 '</tr></table></div></div></td></tr>',
		 '<tr><td><div class="todo_bd_bl"><div class="todo_bd_br"><div class="todo_bd_bc"></div></div></div></td></tr>',
	     '</table></div>'].join(''),

	// 用于控制鼠标移除对象后隐藏名片
	mouseOutTimer: null,

	// 用于记录用户信息
	userInfo: {},

	// 是否正在显示
	isShow: null,

	// 对象 
	obj: null,

	// 用于控制多少秒后显示用户名片
	timer: null,

	// 初始化
	init: function() {
	},

	// 显示
	show: function(obj, sec) {
		var me = this,
			email = $(obj).attr('_email'),
			userId = (email != '^system') ? email.split('@')[0] : email;

	    if (!userId) {
	    	return ;
	    }

		me.obj = obj;

		if (null === this.ele) {
	        me.ele = $(this.tpl);
	        me.ele.appendTo(document.body).hide();

	        me.ele.mouseover(function() {
	    		clearTimeout(me.mouseOutTimer);
		    }).mouseout(function(e) {
		    	if (me.isMouseLeaveOrEnter(e, this)) {
		    		me.ele.hide();
		    		me.clearCard();
		    		me.isShow = false;
		    	}
		    });
	    }

		var p = me.getAbsolutePosition(obj),
			left = p.x,
			top = p.y;

		var width = me.ele.width(),
	    	bodyWidth = $(window).width(),
	    	bodyHeight = $(window).height(),
	    	oH     = $(obj).outerHeight(),
			mH     = me.ele.height(),
			sTop   = document.body.scrollTop ? document.body.scrollTop : document.documentElement.scrollTop,
	    	pos = {
	        	left : left ? left : 0,
	        	top : top ? top + oH : 0
	    	};

	    if ($('#float-toolbar').size()) {
	        var fH = $('#float-toolbar').outerHeight();
	        if (fH && typeof fH != 'undefined') {
	        	sTop = sTop + fH;
	        }
	    }

	    if (top + oH + mH - sTop > bodyHeight) {
	    	pos.top = pos.top - mH - oH;
	    }

		me.timer = setTimeout(function(){
			if (userId == '^system' || userId == 'robot') {
				me.systemCard();
			} else if (!me.userInfo[userId]) {
		    	me.getUserInfo(userId);
		    } else if (me.userInfo[userId].isnull) {
		    	me.nullCard();
		    } else {
		    	me.updateCard(me.userInfo[userId]);
		    }
			me.ele.css({
		        left : pos.left + 'px',
		        top : pos.top + 'px'
		    });
			me.ele.show();
			me.isShow = true;
			clearTimeout(me.timer);
		}, sec);
	},

	// 隐藏
	hide: function() {
		var me = this;
		if (me.isShow) {
			me.mouseOutTimer = setTimeout(function(){
				me.ele.hide();
				me.clearCard();
			}, 100);
		}

		if (me.timer) {
			clearTimeout(me.timer);
			me.timer = null;
		}

		me.isShow = false;
	},

	isMouseLeaveOrEnter: function(e, handler) {   
	    if (e.type != 'mouseout' && e.type != 'mouseover') return false;   
	    var reltg = e.relatedTarget ? e.relatedTarget : e.type == 'mouseout' ? e.toElement : e.fromElement;   
	    while (reltg && reltg != handler)   
	        reltg = reltg.parentNode;   
	    return (reltg != handler);   
	},

	//获取对象的绝对位置
	getAbsolutePosition: function(o){
		var p = {x: o.offsetLeft, y: o.offsetTop};
		while(o = o.offsetParent){
			p.x += o.offsetLeft;
			p.y += o.offsetTop;
		}

		return p;
	},

	/**
	 * 获取用户信息
	 */
	getUserInfo: function(userId) {
		var me = this;
		$.ajax({
            type: 'GET',
            dataType: 'json',
            url: '/tudu/user-card?userid=' + userId,
            success: function(ret) {
                if (ret.success) {
                    if (ret.data) {
                    	me.userInfo[userId] = ret.data;
                    	me.updateCard(ret.data);
                    } else {
                    	me.userInfo[userId] = {isnull: true};
                    	me.nullCard();
                    }
                }
            },
            error: function(res) {
            }
        });
	},
	
	//聊天
	chat: function(email) {
		var T = getTop();
		T.TuduTalk.talk(email, function(){
			if (!$.browser.msie) {
				TOP.TuduTalk.openTalk('tdim://chat?jid=' + email);
				return ;
			}
			
			var T = getTop();
			var d = T.dialog({
				title: T.TEXT.HINT,
				body: '<p>' + T.TEXT.TALK_HINT + '</p>',
				buttons: [
	                {
	                    text: T.TEXT.INSTALL_NOW,
	                    cls: 'btn',
	                    events: {click: function(){
	                        window.open(TOP.TALK_URL);
	                    }}
	                },
	                {
	                    text: T.TEXT.DONOT_INSTALL,
	                    cls: 'btn',
	                    events: {click: function(){
	                        d.close();
	                    }}
	                }
	            ]
			});
		});
	},

	// 清除名片信息
	clearCard: function() {
		this.ele.find('span[name="truename"], span[name="deptname"], span[name="position"], span[name="tel"], span[name="mobile"]').text('');
		this.ele.find('img[name="avatar"]').attr('src', '/logo?unid=&rnd=' + parseInt(Math.random()*99999));
		this.ele.find('a[name="sendmsg"]').attr('href', 'javascript:void(0)');
    	this.ele.find('a[name="sendmsg"]').unbind('click');
    	this.ele.find('a[name="info"], a[name="sendtudu"]').attr('href', '');
    	this.ele.find('div.link').removeClass('link_disable');
	},

	/**
	 * 更新用户卡片信息
	 */
	updateCard: function(data) {
		var me = this;
		this.ele.find('span[name="truename"]').text(data.truename);
		this.ele.find('span[name="deptname"]').text(data.deptname);
		this.ele.find('span[name="position"]').text(data.position);
    	if (!data.tel) {
    		data.tel = '-';
    	}
    	this.ele.find('span[name="tel"]').text(data.tel);
    	if (!data.mobile) {
    		data.mobile = '-';
    	}
    	this.ele.find('span[name="mobile"]').text(data.mobile);
    	this.ele.find('img[name="avatar"]').attr('src', '/logo?unid=' + data.uniqueid);
    	this.ele.find('a[name="sendmsg"]').bind('click', function(){
    		me.chat(data.userid+'@'+data.orgid);
    	});
    	this.ele.find('a[name="info"]').attr('href', $(this.obj).attr('_url') ? $(this.obj).attr('_url') : $(this.obj).attr('href'));
    	this.ele.find('a[name="sendtudu"]').attr('href', '/tudu/modify/?to=' + data.userid+'@'+data.domainname);
	},

	/**
	 * 图度系统用户名片
	 */
	systemCard: function() {
		this.ele.find('span[name="truename"]').text($(this.obj).text() ? $(this.obj).text() : $(this.obj).attr('_name'));
		this.ele.find('img[name="avatar"]').attr('src', '/logo?unid=^system');
		this.ele.find('span[name="tel"]').text('-');
    	this.ele.find('span[name="mobile"]').text('-');
    	this.ele.find('a[name="sendmsg"], a[name="info"], a[name="sendtudu"]').removeAttr('href');
		this.ele.find('div.link').addClass('link_disable');
	},
	
	/**
	 * 用户不存在提示
	 */
	nullCard: function() {
		this.ele.find('span[name="truename"]').text(TOP.TEXT.USER_ISNULL);
		this.ele.find('span[name="tel"]').text('-');
    	this.ele.find('span[name="mobile"]').text('-');
    	this.ele.find('img[name="avatar"]').attr('src', '/logo?unid=&rnd=' + parseInt(Math.random()*99999));
    	this.ele.find('a[name="sendmsg"], a[name="info"], a[name="sendtudu"]').removeAttr('href');
		this.ele.find('div.link').addClass('link_disable');
	}
};