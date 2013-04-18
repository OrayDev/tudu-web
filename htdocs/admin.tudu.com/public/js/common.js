// jQuery.window
(function(A){A.window=function(D){var C=this;this.opts=A.extend({},A.window.defaults,D);this.fixIE=(A.browser.msie&&(A.browser.version<7));this.id=Math.random();if(!this.opts.applyTo){return}var B=A(this.opts.applyTo).css({zIndex:8001});this.dialog={container:B,header:B.find(".window-header"),body:B.find(".window-body"),footer:B.find(".window-footer")};this.bh=A("#fix-height");if(!this.bh.size()){this.bh=A("<div>").attr("id","fix-height").css({position:"absolute",height:"100%",width:1,left:-100,top:0}).appendTo(document.body)}this.fn={show:this.opts.show,close:this.opts.close};if(this.fixIE){this.iframe=A('<iframe src="javascript:false;">').css({opacity:0,position:"absolute",zIndex:1000,width:"100%",top:0,left:0}).hide().appendTo("body")}if(this.opts.draggable&&A.fn.drag){this.dialog.container.drag(this.opts.drag)}this.mask=A('<div class="mask">').css({zIndex:8000}).appendTo(document.body).hide();this.dialog.container.hide().appendTo(document.body);this.dialog.container.find("."+this.opts.closeCls).click(function(E){E.preventDefault();C.close()});this.dialog.container.css({height:this.opts.height,width:this.opts.width});return this};A.window.prototype={show:function(){if(this.iframe){this.iframe.show()}this.mask.show();this.resizeMask();this.dialog.container.show();this.center();if(A.isFunction(this.fn.show)){this.fn.show.apply(this,[this.dialog])}this.bindEvents()},close:function(){if(this.iframe){this.iframe.hide()}this.mask.hide();this.dialog.container.hide();if(A.isFunction(this.fn.close)){this.fn.close.apply(this,[this.dialog])}this.unbindEvents()},resizeMask:function(){if(this.fixIE){var B=Math.max(document.body.offsetHeight,this.bh.height());var C=document.body.offsetWidth;this.mask.css({height:B,width:C});if(this.iframe){this.iframe.css({height:B,width:C})}}else{this.mask.css("position","fixed")}},center:function(){var B=(document.body.offsetWidth-A(this.dialog.container).width())/2;var C=0;if(this.dialog.container.height()<Math.min(this.bh.height(),document.documentElement.offsetHeight)){C=((Math.min(this.bh.height(),document.documentElement.offsetHeight)-this.dialog.container.height())/2+Math.max(document.documentElement.scrollTop,document.body.scrollTop))}this.dialog.container.css({left:B,top:C})},bindEvents:function(){var B=this;if(this.fixIE){A(window).bind("resize",function(){B.resizeMask()})}},unbindEvents:function(){var B=this;if(this.fixIE){A(window).unbind("resize",function(){B.resizeMask()})}},destroy:function(){this.mask.remove();this.dialog.container.remove();return null}};A.fn.window=function(B){if(!this.length){B&&B.debug&&C.console&&console.warn("nothing selected, can't open window, returning nothing");return}var C=A.data(this[0],"window");if(C){return C}C=new A.window(A.extend(B,{applyTo:this[0],id:this[0].id}));A.data(this[0],"window",C);return C};A.window.defaults={id:"window",width:false,height:false,closeCls:"close",draggable:false,show:null,close:null}})(jQuery);
// jQuery.drag
(function(B){var A=B.fn.drag=function(E){if(E===false){return this.unbind("mousedown",D)}E=B.extend({not:":input",handle:".handle",distance:0,which:1,constrain:true,drag:function(G){B(this).css({top:G.offsetY,left:G.offsetX})}},E||{});E.distance=F(E.distance);this.bind("mousedown",{options:E},D);function D(L){var K=this,J,M=L.data||{},I=M.options||{};if(M.elem){K=M.elem;L.cursorOffsetX=M.pageX-M.left;L.cursorOffsetY=M.pageY-M.top;L.offsetX=L.pageX-L.cursorOffsetX;L.offsetY=L.pageY-L.cursorOffsetY}else{if(A.dragging||(I.which>0&&L.which!=I.which)||B(L.target).is(I.not)){return}}switch(L.type){case"mousedown":if(I.handle&&B(K).find(I.handle).size()&&!(B(L.target).is(I.handle)||B(L.target).parents(I.handle).size())){return}B.extend(M,B(K).offset(),{elem:K,target:L.target,pageX:L.pageX,pageY:L.pageY});B.event.add(document,"mousemove mouseup",D,M);C(K,false);return false;case !A.dragging&&"mousemove":if(F(L.pageX-M.pageX)+F(L.pageY-M.pageY)<I.distance){break}L.target=M.target;if(I.dragstart&&I.dragstart.apply(K,[L])===false){break}A.dragging=true;case A.dragging&&"mousemove":if(I.dragging&&I.dragging.apply(K,[L])===false){break}if(I.constrain){if(L.offsetX<0){L.offsetX=0}if(L.offsetY<0){L.offsetY=0}var G=(document.body.scrollWidth-K.offsetWidth);var H=(document.body.offsetHeight-K.offsetHeight);if(L.offsetX>G){L.offsetX=G}if(document.body.offsetHeight>K.offsetHeight){if(L.offsetY>H){L.offsetY=H}}else{L.offsetY=0}}if(I.drag.apply(K,[L])!==false){break}L.type="mouseup";case"mouseup":B.event.remove(document,"mousemove mouseup",D);if(A.dragging&&I.dragend){I.dragend.apply(K,[L])}C(K,true);A.dragging=M.elem=null;break}}function F(G){return Math.pow(G,2)}function C(H,G){if(!H){return}H.unselectable=G?"off":"on";H.onselectstart=function(){return G};if(document.selection&&document.selection.empty){document.selection.empty()}if(H.style){H.style.MozUserSelect=G?"":"none"}}}})(jQuery);
//jQuery.slides
var Slides=function(A){this.init()};Slides.options={container:"slides_container",next:"next",prev:"prev",pagination:true,paginationClass:"pagination",currentClass:"current",fadeSpeed:350,fadeEasing:"",slideSpeed:350,slideEasing:"",start:0,effect:"slide",play:0,pause:0,hoverPause:false,animationStart:function(){},animationComplete:function(){},slidesLoaded:function(){},isArrow:false,arrowId:"#arrow"};Slides.prototype={settings:null,container:null,playInterval:null,pauseTimeout:null,current:0,active:false,init:function(){},_initContainer:function(){var D=this,C=$(this.settings.target);$("."+this.settings.container,C).children().wrapAll('<div class="slides_control"/>');this.container=$(".slides_control",C);var B=this.container.children().outerWidth(),A=this.container.children().outerHeight();$("."+this.settings.container,C).css({overflow:"hidden",position:"relative"});this.container.children().css({position:"absolute",top:0,left:this.container.children().outerWidth(),zIndex:0,display:"none"});this.container.css({position:"relative",width:(B*3),height:A,left:-B});$("."+this.settings.container,C).css({display:"block"});this.container.children(":eq("+this.settings.start+")").fadeIn(this.settings.fadeSpeed,this.settings.fadeEasing);this.container.bind("mouseover",function(){if(D.settings.hoverPause){D.stop()}if(D.settings.isArrow){$(D.settings.arrowId).show()}});this.container.bind("mouseleave",function(){if(D.settings.hoverPause){D.pause()}if(D.settings.isArrow){$(D.settings.arrowId).hide()}})},_initPagination:function(){var B=this;$(this.settings.target).parent().append("<div class="+this.settings.paginationClass+"></div>");var A=0;this.container.children().each(function(){$("."+B.settings.paginationClass).append('<a href="javascript:void(0)" _index="'+A+'"></a>');A++});$("."+this.settings.paginationClass+" a:eq("+this.settings.start+")").addClass(this.settings.currentClass);this._initPaginationEvent()},_initPaginationEvent:function(){var B=this,A=0;$("."+this.settings.paginationClass+" a").bind("mouseover",function(){if(B.settings.play){B.stop()}A=$(this).attr("_index");if(B.current!=A){B.animate("pagination",B.settings.effect,A)}return false}).bind("mouseleave",function(){B.pause()})},_initArrow:function(){$(this.settings.arrowId).bind("mouseover",function(){$(this).show()}).bind("mouseout",function(){$(this).hide()});this._initArrowEvent()},_initArrowEvent:function(){var A=this;$("."+A.settings.next,$(this.settings.target)).click(function(B){B.preventDefault();if(A.settings.play){A.pause()}A.animate("next",A.settings.effect)});$("."+A.settings.prev,$(this.settings.target)).click(function(B){B.preventDefault();if(A.settings.play){A.pause()}A.animate("prev",A.settings.effect)})},_initSlide:function(){var A=this;this.playInterval=setInterval(function(){A.animate("next","slide")},A.settings.play);$(this.settings.target).data("interval",this.playInterval)},animate:function(H,J,F){if(!this.active){var B=this,C=0,D=0,E=0,G=this.container.children().size(),A=this.container.children().outerWidth(),I=this.container.children().outerHeight();this.active=true;if(this.settings.start>G){this.settings.start=G-1}switch(H){case"next":C=this.current;D=this.current+1;D=(G===D)?0:D;E=A*2;H=-A*2;this.current=D;break;case"prev":C=this.current;D=this.current-1;D=(D===-1)?G-1:D;E=0;H=0;this.current=D;break;case"pagination":D=parseInt(F,10);C=$("."+this.settings.paginationClass+" a."+this.settings.currentClass).attr("_index");if(D>C){E=A*2;H=-A*2}else{E=0;H=0}this.current=D;break}this.container.children(":eq("+D+")").css({left:E,display:"block"});this.container.animate({left:H},this.container.slideSpeed,this.container.slideEasing,function(){B.container.css({left:-A});B.container.children(":eq("+D+")").css({left:A,zIndex:5});B.container.children(":eq("+C+")").css({left:A,display:"none",zIndex:0});B.active=false});if(this.settings.pagination){$("."+this.settings.paginationClass+" a."+this.settings.currentClass).removeClass(this.settings.currentClass);$("."+this.settings.paginationClass+" a:eq("+D+")").addClass(this.settings.currentClass)}}},play:function(A){this.settings=$.extend(Slides.options,A);this._initContainer();if(this.settings.isArrow){this._initArrow()}if(this.settings.pagination){this._initPagination()}this.current=this.settings.start;if(this.settings.effect=="slide"){this._initSlide()}},stop:function(){clearInterval($(this.settings.target).data("interval"))},pause:function(){var A=this;clearTimeout($(A.settings.target).data("pause"));clearInterval($(A.settings.target).data("interval"));this.pauseTimeout=setTimeout(function(){clearTimeout($(A.settings.target).data("pause"));A.playInterval=setInterval(function(){A.animate("next",A.settings.effect)},A.settings.play);$(A.settings.target).data("interval",A.playInterval)},A.settings.pause);$(this.settings.target).data("pause",this.pauseTimeout)},destroy:function(){var A=this;clearTimeout($(A.settings.target).data("pause"));clearInterval($(A.settings.target).data("interval"))}};

