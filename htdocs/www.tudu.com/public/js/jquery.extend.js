/* dropdown menu */
$.Dropdown=function(a){$.extend(this,$.Dropdown.defaults,a||{});this.init()};$.Dropdown.defaults={id:null,target:null,menuCls:"option-menu",menuCss:"",wrapCls:"option-menu-wrap",menuBody:null,separaCls:"menu-step",itemCls:"menu-item",itemHoverCls:"menu-over",maxHeight:null,maxWidth:null,offsetLeft:0,offsetTop:0,width:null,anime:"slide",animeSpeed:"fast",items:[],onShow:function(){},onSelect:function(){},onHide:function(){},resetpos:null,separate:null,order:null};(function(a){a.Dropdown.prototype={_menu:null,_target:null,_enabled:true,isShow:false,onShow:function(){},onSelect:function(){},onHide:function(){},init:function(){var b=this;if(this.target){this._target=a(this.target);this._target.bind("click",function(c){if(b._enabled){b.toggle(c)}else{b.hide()}c.cancelBubble=true;if(c.stopPropagation){c.stopPropagation()}})}a(document.body).bind("click",function(){b.hide()})},updateSeparate:function(b){this.separate=b},_initMenu:function(){this._wrap=a("<div>").addClass(this.wrapCls);this._menu=a("<div>");if(this.id){this._wrap.attr("id",this.id)}this._wrap.css({position:"absolute",display:"none"});this._menu.addClass(this.menuCls);if(this.maxWidth){this._wrap.css("width",this.maxWidth);if(!this.menuCss){this.menuCss={width:(parseInt(this.maxWidth.replace("px",""))-2)+"px"}}else{this.menuCss.width=(parseInt(this.maxWidth.replace("px",""))-2)+"px"}}this._menu.css(this.menuCss);if(this.menuBody){this._menu.append(this.menuBody)}if(this.items&&this.items.length){this._initMenuItem()}this._wrap.append(this._menu);a(document.body).append(this._wrap)},_initMenuItem:function(){for(var b=0,d=this.items.length;b<d;b++){this.addItem(this.items[b])}},show:function(l){if(!this._menu){this._initMenu()}this._wrap.css({left:"-9999px"}).show();var k=l.srcElement?a(l.srcElement):a(l.target),f=this._target?this._target:k,i=f.offset(),h=f.outerHeight(),n=this._wrap.height(),b=a(window).height(),m=document.body.scrollTop?document.body.scrollTop:document.documentElement.scrollTop;this._wrap.hide();if(i.top+h+n<b||i.top-m<n||this.alwaysBottom){this._wrap.css({top:i.top+h+this.offsetTop+"px",left:i.left+this.offsetLeft})}else{this._wrap.css({top:i.top-n+this.offsetTop+"px",left:i.left+this.offsetLeft})}if(this.maxHeight&&n>this.maxHeight){this._wrap.find(".option-menu").css({height:this.maxHeight})}if(this.anime){var j=this,g=null;switch(this.anime){case"fade":g="fadeIn";break;case"slide":default:g="slideDown";break}this._wrap[g].call(this._wrap,this.animeSpeed,function(){j.isShow=true})}else{this._wrap.show()}this.isShow=true;this.onShow();if(this.resetpos){var i=this._target.offset();this._wrap.css({left:i.left+this._target.outerWidth(true)+"px",top:i.top+"px"});var d=this._wrap.offset(),n=this._wrap.find(".option-menu").height(),c=a(document.body).height();if(d.top+n>c){this._wrap.css({top:d.top-(d.top+n-c)-20+"px"})}}return this},hide:function(){if(!this._menu){return this}if(!this.isShow){return}if(this.anime){var c=this,b=null;switch(this.anime){case"fade":b="fadeOut";break;case"slide":default:b="slideUp";break}this._wrap[b].call(this._wrap,this.animeSpeed,function(){c.isShow=false})}else{this._wrap.hide()}this.isShow=false;this.onHide();return this},addItem:function(d){var e=this;if(d=="-"){this._menu.append('<div class="'+this.separaCls+'"></div>');return this}var c=a("<div>").addClass(this.itemCls);for(var b in d){switch(b){case"body":c.html(d[b]);break;case"event":for(var b in d.event){if(typeof(d.event[b])=="function"){c.bind(b,function(){d.event[b].call(c)})}}break;case"data":c.data=d.data;break;default:c.attr(b,d[b])}}c.bind("click",function(){e.onSelect.call(c)}).bind("mouseover",function(){a(this).addClass(e.itemHoverCls)}).bind("mouseout",function(){a(this).removeClass(e.itemHoverCls)});this._menu.append(c)},clear:function(){if(this._wrap){this._wrap.find(".option-menu").empty()}},setBody:function(b){if(this._wrap){this._wrap.find(".option-menu").html(b)}},toggle:function(b){if(this.isShow){this.hide()}else{this.show(b)}},disabled:function(){this._enabled=false},enabled:function(){this._enabled=true},destroy:function(){this._target.unbind("click");if(this._menu){this._menu.remove()}}};a.fn.dropdown=function(c){c.srcElement=this;c.target=this;var b=new a.Dropdown(c);return b}})(jQuery);
/* ajaxupload */
jQuery.fn.extend({serializeFileArray:function(){return this.map(function(){return jQuery.nodeName(this,"form")?jQuery.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.type=="file")}).map(function(A,B){var C=jQuery(this).val();return C==null?null:C.constructor==Array?jQuery.map(C,function(E,D){return{name:B.name,value:E}}):{name:B.name,value:C}}).get()}});jQuery.extend({createUploadIframe:function(D,B){var A=D;if(window.ActiveXObject){var C;if(jQuery.browser.version>="9.0"){C=document.createElement("iframe");C.id=A;C.name=A}else{C=document.createElement('<iframe id="'+A+'" name="'+A+'" />')}if(typeof B=="boolean"){C.src="javascript:false"}else{if(typeof B=="string"){C.src=B}}}else{var C=document.createElement("iframe");C.id=A;C.name=A}C.style.position="absolute";C.style.top="-1000px";C.style.left="-1000px";document.body.appendChild(C);return C},createUploadForm:function(F,B,D){var A="jUploadFile"+F;var E=F;var C=jQuery('<form action="" method="POST" name="'+E+'" id="'+E+'" enctype="multipart/form-data"></form>');jQuery(B).each(function(){var G=jQuery(this).clone();jQuery(this).before(G);jQuery(this).appendTo(C)});jQuery(D).each(function(){jQuery('<input type="hidden" name="'+this.name+'" value="'+jQuery.HTMLEncodeLite(this.value)+'" />').appendTo(C)});jQuery(C).css("position","absolute");jQuery(C).css("top","-1200px");jQuery(C).css("left","-1200px");jQuery(C).appendTo("body");return C},ajaxUpload:function(J){J=jQuery.extend({},jQuery.ajaxSettings,J);var A=new Date().getTime();var G="jUploadFrame"+A;var I="jUploadForm"+A;var B=jQuery.createUploadForm(I,J.file,J.data);var H=jQuery.createUploadIframe(G,J.secureuri);if(J.global&&!jQuery.active++){jQuery.event.trigger("ajaxStart")}var C=false;var E={};if(J.global){jQuery.event.trigger("ajaxSend",[E,J])}var D=function(K){var O=document.getElementById(G);try{if(O.contentWindow){E.responseText=O.contentWindow.document.body?O.contentWindow.document.body.innerHTML:null;E.responseXML=O.contentWindow.document.XMLDocument?O.contentWindow.document.XMLDocument:O.contentWindow.document}else{if(O.contentDocument){E.responseText=O.contentDocument.document.body?O.contentDocument.document.body.innerHTML:null;E.responseXML=O.contentDocument.document.XMLDocument?O.contentDocument.document.XMLDocument:O.contentDocument.document}}}catch(N){jQuery.handleError(J,E,null,N)}if(E||K=="timeout"){C=true;var L;try{L=K!="timeout"?"success":"error";if(L!="error"){var M=jQuery.uploadHttpData(E,J.dataType);if(J.success){J.success(M,L)}if(J.global){jQuery.event.trigger("ajaxSuccess",[E,J])}}else{jQuery.handleError(J,E,L)}}catch(N){L="error";jQuery.handleError(J,E,L,N)}if(J.global){jQuery.event.trigger("ajaxComplete",[E,J])}if(J.global&&!--jQuery.active){jQuery.event.trigger("ajaxStop")}if(J.complete){J.complete(E,L)}jQuery(O).unbind();setTimeout(function(){try{jQuery(O).remove();jQuery(B).remove()}catch(P){jQuery.handleError(J,E,null,P)}},100);E=null}};if(J.timeout>0){setTimeout(function(){if(!C){D("timeout")}},J.timeout)}try{var B=jQuery("#"+I);jQuery(B).attr("action",J.url);jQuery(B).attr("method","POST");jQuery(B).attr("target",G);if(B.encoding){B.encoding="multipart/form-data"}else{B.enctype="multipart/form-data"}jQuery(B).submit()}catch(F){jQuery.handleError(J,E,null,F)}if(window.addEventListener){document.getElementById(G).addEventListener("load",D,false)}else{document.getElementById(G).attachEvent("onload",D)}return{abort:function(){}}},uploadHttpData:function(r,type){var data=!type;data=type=="xml"||data?r.responseXML:r.responseText;if(type=="script"){jQuery.globalEval(data)}if(type=="json"){eval("data = "+data)}if(type=="html"){jQuery("<div>").html(data).evalScripts()}return data},HTMLEncodeLite:function(A){if(A==undefined){return""}A=A.replace(/\&/g,"&amp;");A=A.replace(/\>/g,"&gt;");A=A.replace(/\</g,"&lt;");A=A.replace(/\"/g,"&quot;");A=A.replace(/\'/g,"&#39;");return A}});
/* mailinput */
/**
 * 联系人输入框扩展
 */
(function($){

$.mailinput = function(config) {
    $.extend(this, config || {});
    this.init();
};

/**
 * prototype
 */
$.mailinput.prototype = {
    /**
     * 替换的输入元素
     */
    target: null,
    
    /**
     * 元素模板
     */
    itemTemplate: null,
    
    /**
     * 元素ID
     */
    id: null,
    
    /**
     * 是否支持多行输入
     */
    multiLine: false,
    
    /**
     * 分隔符
     */
    separator: ';',
    
    /**
     * 字体大小
     */
    fontSize: 12,
    
    /**
     * 可用最大对象
     */
    maxCount: null,
    
    /**
     * 输入框初始长度
     */
    baseWidth: 14,
    
    /**
     * 
     */
    tabIndex: null,
    
    /**
     * 是否按住ctrl
     */
    _onCtrl: false,
    
    /**
     * 是否按住shift
     */
    _onShift: false,
    
    /**
     * 
     */
    _ele: null,
    
    /**
     * 模拟的输入框
     */
    _ipbox: null,
    
    /**
     * 输入框
     */
    _input: null,
    
    /**
     * 输入显示
     */
    _inputDisp: null,
    
    /**
     * jQuery
     */
    jq: $,
    
    /**
     * 
     */
    _enabled: true,
    
    /**
     * 初始化
     */
    init: function() {
        var ct = this.target.parent(),
            instance = this;
        this.target.attr('readonly', false).hide();
        
        if (null === this.tabIndex) {
            this.tabIndex = this.target.attr('tabindex');
        }
        
        this._ele = this.jq('<div>');
        this._ele
        .addClass('mail_input' + (this.cls ? ' ' + this.cls : ''))
        .css({cursor: 'text', outline: 'none', width: this.width ? this.width : 'auto'})
        .append(this._createInput());
        
        if (this.id) {
            this._ele.attr('id', this.id);
        }

        this._initEvents();
        
        if (!$.browser.msie) {
            this._ele.attr('tabindex', 0);
        }
        
        ct.append(this._ele);
        
        if (this.autoComplete) {
            this.autoComplete.jq = this.jq;
            $.extend(this.autoComplete, {target: this._input, attachTo: this._ele});
            this._autoComp = new $.autocomplete(this.autoComplete);
        }
        
        this._checkInputLen();
    },
    
    _createInput: function() {
        var instance = this;
        this._ipbox = this.jq('<div>');
        this._input = this.jq('<input>');
        this._input
        .attr({id: this.id + '-ip', type: 'text', autocomplete: 'off', tabindex: instance.tabIndex})
        .css({
            'width': '100%',
            'border': 'none',
            'background': 'none',
            'padding': '1',
            'margin': '0',
            'font-slsize': this.fontSize + 'px',
            'float': 'left',
            'outline': 'none',
            '-webkit-appearance': 'none'
        })
        .bind('keyup', function(e){instance._inputKeyup(e)})
        .bind('keydown', function(e){instance._inputKeydown(e)})
        .bind('blur', function(){
            var text = instance._input.val() + '';
            
            if (text && instance._autoComp && !instance._autoComp.isOver && !instance.isOver) {
                instance.appendItem(text);
                instance.setText('');
            }
            
            if (instance._autoComp && !instance._autoComp.isOver) {
                instance._autoComp.clearMatchList();
            }
        })
        .bind('click', function(e){
            e.cancelBubble = true;
            if (e.stopPropagation) {
                e.stopPropagation();
            }
        });
        
        this._inputDisp = this.jq('<div>');
        this._inputDisp
        .css({
            'float': 'left', 
            'width': '1px', 
            'height': '1px', 
            'fontSize': this.fontSize + 'px', 
            'overflow': 'auto', 
            'white-space': 'nowrap', 
            'word-spacing': '0px',
            '*overflow': 'hidden'
        });
        
        this._ipbox
        .css({
            'float': 'left',
            'width': this.baseWidth + 'px',
            'overflow': 'hidden',
            'margin-right': '-' + this.baseWidth + 'px'
        })
        .append(this._inputDisp)
        .append(this._input);
        
        return this._ipbox;
    },
    
    /**
     * 
     */
    _initEvents: function() {
        var instance = this;
        
        this._ele
        .bind('click', function(e){
            instance._ipbox.find(':text').focus();
            instance._ele.find('.mail_item_selected').removeClass('mail_item_selected');
            instance._setCursorPos();
            return instance._cancelEventBubble(e)
        })
        .bind('keydown', function(e){instance._elBoxKeydown(e);})
        .bind('mouseover', function(){instance.isOver = true})
        .bind('mouseout', function(){instance.isOver = false});
    },
    
    /**
     * 获取光标在输入框中的位置
     */
    _getCursorPos: function() {
        var CaretPos = 0;   // IE Support
        var ctrl = this._input[0];
        if (document.selection) {
            ctrl.focus ();
            var Sel = document.selection.createRange ();
            Sel.moveStart ('character', -ctrl.value.length);
            CaretPos = Sel.text.length;
        }
        // Firefox support
        else if (ctrl.selectionStart || ctrl.selectionStart == '0')
            CaretPos = ctrl.selectionStart;
        return (CaretPos);
    },
    
    _setCursorPos: function(pos) {
        var elem = this._input[0];
        var max  = elem.value.length;
        
        if (!pos) pos = max;
        
        if (elem.createTextRange) {
            var range = elem.createTextRange();
            range.move('character', pos);
            range.select();
        } else {
            elem.setSelectionRange(pos, pos);
            elem.focus();
            
            this._fixInputCursor();
        }
    },
    
    /**
     * 
     */
    _fixInputCursor: function() {
        if (document.createEvent) {
            elem = this._input[0];
            
            var evt = document.createEvent('KeyboardEvent');
            evt.initKeyEvent('keypress', true, true, null, false, false, false, false, 0, 32);
            elem.dispatchEvent(evt);
            evt = document.createEvent('KeyboardEvent');
            evt.initKeyEvent('keypress', true, true, null, false, false, false, false, 8, 0);
            elem.dispatchEvent(evt);
        }
    },
    
    /**
     * 是否可选择内容
     * 
     * @private
     * @param bool
     * @return
     */
    _setSelectable: function(ele, bool) {
        ele.unselectable = ele.unselectable = bool ? "off" : "on"; // IE
        ele.onselectstart = ele.onselectstart = function(){ return bool; }; // IE
        if (document.selection && document.selection.empty) document.selection.empty(); // IE
        if (ele.style) ele.style.MozUserSelect = bool ? "" : "none"; // FF
        ele.style.MozUserSelect = bool ? "" : 'none';
    },
    
    /**
     * 
     */
    _elBoxKeydown: function(e) {
        if (!this._enabled) return ;
        
        var key = e.keyCode ? e.keyCode : e.which;
        
        switch (key) {
            case 13:
                if (evt.preventDefault) {
                    evt.preventDefault();
                }
                break;
            case 9:
                this.appendItem(this._input.val());
                this.setText('');
                break;
            case 8:
            case 46:
                var instance = this;
                this._ele.find('.mail_item_selected').each(function(){
                    instance.removeItem(instance.jq(this));
                });
                
                if (key == 8 && !this.hasFocus()) {
                    evt = window.event ? window.event : e;
                    evt.keyCode = 0;
                    evt.returnValue = false;
                    
                    if (evt.preventDefault) {
                        evt.preventDefault();
                    }
                }
                
                this.focus();
                
                break;
        }
        
        this._cancelEventBubble(e);
    },
    
    /**
     * 
     */
    _inputKeyup: function(e) {
        if (!this._enabled) return ;
        
        var key = e.keyCode ? e.keyCode : e.which;
        switch (key) {
            case 13:
                this._input.focus();
                break;
            // backspace & del
            case 8:
            case 46:
                this._inputDisp.text(this._input.val() + 'WW');
                break;
            // ctrl
            case 17:
                this._onCtrl = false;
                break;
            // shift
            case 16:
                this._onShift = false;
                break;
            // , ;
            case 188:
            case 186:
            case 59:
                var text = this._format(this._input.val());
                if (text) {
                    this.appendItem(text);
                    this.setText('');
                    this.focus();
                }
                
                break;
            default:
                if (this.maxCount > 0) {
                    var count = this._ele.find('.mail_item').size();
                    
                    if (this.maxCount <= count) {
                        this._input.val('');
                        return this._cancelEventBubble(e);
                    }
                }
            
                var text = this._input.val();
                
                this._inputDisp.text(text + 'WW');
                break;
        }
        
        this._updateInputLen();
        return this._cancelEventBubble(e);
    },
    
    /**
     * 输入框事件
     */
    _inputKeydown: function(e) {
        if (!this._enabled) return ;
        
        var key = e.keyCode ? e.keyCode : e.which;
        var pos = this._getCursorPos();

        switch (key) {
            // enter
            case 13:
                if (this._autoComp && this._autoComp.isShow) {
                    this._autoComp.confirmSelect();
                }
                break;
            // backspace
            case 8:
                var prev = this._ipbox.prev();
                
                if (pos <= 0 && prev.size() && !prev.hasClass('mail_item_separator')) {
                    this.removeItem(this._ipbox.prev());
                    this._input.focus();
                } else {
                    this._inputDisp.text(this._input.val() + 'WW');
                }
                
                break;
            // del
            case 46:
                var len = this._input.val().length;
                if (pos >= len && this._ipbox.next().size()) {
                    this.removeItem(this._ipbox.next());
                    this._input.focus();
                } else {
                    this._inputDisp.text(this._input.val() + 'WW');
                }
                break;
            // home
            case 36:
                break;
            // end
            case 35:
                break;
            // left
            case 37:
                if (pos == 0 && this._ipbox.prev().size()) {
                    this._ipbox.insertBefore(this._ipbox.prev());
                    this.focus();
                }
                break;
            // up
            case 38:
                if (this._autoComp && this._autoComp.isShow) {
                    this._autoComp.moveUp();
                    return this._cancelEventBubble(e);
                }
                
                if (!this.multiLine) {
                    return this._cancelEventBubble(e);
                }
                break;
            // right
            case 39:
                var len = this._input.val().length;
                if (pos >= len && this._ipbox.next().size()) {
                    this._ipbox.insertAfter(this._ipbox.next());
                    this.focus();
                }
                break;
            // down
            case 40:
                if (this._autoComp && this._autoComp.isShow) {
                    this._autoComp.moveDown();
                    return this._cancelEventBubble(e);
                }
                
                if (!this.multiLine) {
                    return this._cancelEventBubble(e);
                }
                break;
            // ctrl
            case 17:
                this._onCtrl = true;
                break;
            // shift
            case 16:
                this._onShift = true;
                break;
            // tab
            case 9:
                var text = this._format(this._input.val());
                if (text) {
                    this.appendItem(text);
                    this.setText('');
                }
                break;

            default:
                if (this.maxCount > 0) {
                    var count = this._ele.find('.mail_item').size();
                    
                    if (this.maxCount <= count) {
                        this._input.val('');
                        return this._cancelEventBubble(e);
                    }
                }
                
                var text = this._input.val();
                
                this._inputDisp.text(text + 'WW');
                break;
        }
        
        this._updateInputLen();
        return this._cancelEventBubble(e);
    },
    
    /**
     * 取消事件冒泡
     */
    _cancelEventBubble: function(e) {
        e.cancelBubble = true;
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        e.returnValue = false;
        return false;
    },
    
    _format: function(str) {
        if (typeof str == 'undefined') {
            return null;
        }
        var reg = new RegExp('/(^\s*)|(\s*$)/');
        
        str = str.replace(',', '').replace(';', '').replace(reg, '');
        
        return str;
    },
    
    disabled: function() {
        this._input.val('');
        this._input.attr('readonly', true);
        this._ele.addClass('mail_input_disabled');
        this._enabled = false;
    },
    
    enabled: function() {
        this._input.removeAttr('readonly');
        this._ele.removeClass('mail_input_disabled');
        this._enabled = true;
    },
    
    /**
     * 
     */
    focus: function() {
        if (!this._input) {
            return ;
        }
        
        var instance = this;
        if ($.browser.msie) {
            setTimeout(function(){
                if (!instance._input) {
                    return ;
                }
                instance._input.focus();
                instance._setCursorPos();
            }, 100);
        } else {
            this._input.focus();
        }
    },
    
    /**
     * 
     */
    hasFocus: function() {
        var e = this._input[0];
        
        if (e.hasFocus) {
            return e.hasFocus();
        }
        
        return document.activeElement == e;
    },
    
    /**
     * 添加项目触发事件
     */
    onAppend: function(item) {
        
    },
    
    /**
     * 
     */
    onBefore: function() {
        
    },
    
    /**
     * 
     */
    onUpdate: function() {
        
    },
    
    /**
     * 删除项目触发事件
     */
    onRemove: function() {
        
    },
    
    appendSeparator: function(separator, item, title) {
        var obj, me = this;
        if (!title) {
            title = '';
        }
        
        if (separator == '+') {
            obj = this.jq('<a href="javascript:void(0)" class="mail_item_separator icon icon_plus" style="float:left;" title="'+title+'"></a>');
        } else {
            obj = this.jq('<a href="javascript:void(0)" class="mail_item_separator icon icon_flow_arrow" style="float:left;" title="'+title+'"></a>');
        }
        
        obj.attr('_separator', separator);
        
        obj.bind('click', function(){
            var o = me.jq(this);
            
            if (me.onBefore.call(me, o)) {
                return ;
            }
            
            if (o.hasClass('icon_plus')) {
                o.removeClass('icon_plus').addClass('icon_flow_arrow');
                o.attr('_separator', '>');
            } else {
                o.removeClass('icon_flow_arrow').addClass('icon_plus');
                o.attr('_separator', '+');
            }
            
            me.onUpdate.call(me, o);
        });

        if (undefined === item || !item || !item.size()) {
            item = this._ipbox;
        }
        
        item.before(obj);
    },
    
    // 添加项目
    appendItem: function(item, params, callback) {
        var obj = this.jq('<div>');
            instance = this;
        obj
        .addClass('mail_item')
        .css('float', 'left')
        .bind('mouseover', function(){
            if (!instance._enabled) return ;
            instance.jq(this).addClass('mail_item_hover');
        })
        .bind('mouseout', function(){
            if (!instance._enabled) return ;
            instance.jq(this).removeClass('mail_item_hover');
        })
        .bind('click', function(e){
            if (!instance._enabled) return ;
            instance.jq('.mail_item_selected').not(this).removeClass('mail_item_selected');
            instance.jq(this).toggleClass('mail_item_selected');
            instance._ele.focus();
            instance._cancelEventBubble(e);
        });
        /*.bind('dblclick', function(e) {
            var o = $(this);
            instance._ipbox.insertBefore(o);
            instance.setText(o.text());
            instance._ele.focus();
            o.remove();
        });*/
        
        this._setSelectable(obj[0], false);
        
        item = this._format(item);
        this._ipbox.before(obj.html(item + this.separator));
        
        if (params) {
            for (var k in params) {
                obj.attr(k, params[k]);
            }
        }
        
        if (!callback) {
            callback = this.onAppend;
        }
        
        callback.call(this, obj);
    },
    
    /**
     * 获取已输入项目
     */
    getItems: function(filter) {
        if (!filter) {
            filter = '';
        }
        return this._ele.find('.mail_item' + filter + ', .mail_item_separator' + filter);
    },
    
    /**
     * 设置输入框文字
     */
    setText: function(text) {
        if (!this._input) {
            return ;
        }
        
        this._input.val(text);
        this._inputDisp.text(text + 'WW');
        this._input.focus();
    },
    
    getText: function(text) {
        if (!this._input) {
            return '';
        }
        
        return this._input.val();
    },
    
    /**
     * 设置实际输入框的宽度
     */
    _updateInputLen: function() {
        if (!this._input || !this._ipbox) {
            return ;
        }
        try {
            if (this._input && !this._input.val()) {
                this._ipbox.css('width', this.baseWidth + 'px');
            } else {
                var width = Math.max(this._inputDisp[0].scrollWidth, this.baseWidth);
                
                this._ipbox.css('width', width + 'px');
                this._input[0].scrollLeft = 0;
            }
        } catch (e) {}
    },
    
    /**
     * for IE
     */
    _checkInputLen: function() {
        var instance = this;
        this._updateInputLen();
        setTimeout(function(){
            if (instance && instance._input && instance._ipbox) {
                instance._checkInputLen();
            }
        }, 100);
    },
    
    // 移除项目
    removeItem: function(item) {
        var instance = this;
        var prev = item.prev();
        item.remove();

        if (prev.hasClass('mail_item_separator')) {
            prev.remove();
        }
        
        item.each(function() {instance.onRemove(this);});
    },
    
    /**
     * 情况所有项目
     */
    removeAll: function() {
        var instance = this;
        this._ele.find('.mail_item').each(function(){
            var item = instance.jq(this);
            item.remove();
            instance.onRemove(item);
        });
    },
    
    // 清空内容
    clear: function() {
        this._input.val('');
        this._inputDisp.empty();
        this._ele.find('.mail_item').remove();
        this._ele.find('.mail_item_separator').remove();
    }
};

/**
 * 完成提示
 */
$.autocomplete = function(config) {
    $.extend(this, config || {});
    
    this.init();
}

/**
 * 
 */
$.autocomplete.prototype = {
    
    /**
     * 固定宽度
     */
    width: null,
    
    /**
     * 数据源
     * 
     * @var Array 
     */
    data: null,
    
    /**
     * 数据获取URL
     * 
     */
    url: null,
    
    /**
     * 检索字段
     * 不指定对记录进行匹配
     * 
     * @var Array | null
     */
    columns: null,
    
    /**
     * 是否为可见状态
     */
    isShow: false,
    
    /**
     * 
     */
    isOver: false,
    
    /**
     * 显示格式
     */
    template: null,
    
    /**
     * 目标输入框
     */
    target: null,
    
    /**
     * 依附元素
     */
    attachTo: null,
    
    /**
     * 
     */
    maxHeight: 200,
    
    /**
     * 
     */
    _el: null,
    
    /**
     * 
     */
    _matches: [],
    
    jq: $,
    
    /**
     * 初始化
     */
    init: function() {
        if (!this.target) {
            return false;
        }
        
        var instance = this;
        this._el = this.jq('<div>');
        
        this._el
        .addClass('ac_list')
        .hide()
        .appendTo(this.target.parents('body'));
        
        var width = (this.width ? this.width : (this.attachTo ? this.attachTo.width() : null));
        
        this._el.css('width', width + 'px');
        
        if (!this.attachTo) {
            this.attachTo = this.target;
        }
        
        this._el
        .bind('mouseover', function(){instance.isOver = true;})
        .bind('mouseout', function(){instance.isOver = false;});
        
        this.jq(document.body).bind('click', function(){
            instance._el.hide();
        });
        this.target.bind('keyup', function(e){
            var key = e.keyCode ? e.keyCode : e.which;
            if (instance.isShow && (key == 38 || key == 40 || key == 13)) {
                if (instance.arrowSupport) {
                    if (key == 38) {
                        return instance.moveUp();
                    }
                    
                    if (key == 40) {
                        return instance.moveDown();
                    }
                }
                
                return ;
            }
            
            if (!this.value) {
                instance.clearMatchList();
                instance._el.hide();
            } else {
                instance.initMatchList(instance.target.val());
            }
        }).bind('keydown', function(e){
            var e   = window.event ? window.event : e,
                key = e.keyCode ? e.keyCode : e.which;
            if (key == 13 && instance.isShow) {
                e.keyCode = e.which = 0;
                e.returnValue = false;
                if (e.preventDefault) {
                    e.preventDefault();
                }
                instance._cancelEventBubble(e);
                instance.confirmSelect();
                return false;
            }
        }).attr({'autocomplete': 'off'});
        
        this._ul = this.jq('<ul>');
        
        this._el.append(this._ul);
    },
    
    /**
     * 数据加载完成触发事件
     */
    onLoaded: function(ret) {
        
    },
    
    /**
     * 被选中触发事件
     */
    onSelect: function(item) {
        
    },
    
    /**
     * 选择匹配数据
     */
    match: function(key) {
        key = key.toLowerCase();
        var datas = isArray(this.data) ? {'': this.data} : this.data;
        this._matches = {};

        if (datas) {
            for (var p in datas) {
                if (this['no' + p] != undefined && this['no' + p]) {
                    continue;
                }

                var cols = (this.columns && this.columns[p] != undefined) ? this.columns[p] : this.columns,
                    data = datas[p];
                this._matches[p] = [];

                var colCount = cols && cols.length ? cols.length : 0;
                for (var k in data) {
                    if (colCount) {
                        for (var i = 0; i < colCount; i++) {
                            if (data[k][cols[i]] != undefined
                                && data[k][cols[i]].toLowerCase().indexOf(key) != -1) 
                            {
                                this._matches[p].push(data[k]);
                                break;
                            }
                        }
                    } else {
                        if (typeof(data[k].indexOf) == 'function'
                            && data[k].toLowerCase().indexOf(key) != -1)
                        {
                            this._matches[p].push(data[k]);
                        }
                    }
                }
            }
        }
    },
    
    /**
     * 初始化列表项目
     */
    _initList: function(keyword) {
        this._ul.empty();
        
        if (this.maxHeight) {
            this._el.css({'height': this.maxHeight + 'px', 'z-index': 10005});
        }
        var instance = this;
        
        for (var k in this._matches) {
            var ms = this._matches[k];
            var tpl = this.template && this.template[k] != undefined ? this.template[k] : this.template;
            
            for (var i = 0, c = ms.length; i < c; i++) {
                var item = this.jq('<li>');
                
                item[0].data = ms[i];
                item
                .bind('mouseover', function(){
                    instance._el.find('.ac_item_over').removeClass('ac_item_over');
                    instance.jq(this).addClass('ac_item_over');
                })
                .bind('click', function(e){
                    instance.hide();
                    instance.onSelect(this);
                    return instance._cancelEventBubble(e);
                });
                
                if (!tpl) {
                    var text = ms[i] + '';
                    text = text.replace(keyword, '<strong>' + keyword + '</strong>');
                    item.text(text);
                } else {
                    var m = tpl.match(/\{\w+\}/gm);
                    var text = tpl;
                    
                    for (var p in m) {
                        var key = m[p] + '';
                        key = key.replace(/[\{\}]/g, '');
                        var val = ms[i][key];
                        
                        if (!val) {
                            val = '';
                        }
                        
                        text = text.replace('{' + key + '}', val.replace(keyword, '<strong>' + keyword + '</strong>'));
                    }
                    item.html(text);
                }
                
                this._ul.append(item);
            }
        }
    },
    
    initMatchList: function(keyword) {
        var instance = this;
        var isNull = true;
        
        if (isArray(this.data) && this.data.length > 0) {
            isNull = false;
        } else {
            for (var k in this.data) {
                if (this.data[k] && this.data[k].length) {
                    isNull = false;
                    break;
                }
            }
        }
        
        if (isNull) {
            if (this.url) {
                $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: this.url,
                    success: function(ret) {
                        instance.onLoaded(ret);
                        
                        instance._initMatchList(keyword);
                    },
                    error: function(res) {
                        return false;
                    }
                });
            } else if (this.loadMethod) {
                this.loadMethod.call(this);
            }
        } else {
            if (this.loadMethod) {
                this.loadMethod.call(this);
            }
            this._initMatchList(keyword);
        }
    },
    
    /**
     * 
     */
    _initMatchList: function(keyword) {
        this.match(keyword);
        
        var count = 0;
        
        for (var k in this._matches) {
            count += (this._matches[k] && this._matches[k].length ? this._matches[k].length : 0);
        }
        if (count > 0) {
            this._initList(keyword);
            this._el.show();
            this.isShow = true;
            var height = this.maxHeight;
            
            if (this.maxHeight > 0) {
                height = this._ul.height();
                
                if (height < this.maxHeight) {
                    this._el.css('height', height + 'px');
                }
            }
            
            var offset = this.attachTo.offset();
            
            this._el
            .css({left: offset.left + 'px', top: offset.top + this.attachTo.height() + 5 + 'px'})
            
            this._ul.find('li:first').addClass('ac_item_over');
        } else {
            this.clearMatchList();
        }
    },
    
    clearMatchList: function() {
        this._matches = {};
        this._ul.empty();
        this.hide();
        this.isShow = false;
    },
    
    /**
     * 
     */
    hide: function() {
        this._el.hide();
    },
    
    /**
     * 焦点上移动
     */
    moveUp: function() {
        var onfocus = this._ul.find('.ac_item_over');
        if (!onfocus.size() || !onfocus.prev().size()) {
            this._ul.find('li:last').addClass('ac_item_over');
            this._el[0].scrollTop = this._el[0].scrollHeight;
            return ;
        }
        
        if (onfocus.prev()) {
            onfocus.removeClass('ac_item_over');
            onfocus.prev().addClass('ac_item_over');
            
            var top = onfocus.prev()[0].offsetTop;
            
            if (top < this._el[0].scrollTop) {
                this._el[0].scrollTop = top;
            }
        }
    },
    
    /**
     * 焦点向下移动
     */
    moveDown: function() {
        var onfocus = this._ul.find('.ac_item_over');
        if (!onfocus.size() || !onfocus.next().size()) {
            this._ul.find('li:first').addClass('ac_item_over');
            this._el[0].scrollTop = 0;
            return ;
        }
        
        if (onfocus.next()) {
            onfocus.removeClass('ac_item_over');
            onfocus.next().addClass('ac_item_over');
            
            var top = onfocus.next()[0].offsetTop;
            
            if (top >= this._el[0].scrollTop + this._el.height() || top < this._el[0].scrollTop) {
                this._el[0].scrollTop = top;
            }
        }
    },
    
    /**
     * 是否有选中项目
     */
    hasSelect: function() {
        return this._ul.find('.ac_item_over').size() > 0;
    },
    
    /**
     * 确认选中
     */
    confirmSelect: function() {
        var item = this._ul.find('.ac_item_over');
        
        if (item.size()) {
            this.hide();
            this.onSelect(item[0]);
        }
    },
    
    /**
     * 取消事件冒泡
     */
    _cancelEventBubble: function(e) {
        e.cancelBubble = true;
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        return false;
    }
};

})(jQuery);

function isArray(obj) {
    return Object.prototype.toString.apply(obj) == '[object Array]';
}
/* tree */
(function(A){A.tree=function(C){C=C||{};for(var B in C){this[B]=C[B]}this.init()};A.tree.prototype={ele:null,id:null,isAsync:false,childrenKey:"children",idKey:"id",idPrefix:"tree-",baseCls:"tree",nodes:null,init:function(){this.ele=A("<div>");this.ele.addClass(this.baseCls);this.nodeList=A("<ul>");this.nodeList.addClass(this.baseCls+"-root");this.ele.append(this.nodeList);if(this.id){this.ele.attr("id",this.id)}if(this.cls){this.ele.addClass(this.cls)}this.nodes=[]},getNode:function(G,B,E){if(!E){E=this.idKey}var C=null;for(var D=0,F=this.nodes.length;D<F;D++){if(this.nodes[D].get(E)==G){return this.nodes[D]}if(B){C=this.nodes[D].find(G,true,E);if(C!=null){return C}}}return null},find:function(D,B,C){return this.getNode(D,B,C)},search:function(B){var H=[];for(var E=0,I=this.nodes.length;E<I;E++){var F=0,J=0;for(var D in B){J++;if(B[D]==this.nodes[E].get(D)){F++}}if(F>0&&F==J){H.push(this.nodes[E])}var G=this.nodes[E].search(B);for(var D=0,C=G.length;D<C;D++){H.push(G[D])}}return H},appendTo:function(B){this.ele.appendTo(B);return this},appendNode:function(B){this.nodes.push(B);B.setTree(this);this.nodeList.append(B.ele);return this},removeNode:function(C){var B=this.find(C,true);if(B){B.remove()}return this},clear:function(){for(var B in this.nodes){if(this.nodes[B] instanceof A.treenode){this.nodes[B].remove();delete this.nodes[B]}}this.nodes=[]}};A.treenode=function(C){C=C||{};for(var B in C){this[B]=C[B]}this._children=[];this.init()};A.treenode.prototype={ele:null,nodeBody:null,id:null,_childList:null,tree:null,data:{},parent:null,depth:0,isLeave:false,init:function(){var B=this;if(!this.tree){return}this.ele=A("<li>");this.ele.addClass(this.tree.baseCls+"-node");this.nodeBody=A("<div>");this.nodeBody.addClass(this.tree.baseCls+"-node-el");this.indent=A("<span>");this.indent.addClass(this.tree.baseCls+"-node-indent");for(var D=0;D<this.depth;D++){this.indent.append("<span></span>")}this.nodeBody.append(this.indent);if(!this.isLeaf){this.nodeBody.append('<span class="'+this.tree.baseCls+"-ec-icon "+this.tree.baseCls+'-elbow-plus"></span>');this.nodeBody.find("."+this.tree.baseCls+"-ec-icon").click(function(E){B.toggle();E.cancelBubble=true;if(E.stopPropagation){E.stopPropagation()}}).css({visibility:"hidden"})}else{this.ele.addClass(this.tree.baseCls+"-node-leaf");this.nodeBody.append('<span class="'+this.tree.baseCls+'-ec-icon"></span>')}if(this.tree.template){this.content=this.tree.template}content=this._formatEl(this.content);this._childList=A("<ul>");this._childList.hide();if(this.events){for(var C in this.events){this.ele.bind(C,this.events[C])}}this.nodeBody.append(content);this.ele.append(this.nodeBody);this.ele.append(this._childList);if(this.data[this.tree.idKey]!=undefined){this.ele.attr("id",this.tree.idPrefix+this.data[this.tree.idKey])}},appendChild:function(B){this._children.push(B);B.parent=this;B.depth=this.depth+1;B.setTree(this.tree);this._childList.append(B.ele);this.nodeBody.find("."+this.tree.baseCls+"-ec-icon").css({visibility:"visible"})},insertBefore:function(D,C){if(!C){return}var B=this.find(D);if(!B){this.appendChild(C)}this._childList.before(B.ele);C.parent=this;C.depth=this.depth+1;this._children.push(C);C.setTree(this.tree)},removeChild:function(C){var B=this.find(C);if(B!=null){B.remove()}},find:function(G,B,E){if(!this._children.length){return null}if(!E){E=this.tree.idKey}var C=null;for(var D=0,F=this._children.length;D<F;D++){if(this._children[D].get(E)==G){return this._children[D]}if(B){C=this._children[D].find(G,true,E);if(C!=null){return C}}}return null},search:function(B){var H=[];for(var E=0,I=this._children.length;E<I;E++){var F=0,J=0;for(var D in B){J++;if(this._children[E]&&B[D]==this._children[E].get(D)){F++}}if(F>0&&F==J){H.push(this._children[E])}var G=this._children[E].search(B);for(var D=0,C=G.length;D<C;D++){H.push(G[D])}}return H},getChildren:function(B){var D=[];for(var C=0,F=this._children.length;C<F;C++){if(!this._children[C]){continue}D.push(this._children[C]);if(B){var E=this._children[C].getChildren(true);D=D.concat(E)}}return D},remove:function(){this.ele.remove()},hide:function(){this.ele.hide()},show:function(){this.ele.show()},isShow:function(){return this.ele.is(":visible")},get:function(B){return this.data[B]!=undefined?this.data[B]:null},getData:function(){return this.data},setTree:function(B){var C=(this.tree!=B);this.tree=B;this.baseCls=this.tree.baseCls;if(C){this.init()}},expand:function(){this.isExpanded=true;this.ele.addClass(this.baseCls+"-node-expand");this.nodeBody.find("."+this.baseCls+"-ec-icon").removeClass(this.baseCls+"-elbow-plus").addClass(this.baseCls+"-elbow-minus");if(this.parent){this.parent.expand()}this._childList.show()},collspan:function(){delete this.isExpanded;this.ele.removeClass(this.baseCls+"-node-expand");this.nodeBody.find("."+this.baseCls+"-ec-icon").removeClass(this.baseCls+"-elbow-minus").addClass(this.baseCls+"-elbow-plus");this._childList.hide()},toggle:function(){if(this.isExpanded){this.collspan()}else{this.expand()}},select:function(){this.ele.addClass(this.baseCls+"-node-selected")},unselect:function(){this.ele.removeClass(this.baseCls+"-node-selected")},_formatEl:function(G){var F=new RegExp("{([^}]+)}","g");var B=null,C=null,E=[];while(B=F.exec(G)){E.push(B)}for(var D=0,H=E.length;D<H;D++){C=this.get(E[D][1]);C=C==null?"":C;G=G.replace(E[D][0],C)}return G}}})(jQuery);
/* window */
(function(A){A.window=function(D){var C=this;this.opts=A.extend({},A.window.defaults,D);this.fixIE=(A.browser.msie&&(A.browser.version<7));this.id=Math.random();if(!this.opts.applyTo){return}var B=A(this.opts.applyTo);B.css("z-index",8001);this.dialog={container:B,header:B.find(".pop_header"),body:B.find(".pop_body"),footer:B.find(".pop_footer")};this.bh=A("#fix-height");if(!this.bh.size()){this.bh=A("<div>").attr("id","fix-height").css({position:"absolute",height:"100%",width:1,left:-100,top:0}).appendTo(document.body)}this.fn={show:this.opts.show,close:this.opts.close};this.mask=A('<div class="mask">').appendTo(document.body).hide();this.mask.css("z-index",8000);this.dialog.container.hide().appendTo(document.body);this.dialog.container.find("."+this.opts.closeCls).click(function(E){E.preventDefault();C.close()});this.dialog.container.css({height:this.opts.height,width:this.opts.width});if(this.fixIE){this.iframe=A('<iframe src="javascript:false;">').css({opacity:0,position:"absolute",zIndex:-1,width:"100%",height:this.mask.height(),top:0,left:0}).hide().appendTo(this.mask)}if(this.opts.draggable){this.dragDelegate=new A.dragable({ele:this.dialog.container[0],triggerEle:this.dialog.header[0]})}return this};A.window.prototype={show:function(){if(this.iframe){this.iframe.show()}this.mask.show();this.resizeMask();this.dialog.container.show();this.center();if(A.isFunction(this.fn.show)){this.fn.show.apply(this,[this.dialog])}this.bindEvents();this.opts.onShow()},close:function(){if(this.iframe){this.iframe.hide()}this.mask.hide();this.dialog.container.hide();if(A.isFunction(this.fn.close)){this.fn.close.apply(this,[this.dialog])}this.unbindEvents();this.opts.onClose()},resizeMask:function(){if(this.fixIE){var B=Math.max(document.body.offsetHeight,this.bh.height());var C=document.body.offsetWidth;this.mask.css({height:B,width:C})}},center:function(){var B=(document.body.offsetWidth-A(this.dialog.container).width())/2;var C=((Math.min(this.bh.height(),document.documentElement.offsetHeight)-this.dialog.container.height())/2+Math.max(document.documentElement.scrollTop,document.body.scrollTop));this.dialog.container.css({left:B,top:C})},bindEvents:function(){var B=this;if(this.fixIE){A(window).bind("resize",function(){B.resizeMask()})}},unbindEvents:function(){var B=this;if(this.fixIE){A(window).unbind("resize",function(){B.resizeMask()})}},destroy:function(){this.mask.remove();this.dialog.container.remove();return null},hide:function(){if(this.iframe){this.iframe.hide()}this.mask.hide();this.dialog.container.hide()},find:function(B){return this.dialog.container.find(B)}};A.fn.window=function(C){var B=this[0];return B&&B.window?B.window:B.window=new A.window(A.extend(C,{applyTo:B,id:B.id}))};A.window.defaults={id:"window",width:500,height:"auto",close:true,closeCls:"close",draggable:false,onOpen:function(){},onShow:function(){},onClose:function(){}};A.window.prototype2={id:null,defaults:{id:"window",width:500,height:"auto",close:true,closeCls:"close",draggable:false,onOpen:null,onShow:null,onClose:null},show2:function(){console.log(this);return;this.mask.show().setSize();if(this.dialog.iframe){this.dialog.iframe.show()}this.dialog.container.show();this.center();this.bindEvents();if(A.isFunction(this.fn.onShow)){this.fn.onShow.apply(this,[this.dialog])}},container:"body",dialog:{},fn:{},init:function(B){console.log(this);return;this.opts=A.extend({},A.window.defaults,B);this.dialog.container=A("<div>");if(this.opts.applyTo){this.dialog.container=A(this.opts.applyTo);this.dialog.header=this.dialog.container.find(".window-header");this.dialog.body=this.dialog.container.find(".window-body");this.dialog.footer=this.dialog.container.find(".window-footer")}this.fn.onShow=this.opts.onShow;this.fn.onClose=this.opts.onClose;this.create();this.resize();return this},create:function(){var B=this;this.container=A(this.container);this.mask=A('<div class="mask">');if(!this.dialog.header){this.dialog.header=A("<div>").addClass("window-header");this.dialog.container.append(this.dialog.header)}if(!this.dialog.body){this.dialog.body=A("<div>").addClass("window-body");this.dialog.container.append(this.dialog.body)}if(!this.dialog.container){this.dialog.container=A("<div>").attr("id",this.opts.id).addClass("window").css({height:this.opts.height,width:this.opts.width}).hide()}if(this.dialog.footer){this.dialog.container.append(this.dialog.footer)}this.mask.appendTo(this.container).hide();this.dialog.container.hide().appendTo("body");this.container.find("."+this.opts.closeCls).click(function(C){C.preventDefault();B.close()});if(this.fixIE){this.dialog.iframe=A('<iframe src="javascript:false;">').css({opacity:0,position:"absolute",zIndex:1000,width:"100%",top:0,left:0}).hide().appendTo("body")}if(this.opts.draggable&&A.fn.drag){this.dialog.container.drag(this.opts.drag)}this.mask.setSize=function(){if(B.fixIE){var C=Math.max(B.container.height(),B.bh.height());var D=B.container.width();B.mask.css({height:C,width:D});if(B.dialog.iframe){B.dialog.iframe.css({height:C,width:D})}}else{B.mask.css("position","fixed")}};this.center=function(){var C=(B.container.width()-A(B.dialog.container).width())/2;var D=((Math.min(B.bh.height(),document.documentElement.offsetHeight)-B.dialog.container.height())/2+Math.max(document.documentElement.scrollTop,document.body.scrollTop));B.dialog.container.css({left:C,top:D})}},resize:function(){this.dialog.container.css({height:this.opts.height,width:this.opts.width})},bindEvents:function(){if(this.fixIE){A(window).bind("resize",this.mask.setSize)}A(window).bind("resize",this.center)},unbindEvents:function(){if(this.fixIE){A(window).unbind("resize",this.mask.setSize)}A(window).unbind("resize",this.center)},showMask:function(){},_center:function(){var B=(this.container.width()-A(this.dialog.container).width())/2;var C=((this.bh.height()-this.dialog.container.height())/2+Math.max(document.documentElement.scrollTop,document.body.scrollTop));this.dialog.container.css({left:B,top:C})},close:function(B){this.mask.hide();if(this.dialog.iframe){this.dialog.iframe.hide()}this.dialog.container.hide();this.unbindEvents();if(A.isFunction(this.fn.onClose)){this.fn.onClose.apply(this,[this.dialog])}}}})(jQuery);$.dragable=function(A){$.extend(this,A||{});if(!this.ele){return}this.triggerEle=this.triggerEle||this.ele;this.init()};(function(A){A.dragable.prototype={ele:null,triggerEle:null,constrainEle:null,constrain:null,lockX:false,lockY:false,_mouseX:null,_mouseY:null,init:function(){var B=this;A(this.ele).css({position:"absolute"});if(!this.constrainEle&&!this.constrain){this._getDocumentConstrain()}A(this.triggerEle).css("cursor","move").bind("mousedown",function(C){B._startDrag(C)});A(window).bind("resize",function(){B._getDocumentConstrain()})},_getDocumentConstrain:function(){var B=!this.ele.style.borderTopWidth.replace("px")?0:parseInt(this.ele.style.borderTopWidth.replace("px")),C=!this.ele.style.borderRightWidth.replace("px")?0:parseInt(this.ele.style.borderRightWidth.replace("px")),E=!this.ele.style.borderLeftWidth.replace("px")?0:parseInt(this.ele.style.borderLeftWidth.replace("px")),D=!this.ele.style.borderBottomWidth.replace("px")?0:parseInt(this.ele.style.borderBottomWidth.replace("px"));this.constrain=[0+B,document.body.clientWidth-A(this.ele).width()-C,document.body.clientHeight-A(this.ele).height()-D,0+E]},_setSelectable:function(B){if(!this.ele){return}document.body.unselectable=this.ele.unselectable=B?"off":"on";document.body.onselectstart=this.ele.onselectstart=function(){return B};if(document.selection&&document.selection.empty){document.selection.empty()}if(this.ele.style){this.ele.style.MozUserSelect=B?"":"none"}document.body.style.MozUserSelect=B?"":"none"},disableDrag:function(){},enableDrag:function(){},onDrag:function(){},onDrop:function(){},_startDrag:function(D){var C=D.data||{},B=this;var E={left:D.pageX-this.ele.offsetLeft,top:D.pageY-this.ele.offsetTop};var F=this.constrain||(function(H){var G=[H.offsetTop,H.offsetLeft+H.offsetWidth-A(B.ele).width()-parseInt(B.ele.style.borderRightWidth.replace("px"))-1,H.offsetTop+H.offsetHeight-A(B.ele).height()-parseInt(B.ele.style.borderBottomWidth.replace("px"))-1,H.offsetLeft];this.constrain=G;return G})(this.constrainEle);var C={offset:E,constrain:F};this._setSelectable(false);this.onDrag();A(document).bind("mouseup",function(G){B._endDrag(G)});A(document).bind("mousemove",C,function(G){B._drag(G)})},_drag:function(C){var B=C.data||{};var D={left:C.pageX-B.offset.left,top:C.pageY-B.offset.top};if(B.constrain[0]!=undefined&&D.top<B.constrain[0]){D.top=B.constrain[0]}if(B.constrain[2]!=undefined&&D.top>B.constrain[2]){D.top=B.constrain[2]}if(B.constrain[1]!=undefined&&D.left>B.constrain[1]){D.left=B.constrain[1]}if(B.constrain[3]!=undefined&&D.left<B.constrain[3]){D.left=B.constrain[3]}if(!this.lockX){this.ele.style.left=D.left+"px"}if(!this.lockY){this.ele.style.top=D.top+"px"}},_endDrag:function(B){A(document).unbind("mousemove");A(document).unbind("mouseup");this._setSelectable(true);this.onDrop()}}})(jQuery);
/* stepper */
(function(a){a.fn.stepper=function(e){e=a.extend({min:0,max:10,step:1,start:0,decimals:0,format:"",symbol:"",callback:null},e||{});e.pow=Math.pow(10,e.decimals);var d={BACK:8,TAB:9,LEFT:37,UP:38,RIGHT:39,DOWN:40,PGUP:33,PGDN:34,HOME:36,END:35,PERIOD:190,MINUS:109,NUMPAD_DECIMAL:110,NUMPAD_SUBTRACT:109};function g(k,h,j){var i=parseFloat(k.value);if(undefined==j||j){var l=Math.round(i*e.pow)%(e.step*e.pow);if(l!=0){i-=l/e.pow;if(h<0){h=0}}}i=parseFloat(i)+parseFloat(h);if(isNaN(i)){i=e.start}if(i<e.min){i=e.min}if(i>e.max){i=e.max}k.value=f(i);if(typeof e.callback=="function"){return e.callback()}}function f(h){h=Math.round(parseFloat(h)*e.pow)/e.pow;if(e.format=="percent"){return h+"%"}return h}function c(h){if(h>=96&&h<=105){h="NUMPAD"}switch(h){case d.TAB:case d.BACK:case d.LEFT:case d.RIGHT:case d.PERIOD:case d.MINUS:case d.NUMPAD_DECIMAL:case d.NUMPAD_SUBTRACT:case"NUMPAD":return true;default:return(/[0-9\-\.]/).test(String.fromCharCode(h))}}function b(j){var i=j.css("display");if(i!="none"&&i!==null&&!j.parents(":hidden").size()){return j.outerWidth()}var k=j.clone();k.css({position:"absolute",left:-999});k.appendTo(document.body);var h=k.outerWidth();k.remove();return h}return this.each(function(){var i=this;if(this.type!="text"){return}var h=a(this).addClass("stepper-input").attr("autocomplete","off"),k=b(h);var j=a('<span class="stepper-wrap"><span class="stepper-inner"></span></span>').css("width",k);h.wrap(j);var m=a('<button class="stepper-plus" type="button"></button>');var l=a('<button class="stepper-minus" type="button"></button>');h.after(m,l);if(!i.value){i.value=f(e.start)}h.bind("keydown.stepper",function(o){var n=(window.event?event.keyCode:(o.which?o.which:null));switch(n){case d.UP:case d.PGUP:g(i,e.step);break;case d.DOWN:case d.PGDN:g(i,-e.step);break}a("#hint").html(n);return c(n)}).bind("DOMMouseScroll mousewheel",function(n,o){if(n.wheelDelta){o=n.wheelDelta/120}if(n.detail){o=-n.detail/3}if(a.browser.opera){o=-n.wheelDelta}if(o>0){g(i,e.step)}else{if(o<0){g(i,-e.step)}}return false}).bind("blur",function(n){g(i,0,false)});m.bind("click",function(n){g(i,e.step)});l.bind("click",function(n){g(i,-e.step)})})}})(jQuery);
/* checkbox */
var _CHECKBOXES={};(function(A){A.checkbox=function(B){this._cfg=A.extend({},A.checkbox.defaultConfig,B||{});this.init()};A.checkbox.AUTO_ID=0;A.checkbox.defaultConfig={states:{normal:{value:0,cls:""},half:{value:0,cls:"checkbox-half"},checked:{value:1,cls:"checkbox-checked"}},baseCls:"checkbox",disableCls:"checkbox-disabled",name:"checkbox",css:{}};A.checkbox.prototype={_state:null,_cfg:null,_el:null,_input:null,_evt:["click","change"],_events:null,_disabled:false,init:function(){var D=this;this._el=A("<div></div>").addClass(this._cfg.baseCls);this._input=A('<input type="hidden" name="'+this._cfg.name+'" />');this._el.attr({_name:this._cfg.name,_jqcheckbox:"jqcheckbox"}).css(this._cfg.css);if(!this._cfg.id){this._cfg.id="checkbox-"+(++A.checkbox.AUTO_ID)}this.id=this._cfg.id;this._el.attr("id",this._cfg.id);_CHECKBOXES[this._cfg.id]=this;this._el.append(this._input);var C=[];for(var B in this._cfg.states){C.push(B)}if(this._cfg.replace){this.replace(this._cfg.replace)}else{if(this._cfg.appendTo){this.appendTo(this._cfg.appendTo)}}this._events={};this._el.bind("click",function(F){if(D._disabled){return}E();D.triggerEvent("click",[F])}).bind("mouseover",function(){D._el.addClass("checkbox-hover")}).bind("mouseout",function(){D._el.removeClass("checkbox-hover")}).bind("mousedown",function(){}).bind("mouseup",function(){});A('label[for="'+this._cfg.id+'"]').bind("click",function(F){F.preventDefault();return false}).bind("click",function(){D._el.click()}).bind("mouseover",function(){D._el.addClass("checkbox-hover")}).bind("mouseout",function(){D._el.removeClass("checkbox-hover")});this.state(C[0]);function E(){if(D._disabled){return}var F=C.indexOf(D.state());if(-1===F||C.length-1<=F){F=0}else{F++}D.state(C[F])}},setValue:function(C,E){var D;if(undefined==E&&typeof C=="object"){D=C}else{D={};D[C]=E}for(var B in D){if(undefined!==this._cfg.states[B]){this._cfg.states[B].value=D[B]}}return this},bind:function(B,C){if(typeof this._events[B]=="undefined"){this._events[B]=[]}if(typeof C=="function"){this._events[B].push(C)}return this},unbind:function(C,E){if(typeof this._events[C]=="undefined"){return}if(undefined===E){delete this._events[C];return this}for(var B=0,D=this._events[C].length;B<D;B++){if(E==this._events[C][B]){delete this._events[C][B]}}return this},triggerEvent:function(D,B){if(typeof this._events[D]=="undefined"){return this}if(!B){B=[]}for(var C=0,E=this._events[D].length;C<E;C++){this._events[D][C].apply(this,B)}return this},state:function(B){if(undefined===B){return this._state}else{if(typeof this._cfg.states[B]!="undefined"){this._setState(B)}return this}},disabled:function(){this._disabled=true;this._input.attr("disabled","disabled");this._el.addClass(this._cfg.disableCls)},enabled:function(){this._disabled=false;this._input.attr("disabled",false);this._el.removeClass(this._cfg.disableCls)},appendTo:function(B){this._el.appendTo(B)},replace:function(B){if(typeof B=="string"){B=A(B)}B.after(this._el);B.remove()},_setState:function(C){var B=this._cfg.states[C];if(this._state!=C){this._state=C;this._el.removeClass().addClass(this._cfg.baseCls+" "+B.cls);this._input.val(B.value);this.triggerEvent("change")}}};A.checkboxgroup=function(B){this.items=B||[]};A.checkboxgroup.prototype={items:null,disabled:function(){A.each(this.items,function(){this.disabled()})},enabled:function(){A.each(this.items,function(){this.enabled()})},state:function(B){A.each(this.items,function(){if(this._disabled){return}this.state(B)});return this},bind:function(B,C){A.each(this.items,function(){this.bind(B,C)});return this},unbind:function(B){A.each(this.items,function(){this.unbind(B)});return this},each:function(D){for(var B=0,C=this.items.length;B<C;B++){D.call(this.items[B])}return this},size:function(){return this.items?this.items.length:0}};if(undefined===Array.prototype.indexOf){Array.prototype.indexOf=function(C){for(var B=0,D=this.length;B<D;B++){if(C==this[B]){return B}}return -1}}})(jQuery);function getCheckbox(B,D,C){if(!C){C=$(document.body)}if(B!="id"){B="_"+B}var A=[];C.find('div[_jqcheckbox="jqcheckbox"]['+B+'="'+D+'"]').each(function(){A.push(_CHECKBOXES[this.id])});return new $.checkboxgroup(A)};
/* Messager init */
var Messager={_TPLS:{Window:'<div class="pop"><div class="pop_header"><strong></strong><a class="icon icon_close close"></a></div><div class="pop_body"></div></div>'},window:function(c){var e=c.id?$("#"+c.id):null,g={},f=null,b=Messager._TPLS.Window;if(null===e||!e.size()){e=$('<div class="messager"></div>')}if(c.form!==false){b=['<form method="post" action="?">',b,"</form>"].join("")}e.html(b);for(var a in c){switch(a){case"id":e.attr("id",c[a]);break;case"formid":e.find("form").attr("id",c[a]);break;case"formurl":case"action":e.find("form").attr("action",c[a]);break;case"body":e.find(".pop_body").html(c[a]);break;case"footer":var h=$('<div class="pop_footer"></div>');h.html(c[a]);e.find(".pop").append(h);break;case"title":e.find(".pop_header strong").text(c[a]);break;case"width":case"height":case"autoCloseTime":case"anims":case"onShow":case"onClose":case"showTimer":case"currTime":case"timeFrom":case"destroy":case"initClose":g[a]=c[a];break;case"closeable":if(false===c.closeable){e.find("div.pop_header a.close").remove()}break;case"init":f=c[a];break}}e.addClass("pop_wrap").css({position:"absolute",zIndex:"8000"}).appendTo(document.body);var d=e.messager(g);if(typeof(f)=="function"){f.call(e)}return d}};
/* Messager */
(function(a){a.messager=function(d){var c=this;this.opts=a.extend({},a.messager.defaults,d);if(!this.opts.applyTo){return}var b=a(this.opts.applyTo).css({zIndex:8001});this.dialog={container:b,header:b.find(".pop_header"),body:b.find(".pop_body"),footer:b.find(".pop_footer")};this.fn={show:this.opts.onShow,close:this.opts.onClose};this.dialog.container.hide().appendTo(document.body);if(this.opts.initClose){this.dialog.container.find("."+this.opts.closeCls).click(function(f){f.preventDefault();c.close()})}if(this.opts.autoCloseTime){setTimeout(function(){c.close()},this.opts.autoCloseTime)}if(this.opts.showTimer){if(this.opts.currTime!==null){c.setTime()}else{if(this.opts.timeFrom!==null){c.getServerTime()}}setInterval(function(){c.showTime(c.dialog.body.find("span.timer"))},1000)}this.dialog.container.css({height:this.opts.height,width:this.opts.width});return this};a.messager.prototype={show:function(){switch(this.opts.anims.type){case"slide":this.dialog.container.slideDown(this.opts.anims.speed);break;case"fade":this.dialog.container.fadeIn(this.opts.anims.speed);break;case"show":default:this.dialog.container.show(this.opts.anims.speed);break}this.position();if(a.isFunction(this.fn.show)){this.fn.show.apply(this,[this.dialog])}},close:function(){this.dialog.container.hide();if(this.opts.destroy){this.destroy()}this.rePosition();if(a.isFunction(this.fn.close)){this.fn.close.apply(this,[this.dialog])}},rePosition:function(){var c=this.getMsgSize()-1;if(c<0){return}var b=this;a("div.messager:visible").each(function(){if(c>=0){var d=b.opts.height*c;a(this).css({bottom:d})}c--})},position:function(){var b=0,c=this.getMsgSize()-1;if(c<0){c=0}b=this.opts.height*c;this.dialog.container.css({bottom:b,right:0})},setTime:function(){var b=this;if(b.opts.currTime===null){return}setInterval(function(){b.opts.currTime+=1},1000)},getServerTime:function(){var b=this;if(b.opts.timeFrom===null){return}a.ajax({type:"GET",dataType:"json",url:b.opts.timeFrom,success:function(c){if(c.success&&c.data&&c.data.currtime){b.opts.currTime=c.data.currtime;b.setTime()}},error:function(){}})},getTime:function(){if(this.opts.currTime===null){return new Date()}return new Date(this.opts.currTime*1000)},showTime:function(g){var c=this.getTime(),b=c.getHours(),d=c.getMinutes(),e=c.getSeconds(),f=parseInt(c.getMonth())+1;if(b<10){b="0"+b}if(d<10){d="0"+d}if(e<10){e="0"+e}g.html(c.getFullYear()+"-"+f+"-"+c.getDate()+" "+b+":"+d+":"+e)},destroy:function(){this.dialog.container.remove();return null},getMsgSize:function(){return a("div.messager:visible").size()}};a.fn.messager=function(b){if(!this.length){b&&b.debug&&window.console&&console.warn("nothing selected, can't open messager, returning nothing");return}var c=a.data(this[0],"messager");if(c){return c}c=new a.messager(a.extend(b,{applyTo:this[0],id:this[0].id}));a.data(this[0],"messager",c);return c};a.messager.defaults={id:"messager",width:380,height:176,form:false,title:"message",anims:{type:"fade",speed:600},autoCloseTime:null,onShow:null,onClose:null,init:null,closeCls:"close",showTimer:false,destroy:true,currTime:null,timeFrom:null,initClose:true}})(jQuery);