/**
 * 后台公用程序
 */
var Admin = {
    
    _TPLS: {
        Window: '<div class="window-header"><div class="tool"><a title="关闭" class="close" href="javascript:void(0)"><em>关闭</em></a></div><div class="window-header-text"><strong></strong></div></div><div class="window-body"></div>'
    },
    
    /**
     * 弹出窗口
     */
    window: function(config) {
        var obj = config.id ? $('#' + config.id) : null,
            params = {}, init = null, tpl = Admin._TPLS.Window;
        
        if (null === obj || !obj.size()) {
            obj = $('<div class="window"></div>');
        }
        
        if (config.form !== false) {
            tpl = ['<form method="post" action="?">', tpl, '</form>'].join('');
        }
        
        obj.html(tpl);
        
        for (var k in config) {
            switch (k) {
                case 'id':
                    obj.attr('id', config[k]);
                    break;
                case 'formid':
                    obj.find('form').attr('id', config[k]);
                    break;
                case 'body':
                    obj.find('.window-body').html(config[k]);
                    break;
                case 'footer':
                    var footer = $('<div class="window-footer"></div>');
                    footer.html(config[k]);
                    if (config.form !== false) {
                        obj.find('form').append(footer);
                    } else {
                        obj.append(footer)
                    }
                    break;
                case 'title':
                    obj.find('.window-header-text strong').text(config[k]);
                    break;
                case 'action':
                    obj.find('form').attr('action', config[k]);
                    break;
                case 'width':
                    params.width = config[k];
                    break;
                case 'onShow':
                case 'show':
                    params.show = config[k];
                    break;
                case 'onClose':
                case 'close':
                    params.close = config[k];
                    break;
                case 'closeable':
                	if (false === config.closeable) {
                		obj.find('div.window-header a.close').remove();
                	}
                	break;
                case 'draggable':
                    params[k] = config[k];
                    params['drag'] = {
                        handle: '.window-header',
                        not: ':input,img,a',
                        distance: 5,
                        dragging: function(event){
                            if (event.offsetX < 0) event.offsetX = 0;
                            if (event.offsetY < 0) event.offsetY = 0;
                        }
                    };
                    break;
                case 'init':
                    init = config[k];
                    break;
            }
        }
        
        obj.css({position: 'absolute'}).appendTo(document.body);
        var win = obj.window(params);
        
        if (typeof (init) == 'function') {
            init.call(obj);
        }
        
        return win;
    },
    
    /**
     * 选择框全选择
     * 
     * @param name
     * @param ischeck
     * @param scope
     * @return
     */
    checkBoxAll: function(name, ischeck, scope) {
        if (scope) {
            $(':checkbox[name="'+name+'"]:visible', scope).attr('checked', ischeck);
        } else {
            $(':checkbox[name="'+name+'"]:visible').attr('checked', ischeck);
        }
    },
    
    /**
     * 更改皮肤
     */
    switchSkin: function(skin) {
        $('#css-skin').attr('href', SITES['static'] + '/css/skin_' + skin + '.css?' + Math.random());
    }
};

var Message = {
    
    PROCESSING_ERROR: '程序处理错误，请稍候重试',
    
    /**
     * @var {Object}
     */
    _timer: null,
    
    /**
     * 显示操作返回信息
     */
    show: function(content, timeout, cls) {
        var msgid = 'sys-msg',
            obj = $('#' + msgid);
        
        if (!obj.size()) {
            obj = $('<p>').addClass('msg');
            obj.attr('id', msgid).append('<span></span>');
        }
        
        if (!cls) {
            obj.find('span').addClass('error');
        } else {
            obj.find('span').removeClass('error');
        }
        
        if (!content) {
            obj.fadeOut();
        }
        
        obj.find('span').html(content);
        
        obj.hide().appendTo(document.body);
        
        obj.fadeIn();
        
        if (timeout !== 0) {
            if (!timeout) {
                timeout = 10000;
            }
            
            Message._timer = setTimeout(function(){
                obj.fadeOut('normal');
                clearTimeout(Message._timer);
            }, timeout);
        }
    }
};

/**
 * 单例ajax请求对象
 */
var _S_LOADERS = {};
var SingleLoader = function(params) {
    this._cfgs = params;
    this._callbacks = [];
};

SingleLoader.prototype = {

    /**
     * @type {Object}
     */
    _cfgs: null,
    
    /**
     * 
     */
    _callbacks: null,
    
    /**
     * 
     */
    _isLoading: false,
    
    /**
     * 
     */
    _loaded: false,
    
    /**
     * 
     */
    _data: null,
    
    /**
     * 
     */
    load: function(callback) {
        var me = this;
        
        if (this._isLoading) {
            if (typeof (callback) == 'function') {
                me._callbacks.push(callback);
                return ;
            }
        } else if (!me._data) {
            if (typeof (callback) == 'function') {
                me._callbacks.push(callback);
            }
        }
        
        if (!me._data) {
            this._isLoading = true;
            $.ajax({
                type: me._cfgs.type ? me._cfgs.type : 'POST',
                dataType: 'json',
                data: me._cfgs.data ? me._cfgs.data : {},
                url: me._cfgs.url ? me._cfgs.url : '?',
                success: function(ret) {
                    me._data = ret;
                    for (var i = 0, c = me._callbacks.length; i < c; i++) {
                        if (typeof me._callbacks[i] == 'function') {
                            me._callbacks[i].call(me, me._data);
                        }
                    }
                    me._isLoading = false;
                    me._callbacks = [];
                },
                error: function(res){me._isLoading = false;me._callbacks = [];}
            });
            
            return ;
        }
        
        if (me._callbacks.length) {
            for (var i = 0, c = me._callbacks.length; i < c; i++) {
                me._callbacks[i].call(me, me._data);
            }
            me._callbacks = [];
        } else if (typeof(callback) == 'function') {
            callback.call(me, me._data);
        }
    },
    
    clear: function() {
        this._data = null;
    }
};

SingleLoader.getLoader = function(key, params) {
    if (undefined === _S_LOADERS[key]) {
        _S_LOADERS[key] = new SingleLoader(params);
    }
    
    return _S_LOADERS[key];
};

/**
 * 用户选择里
 */
var UserSelector = function(appendTo, isGroup, jq, containGroup) {
    this._isGroup = isGroup !== false;
    this._jq = jq || jQuery;
    this._containGroup = containGroup || false;

    this.init();
    this.renderTo(appendTo);
};

UserSelector.tpl = '<div><div class="select-box"><div class="search-box"><input name="search-input" type="text" class="text" style="width:188px;"/><span class="icon icon-search"></span></div><div class="select-box-inner" _name="member-box"></div><div class="select-box-inner" _name="search-result"></div></div><div class="arrow-box"></div><div class="select-box" _name="select-box"></div><div class="clear"></div></div>';

UserSelector.prototype = {
    
    /**
     * 元素
     */
    _el: null,
    
    /**
     * 检索输入框
     */
    _searchInput: null,
    
    /**
     * 检索结果
     */
    _searchResult: null,
    
    /**
     * 选中框
     */
    _selectBox: null,
    
    /**
     * 待选框
     */
    _memberBox: null,
    
    /**
     * 用户树
     */
    _memberTree: null,
    
    /**
     * 
     */
    _loader: null,
    
    /**
     * 
     */
    _isGroup: null,
    
    /**
     * 
     */
    _containGroup: false,
    
    /**
     * 
     */
    _enable: true,
    
    /**
     * 
     */
    _jq: null,
    
    /**
     * 初始化
     */
    init: function() {
        var me = this;
        this._el = this._jq(UserSelector.tpl);
        
        this._searchInput  = this._el.find('input[name="search-input"]');
        this._memberBox    = this._el.find('div[_name="member-box"]');
        this._selectBox    = this._el.find('div[_name="select-box"]');
        this._searchResult = this._el.find('div[_name="search-result"]');

		this._searchResult.hide();
		
		var url = '/user/user/struct.load', loaderName = 'user';
		if (this._containGroup) {
			loaderName = 'group';
			url += '?group=1';
		}
		this._loader = SingleLoader.getLoader(loaderName, {
			//url: BASE_PATH + '/user/user/struct.load',
			url: BASE_PATH + url,
			method: 'GET'
		}), me = this;

		this._loader.clear();
		
		this.initTree();
		if (this._containGroup) {
			this.initGroup();
		}
		
		this._searchInput.bind('keyup', function(){
			me.search(this.value);
		});
	},
	
	initTree: function() {
		var me = this;
		this._memberTree = new this._jq.tree({
			id: 'cast-tree',
			idKey: 'id',
			idPrefix: 'cast-',
			cls: 'cast-tree',
			template: '{name}'
		});

		this._memberTree.appendTo(this._memberBox);
		
		this._loader.load(function(ret){
			if (ret.success && ret.data) {
				var depts = ret.data.depts,
					users = ret.data.users,
					orgname = ret.data.org.orgname ? ret.data.org.orgname : ret.data.org.orgid;

                if (me._isGroup) {
                    for (var i = 0, c = depts.length; i < c; i++) {
                        if (depts[i].deptid.indexOf('^') != -1) {
                            depts[i].deptname = orgname;
                        }
                        if (depts[i].deptid && depts[i].deptid.indexOf('^') != -1) {
                            depts[i].deptid = depts[i].deptid.replace('^', '_');
                        }
                        
                        var node = new me._jq.treenode({
                            data: {
                                id: 'd-' + depts[i].deptid,
                                name: depts[i].deptname
                            },
                            events: {
                                click: function(e){Util.stopEventBuddle(e);if (!me._enable) { return ;}me._memberTree.find(this.id.replace('cast-', ''), true).toggle();}
                            }
                        });
                        
                        var parentid = depts[i].parentid ? depts[i].parentid.replace('^', '_') : '_root';
                        var parent = me._memberTree.find('d-' + parentid, true);
                    
                        if (parent) {
                            parent.appendChild(node);
                        } else {
                            me._memberTree.appendNode(node);
                        }
                    }
                    
                    for (var i = 0, c = users.length; i < c; i++) {
                        var node = new me._jq.treenode({
                            data: {
                                id: 'u-' + users[i].userid,
                                userid: users[i].userid,
                                name: users[i].truename,
                                addr: users[i].username
                            },
                            isLeaf: true,
                            events: {
                                mouseover: function(){me._jq(this).addClass('tree-node-over');},
                                mouseout: function(){me._jq(this).removeClass('tree-node-over');},
                                click: function(e){
                                    Util.stopEventBuddle(e);
                                    if (!me._enable) { return ;}
                                    me.select(this.id.replace('cast-u-', ''));
                                }
                            }
                        });
                        
                        var deptid = users[i].deptid ? users[i].deptid : '_root';
                        var dept = me._memberTree.find('d-' + deptid, true);
                    
                        if (dept) {
                            dept.appendChild(node);
                        } else {
                            me._memberTree.appendNode(node);
                        }
                    }
                } else {
                    for (var i = 0, c = users.length; i < c; i++) {
                        var node = new me._jq.treenode({
                                data: {
                                id: 'u-' + users[i].userid,
                                userid: users[i].userid,
                                name: users[i].truename,
                                addr: users[i].username
                            },
                            isLeaf: true,
                            events: {
                                mouseover: function(){me._jq(this).addClass('tree-node-over');},
                                mouseout: function(){me._jq(this).removeClass('tree-node-over');},
                                click: function(e){
                                    Util.stopEventBuddle(e);
                                    if (!me._enable) { return ;}
                                    me.select(this.id.replace('cast-u-', ''));
                                }
                            }
                        });

                        me._memberTree.appendNode(node);
                    }
                }
            }
            
            var root = me._memberTree.find('d-_root', true);
            if (root != null) {
                root.expand()
            }
        });
    },
    
    enabled: function() {
        this._searchInput.attr('disabled', false);
        this._el.addClass('disabled');
        this._enable = true;
    },
    
    disabled: function() {
        this._searchInput.attr('disabled', true);
        this._el.addClass('disabled');
        this._enable = false;
    },
    
    /**
     * 初始化群组
     */
    initGroup: function() {
        var me = this;
        this._groupTree = new this._jq.tree({
            id: 'group-tree',
            idKey: 'id',
            idPrefix: 'group-',
            cls: 'cast-tree',
            template: '{groupname}'
        });
        
        var root = new this._jq.treenode({
            data: {
                id: 'group_root',
                groupname: '用户群组'
            },
            events: {
                click: function(e){me._groupTree.find(this.id.replace('group-', ''), true).toggle();Util.stopEventBuddle(e);}
            }
        })
        this._groupTree.appendNode(root);
        
        this._groupTree.appendTo(this._memberBox);
        
        this._loader.load(function(ret){
            if (ret.data) {
                var groups = ret.data.groups
                
                if (!groups) {
                    return ;
                }
                
                for (var i = 0, c = groups.length; i < c; i++) {
                    var node = new me._jq.treenode({
                        data: {
                            id: 'group_' + groups[i].groupid,
                            groupname: groups[i].groupname,
                            groupid: groups[i].groupid
                        },
                        isLeaf: true,
                        events: {
                            mouseover: function(){me._jq(this).addClass('tree-node-over');},
                            mouseout: function(){me._jq(this).removeClass('tree-node-over');},
                            click: function(e){
                                if (!me._enable) { return ;}
                                me.select(this.id.replace('group-', ''));
                                Util.stopEventBuddle(e);
                            }
                        }
                    });

					root.appendChild(node);
				}
			}
		});
	},
	
	/**
	 * 搜索
	 */
	search: function(keyword) {
		var me = this;
		if (!Util.trim(keyword)) {
			this._memberBox.show();
			return this._searchResult.hide();
		}
		
		keyword = Util.trim(keyword.toLowerCase());
		
		this._loader.load(function(ret){
			if (ret.success && ret.data) {
				var users = ret.data.users, matches = 0;
				me._searchResult.empty();
				for (var i = 0, c = users.length; i < c; i++) {
					var name = users[i].truename.toLowerCase(),
						addr = users[i].username.toLowerCase();
					
					if (name.indexOf(keyword) >= 0 || addr.indexOf(keyword) >= 0) {
						if (me._searchResult.find('#usr-' + users[i].userid).size()) {
							continue ;
						}
						
						var o = me._jq('<a href="javascript:void(0)" class="select-item" id="usr-'+users[i].userid+'">' + users[i].truename + '</a>');
						o.bind('click', function(){
							me.select(this.id.replace('usr-', ''));
						});
						
						me._searchResult.append(o);
						
						if (me._selectBox.find('a[_userid="'+users[i].userid+'"]').size()) {
							o.hide();
						}
						matches++;
					}
				}
				
				if (me._containGroup && ret.data.groups) {
					var groups = ret.data.groups;
					for (var i = 0, c = groups.length; i < c; i++) {
						var name = groups[i].groupname;
						
						if (name.indexOf(keyword) >= 0) {
							var o = me._jq('<a href="javascript:void(0)" class="select-item" id="usr-group_'+groups[i].groupid+'">' + groups[i].groupname + ' <span class="gray">&lt;群组&gt;</span></a>');
							o.bind('click', function(){
								me.select(this.id.replace('usr-', ''));
							});
							
							me._searchResult.append(o);
							
							if (me._selectBox.find('a[_userid="'+groups[i].groupid+'"]').size()) {
								o.hide();
							}
							matches++;
						}
					}
				}
				
				me._searchResult.show();
				me._memberBox.hide();
			}
		});
		
	},
	
	/**
	 * 选择人员
	 */
	select: function(ids) {
		var me = this;
		
		if (!ids || !ids.length) {
			return ;
		}
		
		if (!Util.isArray(ids)) {
			ids = [ids];
		}
		
		this._loader.load(function(){
			for (var i = 0, c = ids.length; i < c; i++) {
				var id = ids[i],
					isGroup = id.indexOf('group_') === 0;
				if (isGroup && me._containGroup) {
					var node = me._groupTree.find(id, true);
				} else {
					var node = me._memberTree.find('u-' + id, true);
				}
				
				if (node) {
					node.hide();
					
					var data = node.getData();
					
					if (!isGroup) {
						var o = me._jq('<a href="javascript:void(0)" class="select-item" _userid="'+ids[i]+'"></a>').text(node.get('name'));
						o.append('<input type="hidden" name="userid[]" value="'+ids[i]+'" />');
						o.append('<input type="hidden" name="address[]" value="'+data.addr+'" />');
					} else {
						var o = me._jq('<a href="javascript:void(0)" class="select-item" _groupid="'+node.get('groupid')+'"></a>').html(node.get('groupname') + ' <span class="gray">&lt;群组&gt;</span>');
						o.append('<input type="hidden" name="groupid[]" value="'+node.get('groupid')+'" />');
					}
					me._selectBox.append(o);
					
					o.bind('click', function(){
						if (!me._enable) { return ;}
						var id = !isGroup ? me._jq(this).attr('_userid') : 'group_' + me._jq(this).attr('_groupid');
						me.unselect(id);
					});
				}
				
				me._searchResult.find('#usr-' + id).hide();
			}
		});
	},
	
	/**
	 * 取消选择
	 */
	unselect: function(id) {
		var me = this,
			isGroup = id.indexOf('group_') === 0;
		
		if (isGroup) {
			var groupId = id.replace('group_', '');
			me._selectBox.find('a[_groupid="'+groupId+'"]').remove();
			var node = me._groupTree.find(id, true);
		} else {
			me._selectBox.find('a[_userid="'+id+'"]').remove();
			var node = me._memberTree.find('u-' + id, true);
		}
		
		if (node) {
			node.show();
		}
		
		this._jq('#usr-' + id).show();
	},
	
	/**
	 * 重置
	 */
	reset: function() {
		var me = this;
		this._selectBox.find('a.select-item').each(function(){
			var id = me._jq(this).attr('_userid');
			if (!id) {
				id = me._jq(this).attr('_groupid')
			}
			me.unselect(id);
		});
	},
	
	/**
	 * 获取选定的用户ID
	 * 
	 * @return {Array}
	 */
	getUserId: function() {
		var arr = [],
			me  = this;
		this._selectBox.find('a.select-item[_userid]').each(function(){
			arr.push(me._jq(this).attr('_userid'));
		});
		return arr;
	},
	
	getGroupId: function() {
		var arr = [],
		    me  = this;
		this._selectBox.find('a.select-item[_groupid]').each(function(){
			arr.push(me._jq(this).attr('_groupid'));
		});
		return arr;
	},
	
	/**
	 * 渲染到
	 */
	renderTo: function(parent) {
		this._el.appendTo(parent);
	}
};

/**
 * 
 */
var Util = {
    
    /**
     * 停止事件冒泡
     * 
     * @var Object e
     */
    stopEventBuddle: function(e) {
        e.cancelBubble = true;
        if (e.stopPropagation) {
            e.stopPropagation();
        }
    },
    
    /**
     * 是否数组
     * @return boolean
     */
    isArray: function (obj) {
        return obj.length && /function\s+Array/.test(obj.constructor.toString());
    },
    
    /**
     * 数组元素是否存在
     * 
     * @param key
     * @param array
     * @return
     */
    inArray: function(val, arr) {
        for (var i = 0, j = arr.length; i < j; i++) {
            if (arr[i] == val) {
                return true ;
            }
        }
        
        return false;
    },
    
    /**
     * 清除前后空格
     * 
     * @param str
     * @return
     */
    trim: function(str) {
        return str.replace(/^\s+|\s+$/, '');
    }
};

/**
 * Cookie 操作
 */
var Cookie = {

    set: function(key, value, options) {
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + options.expires);
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        var path = options.path ? '; path=' + options.path : '';
        var domain = options.domain ? '; domain=' + options.domain : '';
        var secure = options.secure ? '; secure' : '';
        
        document.cookie = [key, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    },
    
    get: function(name) {
        var v = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = $.trim(cookies[i]);
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    v = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return v;
    }
};

// 扩展jQuery方法
function jQueryExtend($){

// 输入框提示
$.fn.keyhint = function(content, css) {
    if (!css) css = 'gray';
    var input = $(this);
    if (!content) content = input.attr('title');
    
    input.focus(function(){
        input.filter('.'+css).removeClass(css).val('');
    }).blur(function(){
        if (this.value == '')
            input.addClass(css).val(content);
    }).each(function(){
        if(this.value == '' || this.value == this.title)
            input.addClass(css).val(content);
    });
    
    input.parents('form:eq(0)').submit(function(){
        input.filter('.'+css).removeClass(css).val('');
    });
    
    return this;
};

$.fn.disable = function() {
    var obj = $(this);
    var tagName = this.tagName;
    if (tagName && tagName.toLowerCase() == 'form') {
        return obj.find('input,button,select').attr('disabled', true);
    }
    obj.attr('disabled', true);
    
    return obj;
};

$.fn.enable = function() {
    var obj = $(this);
    var tagName = this.tagName;
    if (tagName && tagName.toLowerCase() == 'form') {
        return obj.find('input:not([_disabled]),button:not([_disabled]),select:not([_disabled])').attr('disabled', false);
    } else if (!obj.attr('_disabled')) {
        obj.removeAttr('disabled');
    }
    
    return obj;
};

$.fn.rowHover = function(markClick, cls) {
    var list = $(this);
    if (!cls) {
        cls = 'over';
    }
    
    list
    .bind('mouseover', function(){
        if (this.tagName.toLowerCase() == 'tr') {
            $(this).addClass('over');
        } else {
            $(this).find('tr').addClass('over');
        }
    })
    .bind('mouseout', function(){
        $(this).removeClass('over');
        $(this).find('tr').removeClass('over');
    });
    
    if (markClick) {
        list.bind('click', function(){
            list.find('tr.focus').removeClass('focus');
            
            if (this.tagName.toLowerCase() == 'tr') {
                $(this).addClass('focus');
            } else {
                $(this).find('tr').addClass('focus');
            }
        });
    }
    
    return this;
};

$.fn.rowFocus = function() {
    var o = $(this)[0];

    if (o.tagName.toLowerCase() == 'tr') {
        $(this).addClass('focus');
    } else {
        $(this).find('tr:eq(0)').addClass('focus');
    }
};

}

jQueryExtend(jQuery);


// 外层调用函数
var checkHash, reloadHash = true;

function hash(str) {
    location.hash = str;
    reloadHash = false;
}

function title(str) {
    document.title = str;
}

function checkHash(hash) {
    var currHash = location.hash;

    checkHash = setInterval(function(){
        if (!reloadHash) {
            currHash = location.hash;
            reloadHash = true;
            return ;
        }

        if (location.hash != currHash) {
            if (currHash.replace(/^#+/, '')) {
                $('#mainframe')[0].src = hash;
            }

            currHash = location.hash;
        }
    }, 100);
}

function switchMod(mod) {
    $('#sidebar li.current').removeClass('current');
    
    if (mod) {
        $('#nav-' + mod).addClass('current');
    }
}

$(function(){
var hash = location.hash;
if (hash) {
    hash = hash.replace(/^#+/, '');
    $('#mainframe')[0].src = hash;
}

if (!$.browser.msie || ($.browser.msie && $.browser.version >= 8)) {

    window.onhashchange = function() {
        var currHash = location.hash;
        
        if (currHash) {
            currHash = currHash.replace(/^#+/, '');
            
            if (reloadHash) {
                $('#mainframe')[0].src = currHash;
            }
        }
        
        if (!reloadHash) {
            reloadHash = true;
        }
        
        hash = currHash;
    };
}
});