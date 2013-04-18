/*!
 * xhEditor - WYSIWYG XHTML Editor
 * @requires jQuery v1.4.2
 *
 * @author Yanis.Wang<yanis.wang@gmail.com>
 * @site http://xheditor.com/
 * @licence LGPL(http://xheditor.com/license/lgpl.txt)
 *
 * @Version: 1.1.6 (build 110411)
 */
(function($,undefined){
if($.xheditor)return false;//防止JS重複加載
$.fn.xheditor=function(options)
{
	var arrSuccess=[];
	this.each(function(){
		if(!$.nodeName(this,'TEXTAREA'))return;
		if(options===false)//卸載
		{
			if(this.xheditor)
			{
				this.xheditor.remove();
				this.xheditor=null;
			}
		}
		else//初始化
		{
			if(!this.xheditor)
			{
				var tOptions=/({.*})/.exec($(this).attr('class'));
				if(tOptions)
				{
					try{tOptions=eval('('+tOptions[1]+')');}catch(ex){};
					options=$.extend({},tOptions,options );
				}
				var editor=new $.xheditor(this,options);
				if(editor.init())
				{
					this.xheditor=editor;
					arrSuccess.push(editor);
				}
				else editor=null;
			}
			else arrSuccess.push(this.xheditor);
		}
	});
	if(arrSuccess.length===0)arrSuccess=false;
	if(arrSuccess.length===1)arrSuccess=arrSuccess[0];
	return arrSuccess;
}
var xCount=0,browerVer=$.browser.version,isIE=$.browser.msie,isMozilla=$.browser.mozilla,isSafari=$.browser.safari,isOpera=$.browser.opera,bShowPanel=false,bClickCancel=true,bShowModal=false,bCheckEscInit=false;
var _jPanel,_jShadow,_jCntLine,_jPanelButton;
var jModal,jModalShadow,layerShadow,jOverlay,jHideSelect,onModalRemove;
var editorRoot;
$('script[src*=xheditor]').each(function(){
	var s=this.src;
	if(s.match(/xheditor[^\/]*\.js/i)){editorRoot=s.replace(/[\?#].*$/, '').replace(/(^|[\/\\])[^\/]*$/, '$1');return false;}
});

var specialKeys={ 27: 'esc', 9: 'tab', 32:'space', 13: 'enter', 8:'backspace', 145: 'scroll',
          20: 'capslock', 144: 'numlock', 19:'pause', 45:'insert', 36:'home', 46:'del',
          35:'end', 33: 'pageup', 34:'pagedown', 37:'left', 38:'up', 39:'right',40:'down',
          112:'f1',113:'f2', 114:'f3', 115:'f4', 116:'f5', 117:'f6', 118:'f7', 119:'f8', 120:'f9', 121:'f10', 122:'f11', 123:'f12' };
var itemColors=['#FFFFFF','#CCCCCC','#C0C0C0','#999999','#666666','#333333','#000000','#FFCCCC','#FF6666','#FF0000','#CC0000','#990000','#660000','#330000','#FFCC99','#FF9966','#FF9900','#FF6600','#CC6600','#993300','#663300','#FFFF99','#FFFF66','#FFCC66','#FFCC33','#CC9933','#996633','#663333','#FFFFCC','#FFFF33','#FFFF00','#FFCC00','#999900','#666600','#333300','#99FF99','#66FF99','#33FF33','#33CC00','#009900','#006600','#003300','#99FFFF','#33FFFF','#66CCCC','#00CCCC','#339999','#336666','#003333','#CCFFFF','#66FFFF','#33CCFF','#3366FF','#3333FF','#000099','#000066','#CCCCFF','#9999FF','#6666CC','#6633FF','#6600CC','#333399','#330099','#FFCCFF','#FF99FF','#CC66CC','#CC33CC','#993399','#663366','#330033'];
var arrBlocktag=[{n:'p',t:'普通段落'},{n:'h1',t:'標題1'},{n:'h2',t:'標題2'},{n:'h3',t:'標題3'},{n:'h4',t:'標題4'},{n:'h5',t:'標題5'},{n:'h6',t:'標題6'},{n:'pre',t:'已編排格式'},{n:'address',t:'地址'}];
var arrFontname=[{n:'新細明體',c:'PMingLiu'},{n:'細明體',c:'mingliu'},{n:'標楷體',c:'DFKai-SB'},{n:'微軟正黑體',c:'Microsoft JhengHei'},{n:'Arial'},{n:'Arial Narrow'},{n:'Arial Black'},{n:'Comic Sans MS'},{n:'Courier New'},{n:'System'},{n:'Times New Roman'},{n:'Tahoma'},{n:'Verdana'}];
var arrFontsize=[{n:'xx-small',wkn:'x-small',s:'8pt',t:'極小'},{n:'x-small',wkn:'small',s:'10pt',t:'特小'},{n:'small',wkn:'medium',s:'12pt',t:'小'},{n:'medium',wkn:'large',s:'14pt',t:'中'},{n:'large',wkn:'x-large',s:'18pt',t:'大'},{n:'x-large',wkn:'xx-large',s:'24pt',t:'特大'},{n:'xx-large',wkn:'-webkit-xxx-large',s:'36pt',t:'極大'}];
var menuAlign=[{s:'靠左對齊',v:'justifyleft'},{s:'置中',v:'justifycenter'},{s:'靠右對齊',v:'justifyright'},{s:'左右對齊',v:'justifyfull'}],menuList=[{s:'數字列表',v:'insertOrderedList'},{s:'符號列表',v:'insertUnorderedList'}];
var htmlPastetext='<div>使用鍵盤快捷鍵(Ctrl+V)把內容貼上到方框裡，按 確定</div><div><textarea id="xhePastetextValue" wrap="soft" spellcheck="false" style="width:300px;height:100px;" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="確定" /></div>';
var htmlLink='<div>鏈接地址: <input type="text" id="xheLinkUrl" value="http://" class="xheText" /></div><div>打開方式: <select id="xheLinkTarget"><option selected="selected" value="">預設</option><option value="_blank">新窗口</option><option value="_self">當前窗口</option><option value="_parent">父窗口</option></select></div><div style="display:none">鏈接文字: <input type="text" id="xheLinkText" value="" class="xheText" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="確定" /></div>';
var htmlImg='<div>圖片文件: <input type="text" id="xheImgUrl" value="http://" class="xheText" /></div><div>替換文本: <input type="text" id="xheImgAlt" /></div><div>對齊方式: <select id="xheImgAlign"><option selected="selected" value="">預設</option><option value="left">靠左對齊</option><option value="right">靠右對齊</option><option value="top">頂端</option><option value="middle">置中</option><option value="baseline">基線</option><option value="bottom">底邊</option></select></div><div>寬度高度: <input type="text" id="xheImgWidth" style="width:40px;" /> x <input type="text" id="xheImgHeight" style="width:40px;" /></div><div>邊框大小: <input type="text" id="xheImgBorder" style="width:40px;" /></div><div>水平間距: <input type="text" id="xheImgHspace" style="width:40px;" /> 垂直間距: <input type="text" id="xheImgVspace" style="width:40px;" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="確定" /></div>';
var htmlFlash='<div>動畫文件: <input type="text" id="xheFlashUrl" value="http://" class="xheText" /></div><div>寬度高度: <input type="text" id="xheFlashWidth" style="width:40px;" value="480" /> x <input type="text" id="xheFlashHeight" style="width:40px;" value="400" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="確定" /></div>';
var htmlMedia='<div>媒體文件: <input type="text" id="xheMediaUrl" value="http://" class="xheText" /></div><div>寬度高度: <input type="text" id="xheMediaWidth" style="width:40px;" value="480" /> x <input type="text" id="xheMediaHeight" style="width:40px;" value="400" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="確定" /></div>';
var htmlTable='<div>行數列數: <input type="text" id="xheTableRows" style="width:40px;" value="3" /> x <input type="text" id="xheTableColumns" style="width:40px;" value="2" /></div><div>標題單元: <select id="xheTableHeaders"><option selected="selected" value="">無</option><option value="row">第一行</option><option value="col">第一列</option><option value="both">第一行和第一列</option></select></div><div>寬度高度: <input type="text" id="xheTableWidth" style="width:40px;" value="200" /> x <input type="text" id="xheTableHeight" style="width:40px;" value="" /></div><div>邊框大小: <input type="text" id="xheTableBorder" style="width:40px;" value="1" /></div><div>表格間距: <input type="text" id="xheTableCellSpacing" style="width:40px;" value="1" /> 表格填充: <input type="text" id="xheTableCellPadding" style="width:40px;" value="1" /></div><div>對齊方式: <select id="xheTableAlign"><option selected="selected" value="">預設</option><option value="left">靠左對齊</option><option value="center">置中</option><option value="right">靠右對齊</option></select></div><div>表格標題: <input type="text" id="xheTableCaption" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="確定" /></div>';
var htmlAbout='<div style="font:12px Arial;width:245px;word-wrap:break-word;word-break:break-all;"><p><span style="font-size:20px;color:#1997DF;">xhEditor</span><br />v1.1.6 (build 110411)</p><p>xhEditor是基於jQuery開發的跨平台輕量XHTML編輯器，基於<a href="http://www.gnu.org/licenses/lgpl.html" target="_blank">LGPL</a>開源協議發佈。</p><p>Copyright &copy; <a href="http://xheditor.com/" target="_blank">xhEditor.com</a>. All rights reserved.</p></div>';
var itemEmots={'default':{name:'預設',width:24,height:24,line:7,list:{'smile':'微笑','tongue':'吐舌頭','titter':'偷笑','laugh':'大笑','sad':'難過','wronged':'委屈','fastcry':'快哭了','cry':'哭','wail':'大哭','mad':'生氣','knock':'敲打','curse':'罵人','crazy':'抓狂','angry':'發火','ohmy':'驚訝','awkward':'尷尬','panic':'驚恐','shy':'害羞','cute':'可憐','envy':'羨慕','proud':'得意','struggle':'奮鬥','quiet':'安靜','shutup':'閉嘴','doubt':'疑問','despise':'鄙視','sleep':'睡覺','bye':'再見'}}};
var arrTools={Cut:{t:'剪下 (Ctrl+X)'},Copy:{t:'複製 (Ctrl+C)'},Paste:{t:'貼上 (Ctrl+V)'},Pastetext:{t:'貼上文本',h:isIE?0:1},Blocktag:{t:'段落標籤',h:1},Fontface:{t:'字型',h:1},FontSize:{t:'字型大小',h:1},Bold:{t:'粗體 (Ctrl+B)',s:'Ctrl+B'},Italic:{t:'斜體 (Ctrl+I)',s:'Ctrl+I'},Underline:{t:'底線 (Ctrl+U)',s:'Ctrl+U'},Strikethrough:{t:'刪除線'},FontColor:{t:'字型顏色',h:1},BackColor:{t:'背景顏色',h:1},SelectAll:{t:'全選 (Ctrl+A)'},Removeformat:{t:'刪除文字格式'},Align:{t:'對齊',h:1},List:{t:'列表',h:1},Outdent:{t:'減少縮排 (Shift+Tab)',s:'Shift+Tab'},Indent:{t:'增加縮排 (Tab)',s:'Tab'},Link:{t:'超連結 (Ctrl+K)',s:'Ctrl+K',h:1},Unlink:{t:'取消超連結'},Img:{t:'圖片',h:1},Flash:{t:'Flash動畫',h:1},Media:{t:'多媒體文件',h:1},Emot:{t:'表情',s:'ctrl+e',h:1},Table:{t:'表格',h:1},Source:{t:'原始碼'},Preview:{t:'預覽'},Print:{t:'打印 (Ctrl+P)',s:'Ctrl+P'},Fullscreen:{t:'全螢幕編輯 (Esc)',s:'Esc'},About:{t:'關於 xhEditor'}};
var toolsThemes={
	mini:'Bold,Italic,Underline,Strikethrough,|,Align,List,|,Link,Img',
	simple:'Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,|,Align,List,Outdent,Indent,|,Link,Img,Emot',
	full:'Cut,Copy,Paste,Pastetext,|,Blocktag,Fontface,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,SelectAll,Removeformat,|,Align,List,Outdent,Indent,|,Link,Unlink,Img,Flash,Media,Emot,Table,|,Source,Preview,Print,Fullscreen'};
toolsThemes.mfull=toolsThemes.full.replace(/\|(,Align)/i,'/$1');
var arrDbClick={'a':'Link','img':'Img','embed':'Embed'},uploadInputname='filedata';
var arrEntities={'<':'&lt;','>':'&gt;','"':'&quot;','®':'&reg;','©':'&copy;'};//實體
var regEntities=/[<>"®©]/g;

$.xheditor=function(textarea,options)
{
	var defaults={skin:'default',tools:'full',clickCancelDialog:true,linkTag:false,internalScript:false,inlineScript:false,internalStyle:true,inlineStyle:true,showBlocktag:false,forcePtag:true,upLinkExt:"zip,rar,txt",upImgExt:"jpg,jpeg,gif,png",upFlashExt:"swf",upMediaExt:"wmv,avi,wma,mp3,mid",modalWidth:350,modalHeight:220,modalTitle:true,defLinkText:'點擊打開鏈接',layerShadow:3,emotMark:false,upBtnText:'上傳',cleanPaste:2,hoverExecDelay:100,html5Upload:true,upMultiple:99};
	var _this=this,_text=textarea,_jText=$(_text),_jForm=_jText.closest('form'),_jTools,_jArea,_win,_jWin,_doc,_jDoc;
	var bookmark;
	var bInit=false,bSource=false,bFullscreen=false,bCleanPaste=false,outerScroll,bShowBlocktag=false,sLayoutStyle='',ev=null,timer,bDisableHoverExec=false,bQuickHoverExec=false;
	var lastPoint=null,lastAngle=null;//鼠標懸停顯示
	var editorHeight=0;
	var settings=_this.settings=$.extend({},defaults,options );
	
	var plugins=settings.plugins,strPlugins=[];
	if(plugins)
	{
		arrTools=$.extend({},arrTools,plugins);
		$.each(plugins,function(n){strPlugins.push(n);});
		strPlugins=strPlugins.join(',');
	}
	if(settings.tools.match(/^\s*(m?full|simple|mini)\s*$/i))
	{
		var toolsTheme=toolsThemes[$.trim(settings.tools)];
		settings.tools=(settings.tools.match(/m?full/i)&&plugins)?toolsTheme.replace('Table','Table,'+strPlugins):toolsTheme;//插件接在full的Table後面
	}
	if(!settings.tools.match(/(^|,)\s*About\s*(,|$)/i))settings.tools+=',About';
	settings.tools=settings.tools.split(',');
	if(settings.editorRoot)editorRoot=settings.editorRoot;
	editorRoot=getLocalUrl(editorRoot,'abs');
	if(settings.urlBase)settings.urlBase=getLocalUrl(settings.urlBase,'abs');

	//基本控件名
	var idCSS='xheCSS_'+settings.skin,idContainer='xhe'+xCount+'_container',idTools='xhe'+xCount+'_Tool',idIframeArea='xhe'+xCount+'_iframearea',idIframe='xhe'+xCount+'_iframe',idFixFFCursor='xhe'+xCount+'_fixffcursor';
	var headHTML='',bodyClass='',skinPath=editorRoot+'xheditor_skin/'+settings.skin+'/',arrEmots=itemEmots,urlType=settings.urlType,urlBase=settings.urlBase,emotPath=settings.emotPath,emotPath=emotPath?emotPath:editorRoot+'xheditor_emot/',selEmotGroup='';
	arrEmots=$.extend({},arrEmots,settings.emots);
	emotPath=getLocalUrl(emotPath,'rel',urlBase?urlBase:null);//返回最短表情路徑

	bShowBlocktag=settings.showBlocktag;
	if(bShowBlocktag)bodyClass+=' showBlocktag';

	var arrShortCuts=[];
	this.init=function()
	{
		//加載樣式表
		if($('#'+idCSS).length===0)$('head').append('<link id="'+idCSS+'" rel="stylesheet" type="text/css" href="'+skinPath+'ui.css" />');
		//初始化編輯器
		var cw = settings.width || _text.style.width || _jText.outerWidth();
		editorHeight = settings.height || _text.style.height || _jText.outerHeight();
		if(is(editorHeight,'string'))editorHeight=editorHeight.replace(/[^\d]+/g,'');
		if(cw<=0||editorHeight<=0)//禁止對隱藏區域裡的textarea初始化編輯器
		{
			alert('當前textarea處於隱藏狀態，請將之顯示後再初始化xhEditor，或者直接設置textarea的width和height樣式');
			return false;
		}
		if(/^[0-9\.]+$/i.test(''+cw))cw+='px';
		//編輯器CSS背景
		var editorBackground=settings.background || _text.style.background;

		//工具欄內容初始化
		var arrToolsHtml=['<span class="xheGStart"/>'],tool,cn,regSeparator=/\||\//i;
		$.each(settings.tools,function(i,n)
		{
			if(n.match(regSeparator))arrToolsHtml.push('<span class="xheGEnd"/>');
			if(n==='|')arrToolsHtml.push('<span class="xheSeparator"/>');
			else if(n==='/')arrToolsHtml.push('<br />');
			else
			{
				tool=arrTools[n];
				if(!tool)return;
				if(tool.c)cn=tool.c;
				else cn='xheIcon xheBtn'+n;
				arrToolsHtml.push('<span><a href="javascript:void(0);" title="'+tool.t+'" name="'+n+'" class="xheButton xheEnabled" tabindex="-1"><span class="'+cn+'" unselectable="on" /></a></span>');
				if(tool.s)_this.addShortcuts(tool.s,n);
			}
			if(n.match(regSeparator))arrToolsHtml.push('<span class="xheGStart"/>');
		});
		arrToolsHtml.push('<span class="xheGEnd"/><br />');

		_jText.after($('<input type="text" id="'+idFixFFCursor+'" style="position:absolute;display:none;" /><span id="'+idContainer+'" class="xhe_'+settings.skin+'" style="display:none"><table cellspacing="0" cellpadding="0" class="xheLayout" style="width:'+cw+';height:'+editorHeight+'px;"><tbody><tr><td id="'+idTools+'" class="xheTool" unselectable="on" style="height:1px;"></td></tr><tr><td id="'+idIframeArea+'" class="xheIframeArea"><iframe frameborder="0" id="'+idIframe+'" src="javascript:;" style="width:100%;"></iframe></td></tr></tbody></table></span>'));
		_jTools=$('#'+idTools);_jArea=$('#'+idIframeArea);
		
		headHTML='<meta content="text/html; charset=UTF-8" http-equiv="Content-Type"/><link rel="stylesheet" href="'+skinPath+'iframe.css"/>';
		var loadCSS=settings.loadCSS;
		if(loadCSS)
		{
			if(is(loadCSS,'array'))for(var i in loadCSS)headHTML+='<link rel="stylesheet" href="'+loadCSS[i]+'"/>';
			else
			{
				if(loadCSS.match(/\s*<style(\s+[^>]*?)?>[\s\S]+?<\/style>\s*/i))headHTML+=loadCSS;
				else headHTML+='<link rel="stylesheet" href="'+loadCSS+'"/>';
			}
		}
		
		var iframeHTML='<html><head>'+headHTML;
		if(editorBackground)iframeHTML+='<style>body{background:'+editorBackground+';}</style>';
		iframeHTML+='</head><body spellcheck="false" class="editMode'+bodyClass+'"></body></html>';
		_this.win=_win=$('#'+idIframe)[0].contentWindow;
		_jWin=$(_win);
		try{
			this.doc=_doc = _win.document;_jDoc=$(_doc);
			_doc.open();
			_doc.write(iframeHTML);
			_doc.close();
			if(isIE)_doc.body.contentEditable='true';
			else _doc.designMode = 'On';
		}catch(e){}
		setTimeout(setOpts,300);
		_this.setSource();
		_win.setInterval=null;//針對jquery 1.3無法操作iframe window問題的hack

		//添加工具欄
		_jTools.append(arrToolsHtml.join('')).bind('mousedown contextmenu',returnFalse).mousedown(function(event)
		{
			var jButton=$(event.target).closest('a');
			if(jButton.is('.xheEnabled'))
			{
				ev=event;
				_this.exec(jButton.attr('name'));
			}
			return false;
		});
		_jTools.find('.xheButton').hover(function(event){//鼠標懸停執行
			var jButton=$(this),delay=settings.hoverExecDelay;
			var tAngle=lastAngle;lastAngle=null;
			if(delay===-1||bDisableHoverExec||!jButton.is('.xheEnabled'))return false;
			if(tAngle&&tAngle>10)//檢測誤操作
			{
				bDisableHoverExec=true;
				setTimeout(function(){bDisableHoverExec=false;},100);
				return false;
			}
			var cmd=jButton.attr('name'),bHover=arrTools[cmd].h===1;
			if(!bHover)
			{
				_this.hidePanel();//移到非懸停按鈕上隱藏面板
				return false;
			}
			if(bQuickHoverExec)delay=0;
			if(delay>=0)timer=setTimeout(function(){
				ev=event;
				lastPoint={x:ev.clientX,y:ev.clientY};
				_this.exec(cmd);
			},delay);
		},function(event){lastPoint=null;if(timer)clearTimeout(timer);}).mousemove(function(event){
			if(lastPoint)
			{
				var diff={x:event.clientX-lastPoint.x,y:event.clientY-lastPoint.y};
				if(Math.abs(diff.x)>1||Math.abs(diff.y)>1)
				{
					if(diff.x>0&&diff.y>0)
					{
						var tAngle=Math.round(Math.atan(diff.y/diff.x)/0.017453293);
						if(lastAngle)lastAngle=(lastAngle+tAngle)/2
						else lastAngle=tAngle;
					}
					else lastAngle=null;
					lastPoint={x:event.clientX,y:event.clientY};
				}
			}
		});
		//初始化面板
		_jPanel=$('#xhePanel');
		_jShadow=$('#xheShadow');
		_jCntLine=$('#xheCntLine');
		if(_jPanel.length===0)
		{
			_jPanel=$('<div id="xhePanel"></div>').mousedown(function(ev){ev.stopPropagation()});
			_jShadow=$('<div id="xheShadow"></div>');
			_jCntLine=$('<div id="xheCntLine"></div>');
			setTimeout(function(){
				$(document.body).append(_jPanel).append(_jShadow).append(_jCntLine);
			},10);
		}

		//切換顯示區域
		$('#'+idContainer).show();
		_jText.hide();
		_jArea.css('height',editorHeight-_jTools.outerHeight());
		if(isIE&browerVer<8)setTimeout(function(){_jArea.css('height',editorHeight-_jTools.outerHeight());},1);

		//綁定內核事件
		_jText.focus(_this.focus);
		_jForm.submit(saveResult).bind('reset', loadReset);
		$(window).bind('unload beforeunload',saveResult).bind('resize',fixFullHeight);
		$(document).mousedown(clickCancelPanel);
		if(!bCheckEscInit){$(document).keydown(checkEsc);bCheckEscInit=true;}
		_jWin.focus(function(){if(settings.focus)settings.focus();}).blur(function(){if(settings.blur)settings.blur();});
		if(isSafari)_jWin.click(fixAppleSel);
		_jDoc.mousedown(clickCancelPanel).keydown(checkShortcuts).keypress(forcePtag).dblclick(checkDblClick).bind('mousedown click',function(ev){_jText.trigger(ev.type);});
		if(isIE)
		{
			//IE控件上Backspace會導致頁面後退
			_jDoc.keydown(function(ev){var rng=_this.getRng();if(ev.which===8&&rng.item){$(rng.item(0)).remove();return false;}});
			//修正IE拖動img大小不更新width和height屬性值的問題
			function fixResize(ev)
			{
				var jImg=$(ev.target),v;
				if(v=jImg.css('width'))jImg.css('width','').attr('width',v.replace(/[^0-9%]+/g, ''));
				if(v=jImg.css('height'))jImg.css('height','').attr('height',v.replace(/[^0-9%]+/g, ''));
			}
			_jDoc.bind('controlselect',function(ev){
				ev=ev.target;if(!$.nodeName(ev,'IMG'))return;
				$(ev).unbind('resizeend',fixResize).bind('resizeend',fixResize);
			});	
		}
		var jBody=$(_doc.documentElement);
		//自動清理貼上內容
		if(isOpera)jBody.bind('keydown',function(e){if(e.ctrlKey&&e.which===86)cleanPaste();});
		else jBody.bind('paste',cleanPaste);
		//禁用編輯區域的瀏覽器預設右鍵菜單
		if(settings.disableContextmenu)jBody.bind('contextmenu',returnFalse);
		//HTML5編輯區域直接拖放上傳
		if(settings.html5Upload)jBody.bind('dragenter dragover',function(ev){var types;if((types=ev.originalEvent.dataTransfer.types)&&$.inArray('Files', types)!==-1)return false;}).bind('drop',function(ev){
			var dataTransfer=ev.originalEvent.dataTransfer,fileList;
			if(dataTransfer&&(fileList=dataTransfer.files)&&fileList.length>0){
				var i,cmd,arrCmd=['Link','Img','Flash','Media'],arrExt=[],strExt;
				for(i in arrCmd){
					cmd=arrCmd[i];
					if(settings['up'+cmd+'Url']&&settings['up'+cmd+'Url'].match(/^[^!].*/i))arrExt.push(cmd+':,'+settings['up'+cmd+'Ext']);//允許上傳
				}
				if(arrExt.length===0)return false;//禁止上傳
				else strExt=arrExt.join(',');
				function getCmd(fileList){
					var match,fileExt,cmd;
					for(i=0;i<fileList.length;i++){
						fileExt=fileList[i].fileName.replace(/.+\./,'');
						if(match=strExt.match(new RegExp('(\\w+):[^:]*,'+fileExt+'(?:,|$)','i'))){
							if(!cmd)cmd=match[1];
							else if(cmd!==match[1])return 2;
						}
						else return 1;
					}
					return cmd;					
				}
				cmd=getCmd(fileList);
				if(cmd===1)alert('上傳文件的擴展名必需為：'+strExt.replace(/\w+:,/g,''));
				else if(cmd===2)alert('每次只能拖放上傳同一類型文件');
				else if(cmd){
					_this.startUpload(fileList,settings['up'+cmd+'Url'],'*',function(arrMsg){
						var arrUrl=[],msg,onUpload=settings.onUpload;
						if(onUpload)onUpload(arrMsg);//用戶上傳回調
						for(i in arrMsg){
							msg=arrMsg[i];
							url=is(msg,'string')?msg:msg.url;
							if(url.substr(0,1)==='!')url=url.substr(1);
							arrUrl.push(url);
						}
						_this.exec(cmd);
						$('#xhe'+cmd+'Url').val(arrUrl.join(' '));
						$('#xheSave').click();
					});
				}
				return false;
			}
		});

		//添加用戶快捷鍵
		var shortcuts=settings.shortcuts;
		if(shortcuts)$.each(shortcuts,function(key,func){_this.addShortcuts(key,func);});

		xCount++;
		bInit=true;

		if(settings.fullscreen)_this.toggleFullscreen();
		else if(settings.sourceMode)setTimeout(_this.toggleSource,20);
		return true;
	}
	this.remove=function()
	{
		_this.hidePanel();
		saveResult();//卸載前同步最新內容到textarea
		
		//取消綁定事件
		_jText.unbind('focus',_this.focus);
		_jForm.unbind('submit',saveResult).unbind('reset', loadReset);
		$(window).unbind('unload beforeunload',saveResult).unbind('resize',fixFullHeight);
		$(document).unbind('mousedown',clickCancelPanel);

		$('#'+idContainer).remove();
		$('#'+idFixFFCursor).remove();
		
		_jText.show();
		bInit=false;
	}
	this.saveBookmark=function(){
		if(!bSource){
			var rng=_this.getRng();
			rng=rng.cloneRange?rng.cloneRange():rng;
			bookmark={'top':_jWin.scrollTop(),'rng':rng};
		}
	}
	this.loadBookmark=function()
	{
		if(bSource||!bookmark)return;
		_this.focus();
		var rng=bookmark.rng;
		if(isIE)rng.select();
		else
		{
			var sel=_this.getSel();
			sel.removeAllRanges();
			sel.addRange(rng);
		}
		_jWin.scrollTop(bookmark.top);
		bookmark=null;
	}
	this.focus=function()
	{
		if(!bSource)(isIE?_jDoc:_jWin).focus();
		else $('#sourceCode',_doc).focus();
		if(isIE){
			var rng=_this.getRng();
			if(rng.parentElement&&rng.parentElement().ownerDocument!==_doc)_this.setCursorFirst();//修正IE初始焦點問題
		}
		return false;
	}
	this.setCursorFirst=function(firstBlock)
	{
		_win.scrollTo(0,0);
		var rng=_this.getRng(true),_body=_doc.body,firstNode=_body,firstTag;
		if(firstBlock&&firstNode.firstChild&&(firstTag=firstNode.firstChild.tagName)&&firstTag.match(/^p|div|h[1-6]$/i))firstNode=_body.firstChild;
		isIE?rng.moveToElementText(firstNode):rng.setStart(firstNode,0);
		rng.collapse(true);
		if(isIE)rng.select();
		else{var sel=_this.getSel();sel.removeAllRanges();sel.addRange(rng);}
	}
	this.getSel=function()
	{
		return _doc.selection ? _doc.selection : _win.getSelection();
	}
	this.getRng=function(bNew)
	{
		var sel,rng;
		try{
			if(!bNew){
				sel=_this.getSel();
				rng = sel.createRange ? sel.createRange() : sel.rangeCount > 0?sel.getRangeAt(0):null;
			}
			if(!rng)rng = _doc.body.createTextRange?_doc.body.createTextRange():_doc.createRange();
		}catch (ex){}
		return rng;
	}
	this.getParent=function(tag)
	{
		var rng=_this.getRng(),p;
		if(!isIE)
		{
			p = rng.commonAncestorContainer;
			if(!rng.collapsed)if(rng.startContainer === rng.endContainer&&rng.startOffset - rng.endOffset < 2&&rng.startContainer.hasChildNodes())p = rng.startContainer.childNodes[rng.startOffset];
		}
		else p=rng.item?rng.item(0):rng.parentElement();
		tag=tag?tag:'*';p=$(p);
		if(!p.is(tag))p=$(p).closest(tag);
		return p;
	}
	this.getSelect=function(format)
	{
		var sel=_this.getSel(),rng=_this.getRng(),isCollapsed=true;
		if (!rng || rng.item)isCollapsed=false
		else isCollapsed=!sel || rng.boundingWidth === 0 || rng.collapsed;
		if(format==='text')return isCollapsed ? '' : (rng.text || (sel.toString ? sel.toString() : ''));
		var sHtml;
		if(rng.cloneContents)
		{
			var tmp=$('<div></div>'),c;
			c = rng.cloneContents();
			if(c)tmp.append(c);
			sHtml=tmp.html();
		}
		else if(is(rng.item))sHtml=rng.item(0).outerHTML;
		else if(is(rng.htmlText))sHtml=rng.htmlText;
		else sHtml=rng.toString();
		if(isCollapsed)sHtml='';
		sHtml=_this.processHTML(sHtml,'read');
		sHtml=_this.cleanHTML(sHtml);
		sHtml=_this.formatXHTML(sHtml);
		return sHtml;
	}
	this.pasteHTML=function(sHtml,bStart)
	{
		if(bSource)return false;
		_this.focus();
		sHtml=_this.processHTML(sHtml,'write');
		var sel=_this.getSel(),rng=_this.getRng();
		if(bStart!==undefined)//非覆蓋式插入
		{
			if(rng.item)
			{
				var item=rng.item(0);
				rng=_this.getRng(true);
				rng.moveToElementText(item);
				rng.select();
			}
			rng.collapse(bStart);
		}
		sHtml+='<'+(isIE?'img':'span')+' id="_xhe_temp" width="0" height="0" />';
		if(rng.insertNode)
		{
			rng.deleteContents();
			rng.insertNode(rng.createContextualFragment(sHtml));
		}
		else
		{
			if(sel.type.toLowerCase()==='control'){sel.clear();rng=_this.getRng();};
			rng.pasteHTML(sHtml);
		}
		var jTemp=$('#_xhe_temp',_doc),temp=jTemp[0];
		if(isIE)
		{
			rng.moveToElementText(temp);
			rng.select();
		}
		else
		{
			rng.selectNode(temp); 
			sel.removeAllRanges();
			sel.addRange(rng);
		}
		jTemp.remove();
	}
	this.pasteText=function(text,bStart)
	{
		if(!text)text='';
		text=_this.domEncode(text);
		text = text.replace(/\r?\n/g, '<br />');
		_this.pasteHTML(text,bStart);
	}
	this.appendHTML=function(sHtml)
	{
		if(bSource)return false;
		_this.focus();
		sHtml=_this.processHTML(sHtml,'write');
		$(_doc.body).append(sHtml);
	}
	this.domEncode=function(text)
	{
		return text.replace(regEntities,function(c){return arrEntities[c];});
	}
	this.setSource=function(sHtml)
	{
		bookmark=null;
		if(typeof sHtml!=='string'&&sHtml!=='')sHtml=_text.value;
		if(bSource)$('#sourceCode',_doc).val(sHtml);
		else
		{
			if(settings.beforeSetSource)sHtml=settings.beforeSetSource(sHtml);
			sHtml=_this.cleanHTML(sHtml);
			sHtml=_this.formatXHTML(sHtml);
			sHtml=_this.processHTML(sHtml,'write');
			if(isIE){//修正IE會刪除可視內容前的script,style,<!--
				_doc.body.innerHTML='<img id="_xhe_temp" width="0" height="0" />'+sHtml+'\n';//修正IE會刪除&符號後面代碼的問題
				$('#_xhe_temp',_doc).remove();
			}
			else _doc.body.innerHTML=sHtml;
		}
	}
	this.processHTML=function(sHtml,mode)
	{
		var appleClass=' class="Apple-style-span"';
		if(mode==='write')
		{//write
			sHtml=sHtml.replace(/(<(\/?)(\w+))((?:\s+[\w-:]+\s*=\s*(?:"[^"]*"|'[^']*'|[^>\s]+))*)\s*((\/?)>)/g,function(all,left,end1,tag,attr,right,end2){
				tag=tag.toLowerCase();
				//編輯狀態統一轉為strike
				if(isMozilla){
					if(tag==='strong')tag='b';
					else if(tag==='em')tag='i';
				}
				else if(isSafari){
					if(tag==='strong'){tag='span';if(!end1)attr+=appleClass+' style="font-weight: bold;"';}
					else if(tag==='em'){tag='span';if(!end1)attr+=appleClass+' style="font-style: italic;"';}
					else if(tag==='u'){tag='span';if(!end1)attr+=appleClass+' style="text-decoration: underline;"';}
					else if(tag==='strike'){tag='span';if(!end1)attr+=appleClass+' style="text-decoration: line-through;"';}
				}
				var emot,tableBorder;
				if(tag==='del')tag='strike';
				else if(tag==='img'){
					//恢復emot
					attr=attr.replace(/\s+emot\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/i,function(all,v){
						emot=v.match(/^(["']?)(.*)\1/)[2];
						emot=emot.split(',');
						if(!emot[1]){emot[1]=emot[0];emot[0]=''}
						if(emot[0]==='default')emot[0]='';
						return settings.emotMark?all:'';
					});
				}
				else if(tag==='a'&&end2)right='></a>';
				else if(tag==='table'&&!end1){
					var tb=attr.match(/\s+border\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/i);
					if(!tb||tb[1].match(/^(["']?)\s*0\s*\1$/))tableBorder='xhe-border';
				}
				
				var bAppleClass;
				attr=attr.replace(/\s+([\w-:]+)\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/g,function(all,n,v){
					n=n.toLowerCase();
					v=v.match(/^(["']?)(.*)\1/)[2];
					aft='';//尾部增加屬性
					if(isIE&&n.match(/^(disabled|checked|readonly|selected)$/)&&v.match(/^(false|0)$/i))return '';
					//恢復emot
					if(tag==='img'&&emot&&n==='src')return '';
					//保存屬性值:src,href
					if(n.match(/^(src|href)$/)){
						aft=' _xhe_'+n+'="'+v+'"';
						if(urlBase)v=getLocalUrl(v,'abs',urlBase);
					}
					//顯示table邊框
					if(tableBorder&&n==='class'){
						v+=' '+tableBorder;
						tableBorder='';
					}
					//處理Safari style值
					if(isSafari&&n==='style'){
						v=v.replace(/(^|;)\s*(font-size)\s*:\s*([a-z-]+)\s*(;|$)/i,function(all,left,n,v,right){
							var t,s;
							for(var i=0;i<arrFontsize.length;i++)
							{
								t=arrFontsize[i];
								if(v===t.n){s=t.wkn;break;}
							}
							return left+n+':'+s+right;
						});
						if(tag==='span'&&v.match(/(^|;)\s*(font-family|font-size|color|background-color)\s*:\s*[^;]+\s*(;|$)/i))bAppleClass=true;
					}
					return ' '+n+'="'+v+'"'+aft;
				});
				
				//恢復emot
				if(emot){
					var url=emotPath+(emot[0]?emot[0]:'default')+'/'+emot[1]+'.gif';
					attr+=' src="'+url+'" _xhe_src="'+url+'"';
				}
				if(bAppleClass)attr+=appleClass;
				if(tableBorder)attr+=' class="'+tableBorder+'"';

				return '<'+end1+tag+attr+right;
			});

			if(isIE)sHtml = sHtml.replace(/&apos;/ig, '&#39;');

			if(!isSafari)
			{
				//style轉font
				function style2font(all,tag,style,content)
				{
					var attrs='',f,s1,s2,c;
					f=style.match(/font-family\s*:\s*([^;"]+)/i);
					if(f)attrs+=' face="'+f[1]+'"';
					s1=style.match(/font-size\s*:\s*([^;"]+)/i);
					if(s1)
					{
						s1=s1[1].toLowerCase();
						for(var j=0;j<arrFontsize.length;j++)if(s1===arrFontsize[j].n||s1===arrFontsize[j].s){s2=j+1;break;}
						if(s2)
						{
							attrs+=' size="'+s2+'"';
							style=style.replace(/(^|;)(\s*font-size\s*:\s*[^;"]+;?)+/ig,'$1');
						}
					}
					c=style.match(/(?:^|[\s;])color\s*:\s*([^;"]+)/i);
					if(c)
					{
						var rgb;
						if(rgb=c[1].match(/\s*rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i)){c[1]='#';for(var i=1;i<=3;i++)c[1]+=(rgb[i]-0).toString(16);}
						c[1]=c[1].replace(/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i,'#$1$1$2$2$3$3');
						attrs+=' color="'+c[1]+'"';
					}
					style=style.replace(/(^|;)(\s*(font-family|color)\s*:\s*[^;"]+;?)+/ig,'$1');
					if(attrs!=='')
					{
						if(style)attrs+=' style="'+style+'"';
						return '<font'+attrs+'>'+content+"</font>";
					}
					else return all;
				}
				sHtml = sHtml.replace(/<(span)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*(?:font-family|font-size|color)\s*:[^"]*)"(?: [^>]*)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,style2font);//第3層
				sHtml = sHtml.replace(/<(span)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*(?:font-family|font-size|color)\s*:[^"]*)"(?: [^>]*)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?)<\/\1>/ig,style2font);//第2層
				sHtml = sHtml.replace(/<(span)(?:\s+[^>]*?)?\s+style\s*=\s*"((?:[^"]*?;)*\s*(?:font-family|font-size|color)\s*:[^"]*)"(?: [^>]*)?>(((?!<\1(\s+[^>]*?)?>)[\s\S])*?)<\/\1>/ig,style2font);//最裡層
			}
			
			//表格單元格處理
			sHtml = sHtml.replace(/<(td|th)(\s+[^>]*?)?>(\s|&nbsp;)*<\/\1>/ig,'<$1$2>'+(isIE?'':'<br />')+'</$1>');
		}
		else
		{//read
			if(isSafari)
			{
				//轉換apple的style為strong,em等
				var arrAppleSpan=[{r:/font-weight:\sbold/ig,t:'strong'},{r:/font-style:\sitalic/ig,t:'em'},{r:/text-decoration:\sunderline/ig,t:'u'},{r:/text-decoration:\sline-through/ig,t:'strike'}];
				function replaceAppleSpan(all,tag,attr1,attr2,content)
				{
					var attr=attr1+attr2,newTag='';
					if(!attr)return content;
					for(var i=0;i<arrAppleSpan.length;i++)
					{
						if(attr.match(arrAppleSpan[i].r))
						{
							newTag=arrAppleSpan[i].t;
							break;
						}
					}
					if(newTag)return '<'+newTag+'>'+content+'</'+newTag+'>';
					else return all;
				}
				for(var i=0;i<2;i++){
					sHtml = sHtml.replace(/<(span)(\s+[^>]*?)?\s+class\s*=\s*"Apple-style-span"(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,replaceAppleSpan);//第3層
					sHtml = sHtml.replace(/<(span)(\s+[^>]*?)?\s+class\s*=\s*"Apple-style-span"(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?)<\/\1>/ig,replaceAppleSpan);//第2層
					sHtml = sHtml.replace(/<(span)(\s+[^>]*?)?\s+class\s*=\s*"Apple-style-span"(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S])*?)<\/\1>/ig,replaceAppleSpan);//最裡層				
				}
			}
			
			sHtml=sHtml.replace(/(<(\w+))((?:\s+[\w-:]+\s*=\s*(?:"[^"]*"|'[^']*'|[^>\s]+))*)\s*(\/?>)/g,function(all,left,tag,attr,right){
				tag=tag.toLowerCase();
				//恢復屬性值src,href
				var saveValue;
				attr=attr.replace(/\s+_xhe_(?:src|href)\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/i,function(all,v){saveValue=v.match(/^(["']?)(.*)\1/)[2];return '';});
				if(saveValue&&urlType)saveValue=getLocalUrl(saveValue,urlType,urlBase);
				
				attr=attr.replace(/\s+([\w-:]+)\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/g,function(all,n,v){
					n=n.toLowerCase();
					v=v.match(/^(["']?)(.*)\1/)[2];
					if(n==='class'){//清理class屬性
						if(v.match(/^["']?(apple|webkit)/i))return '';
						if(tag==='table'){
							v=v.replace(/\s?xhe-border/ig,'');
							if(v==='')return '';
						}
					}
					else if(n.match(/^((_xhe_|_moz_|_webkit_)|jquery\d+)/i))return '';//清理臨時屬性					
					else if(saveValue&&n.match(/^(src|href)$/i))return ' '+n+'="'+saveValue+'"';//恢復屬性值src,href
					else if(isSafari&&n==='style'){//轉換webkit的font-size
						v=v.replace(/(^|;)\s*(font-size)\s*:\s*([a-z-]+)\s*(;|$)/i,function(all,left,n,v,right){
							var t,s;
							for(var i=0;i<arrFontsize.length;i++)
							{
								t=arrFontsize[i];
								if(v===t.wkn){s=t.n;break;}
							}
							return left+n+':'+s+right;
						});
					}
					return ' '+n+'="'+v+'"';
				});
				
				//img強制加alt
				if(tag==='img'&&!attr.match(/\s+alt\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/i))attr+=' alt=""';
				return left+attr+right;
			});
			//表格單元格處理
			sHtml = sHtml.replace(/(<(td|th)(?:\s+[^>]*?)?>)\s*([\s\S]*?)(<br(\s*\/)?>)?\s*<\/\2>/ig,function(all,left,tag,content){return left+(content?content:'&nbsp;')+'</'+tag+'>';});
			//修正瀏覽器在空內容情況下多出來的代碼
			sHtml=sHtml.replace(/^\s*(?:<(p|div)(?:\s+[^>]*?)?>)?\s*(<span(?:\s+[^>]*?)?>\s*<\/span>|<br(?:\s+[^>]*?)?>|&nbsp;)*\s*(?:<\/\1>)?\s*$/i, '');
		}
		//寫和讀innerHTML前pre中<br>轉\r\n
		sHtml=sHtml.replace(/(<pre(?:\s+[^>]*?)?>)([\s\S]+?)(<\/pre>)/gi,function(all,left,code,right){
			return left+code.replace(/<br\s*\/?>/ig,'\r\n')+right;
		});
		return sHtml;
	}
	this.getSource=function(bFormat)
	{
		var sHtml,beforeGetSource=settings.beforeGetSource;
		if(bSource)
		{
			sHtml=$('#sourceCode',_doc).val();
			if(!beforeGetSource)sHtml=_this.formatXHTML(sHtml,false);
		}
		else
		{
			sHtml=_this.processHTML(_doc.body.innerHTML,'read');
			sHtml=_this.cleanHTML(sHtml);
			sHtml=_this.formatXHTML(sHtml,bFormat);
			if(beforeGetSource)sHtml=beforeGetSource(sHtml);
		}
		_text.value=sHtml;
		return sHtml;
	}
	this.cleanWord=function(sHtml)
	{
		var cleanPaste=settings.cleanPaste;
		if(cleanPaste>0&&cleanPaste<3&&sHtml.match(/mso(-|normal)|WordDocument|<table\s+[^>]*?x:str/i))
		{
			//區塊標籤清理
			sHtml = sHtml.replace(/<!--[\s\S]*?-->|<!(--)?\[[\s\S]+?\](--)?>|<style(\s+[^>]*?)?>[\s\S]*?<\/style>/ig, '');
			
			sHtml = sHtml.replace(/\r?\n/ig, '');

			sHtml=sHtml.replace(/(<(\/?)([\w-:]+))((?:\s+[\w-:]+(?:\s*=\s*(?:"[^"]*"|'[^']*'|[^>\s]+))?)*)\s*(\/?>)/g,function(all,left,end,tag,attr,right){
				tag=tag.toLowerCase();
				if((tag.match(/^(link|img)$/)&&attr.match(/file:\/\//i))||tag.match(/:/)||(tag==='span'&&cleanPaste===2))return '';
				if(!end){
					attr=attr.replace(/\s([\w-:]+)(?:\s*=\s*("[^"]*"|'[^']*'|[^>\s]+))?/ig,function(all,n,v){
						n=n.toLowerCase();
						v=v.match(/^(["']?)(.*)\1/)[2];
						if(n.match(/:/))return '';
						else if(n.match(/^(class|lang|language|span)$/))return '';
						else if(tag==='td'&&(n==='height'||(n==='width'&&!attr.match(/\scolspan="\d+"/i))))return '';
						else if(n==='style'){
							if(cleanPaste===2)return '';
							v=$.trim(v.replace(/\s*(mso-[^:]+:.+?|margin\s*:\s*0cm 0cm 0pt\s*|(text-align|font-variant|line-height)\s*:\s*.+?)(;|$)\s*/ig,''));
							return v?' '+n+'="'+v+'"':'';
						}
						return all;
					})
					if((tag==='a'&&!attr.match(/\s+href\s*=/)))return '';
				}
				return left+attr+right;
			});
			for(var i=0;i<3;i++)sHtml = sHtml.replace( /<([^\s>]+)(\s+[^>]*)?>\s*<\/\1>/g,'');//空內容的標籤
		}
		return sHtml;
	}
	this.cleanHTML=function(sHtml)
	{
		sHtml = sHtml.replace(/<!?\/?(DOCTYPE|html|body|meta)(\s+[^>]*?)?>/ig, '');
		var arrHeadSave;sHtml = sHtml.replace(/<head(?:\s+[^>]*?)?>([\s\S]*?)<\/head>/i, function(all,content){arrHeadSave=content.match(/<(script|style)(\s+[^>]*?)?>[\s\S]*?<\/\1>/ig);return '';});
		if(arrHeadSave)sHtml=arrHeadSave.join('')+sHtml;
		sHtml = sHtml.replace(/<\??xml(:\w+)?(\s+[^>]*?)?>([\s\S]*?<\/xml>)?/ig, '');

		if(!settings.internalScript)sHtml = sHtml.replace(/<script(\s+[^>]*?)?>[\s\S]*?<\/script>/ig, '');
		if(!settings.internalStyle)sHtml = sHtml.replace(/<style(\s+[^>]*?)?>[\s\S]*?<\/style>/ig, '');
		if(!settings.linkTag||!settings.inlineScript||!settings.inlineStyle)sHtml=sHtml.replace(/(<(\w+))((?:\s+[\w-]+\s*=\s*(?:"[^"]*"|'[^']*'|[^>\s]+))*)\s*(\/?>)/ig,function(all,left,tag,attr,right){
			if(!settings.linkTag&&tag.toLowerCase()==='link')return '';
			if(!settings.inlineScript)attr=attr.replace(/\s+on(?:click|dblclick|mouse(down|up|move|over|out|enter|leave|wheel)|key(down|press|up)|change|select|submit|reset|blur|focus|load|unload)\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/ig,'');
			if(!settings.inlineStyle)attr=attr.replace(/\s+(style|class)\s*=\s*("[^"]*"|'[^']*'|[^>\s]+)/ig,'');
			return left+attr+right;
		});
		sHtml=sHtml.replace(/<\/(strong|b|u|strike|em|i)>((?:\s|<br\/?>|&nbsp;)*?)<\1(\s+[^>]*?)?>/ig,'$2');//連續相同標籤

		return sHtml;

	}
	this.formatXHTML=function(sHtml,bFormat){

		var emptyTags = makeMap("area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param");//HTML 4.01
		var blockTags = makeMap("address,applet,blockquote,button,center,dd,del,dir,div,dl,dt,fieldset,form,frameset,h1,h2,h3,h4,h5,h6,hr,iframe,ins,isindex,li,map,menu,noframes,noscript,object,ol,p,pre,script,table,tbody,td,tfoot,th,thead,tr,ul");//HTML 4.01
		var inlineTags = makeMap("a,abbr,acronym,applet,b,basefont,bdo,big,br,button,cite,code,del,dfn,em,font,i,iframe,img,input,ins,kbd,label,map,object,q,s,samp,script,select,small,span,strike,strong,sub,sup,textarea,tt,u,var");//HTML 4.01
		var closeSelfTags = makeMap("colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr");
		var fillAttrsTags = makeMap("checked,compact,declare,defer,disabled,ismap,multiple,nohref,noresize,noshade,nowrap,readonly,selected");
		var cdataTags = makeMap("script,style");
		var tagReplac={'b':'strong','i':'em','s':'del','strike':'del'};
		
		var regTag=/<(?:\/([\w:]+)|!--([^>]*?)--|([\w:]+)((?:\s+[\w-:]+(?:\s*=\s*(?:"[^"]*"|'[^']*'|[^>\s]+))?)*)\s*(\/?))>/g;
		var regAttr = /\s+([\w-:]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'|([^\s]+)))?/g;
		var results=[],stack=[];
		stack.last = function(){return this[ this.length - 1 ];};
		var match,tagIndex,nextIndex=0,tagName,tagCDATA,arrCDATA,text;
		var lvl=-1,lastTag='body',lastTagStart,stopFormat=false;
		
		while(match=regTag.exec(sHtml)){
			tagIndex = match.index;
			if(tagIndex>nextIndex){//保存前面的文本或者CDATA
				text=sHtml.substring(nextIndex,tagIndex);
				if(tagCDATA)arrCDATA.push(text);
				else onText(text);
			}
			nextIndex = regTag.lastIndex;

			if(tagName=match[1]){//結束標籤
				tagName=processTag(tagName);
				if(tagCDATA&&tagName===tagCDATA){//結束標籤前輸出CDATA
					onCDATA(arrCDATA.join(''));
					tagCDATA=null;
					arrCDATA=null;
				}
				if(!tagCDATA){
					onEndTag(tagName);
					continue;
				}
			}
			
			if(tagCDATA)arrCDATA.push(match[0]);
			else{
				if(tagName=match[3]){//開始標籤
					tagName=processTag(tagName);
					onStartTag(tagName,match[4],match[5]);
					if(cdataTags[tagName]){
						tagCDATA=tagName;
						arrCDATA=[];
					}
				}
				else if(match[2])onComment(match[0]);//註釋標籤
			}

		}
		if(sHtml.length>nextIndex)onText(sHtml.substring(nextIndex,sHtml.length ));//結尾文本
		onEndTag();//封閉未結束的標籤
		
		sHtml=results.join('');
		results=null;
		
		function makeMap(str)
		{
			var obj = {}, items = str.split(",");
			for ( var i = 0; i < items.length; i++ )obj[ items[i] ] = true;
			return obj;
		}
		function processTag(tagName)
		{
			tagName=tagName.toLowerCase();
			var tag=tagReplac[tagName];
			return tag?tag:tagName;
		}
		function onStartTag(tagName,rest,unary)
		{
			if(blockTags[tagName])while(stack.last()&&inlineTags[stack.last()])onEndTag(stack.last());//塊標籤
			if(closeSelfTags[tagName]&&stack.last()===tagName)onEndTag(tagName);//自封閉標籤
			unary = emptyTags[ tagName ] || !!unary;
			if (!unary)stack.push(tagName);
			
			var all=Array();
			all.push('<' + tagName);
			rest.replace(regAttr, function(match, name)
			{
				name=name.toLowerCase();
				var value = arguments[2] ? arguments[2] :
						arguments[3] ? arguments[3] :
						arguments[4] ? arguments[4] :
						fillAttrsTags[name] ? name : "";
				all.push(' '+name+'="'+value+'"');
			});
			all.push((unary ? " /" : "") + ">");
			addHtmlFrag(all.join(''),tagName,true);
			if(tagName==='pre')stopFormat=true;
		}
		function onEndTag(tagName)
		{
			if(!tagName)var pos=0;//清空棧
			else for(var pos=stack.length-1;pos>=0;pos--)if(stack[pos]===tagName)break;//向上尋找匹配的開始標籤
			if(pos>=0)
			{
				for(var i=stack.length-1;i>=pos;i--)addHtmlFrag("</" + stack[i] + ">",stack[i]);
				stack.length=pos;
			}
			if(tagName==='pre'){
				stopFormat=false;
				lvl--;
			}
		}
		function onText(text){
			addHtmlFrag(_this.domEncode(text));
		}
		function onCDATA(text){
			results.push(text);
		}
		function onComment(text){
			results.push(text);
		}
		function addHtmlFrag(html,tagName,bStart)
		{
			if(!stopFormat)html=html.replace(/(\t*\r?\n\t*)+/g,'');//清理換行符和相鄰的製表符
			if(!stopFormat&&bFormat===true)
			{
				if(html.match(/^\s*$/)){//不格式化空內容的標籤
					results.push(html);
					return;
				}
				var bBlock=blockTags[tagName],tag=bBlock?tagName:'';
				if(bBlock)
				{
					if(bStart)lvl++;//塊開始
					if(lastTag==='')lvl--;//補文本結束
				}
				else if(lastTag)lvl++;//文本開始
				if(tag!==lastTag||bBlock)addIndent();
				results.push(html);
				if(tagName==='br')addIndent();//回車強制換行
				if(bBlock&&(emptyTags[tagName]||!bStart))lvl--;//塊結束
				lastTag=bBlock?tagName:'';lastTagStart=bStart;
			}
			else results.push(html);
		}
		function addIndent(){results.push('\r\n');if(lvl>0){var tabs=lvl;while(tabs--)results.push("\t");}}
		//font轉style
		function font2style(all,tag,attrs,content)
		{
			if(!attrs)return content;
			var styles='',f,s,c,style;
			f=attrs.match(/ face\s*=\s*"\s*([^"]+)\s*"/i);
			if(f)styles+='font-family:'+f[1]+';';
			s=attrs.match(/ size\s*=\s*"\s*(\d+)\s*"/i);
			if(s)styles+='font-size:'+arrFontsize[(s[1]>7?7:(s[1]<1?1:s[1]))-1].n+';';
			c=attrs.match(/ color\s*=\s*"\s*([^"]+)\s*"/i);
			if(c)styles+='color:'+c[1]+';';
			style=attrs.match(/ style\s*=\s*"\s*([^"]+)\s*"/i);
			if(style)styles+=style[1];
			if(styles)content='<span style="'+styles+'">'+content+'</span>';
			return content;
		}
		sHtml = sHtml.replace(/<(font)(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?<\/\1>)*?)<\/\1>/ig,font2style);//第3層
		sHtml = sHtml.replace(/<(font)(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S]|<\1(\s+[^>]*?)?>((?!<\1(\s+[^>]*?)?>)[\s\S])*?<\/\1>)*?)<\/\1>/ig,font2style);//第2層
		sHtml = sHtml.replace(/<(font)(\s+[^>]*?)?>(((?!<\1(\s+[^>]*?)?>)[\s\S])*?)<\/\1>/ig,font2style);//最裡層
		sHtml = sHtml.replace(/^(\s*\r?\n)+|(\s*\r?\n)+$/g,'');//清理首尾換行
		return sHtml;
	}
	this.toggleShowBlocktag=function(state)
	{
		if(bShowBlocktag===state)return;
		bShowBlocktag=!bShowBlocktag;
		var _jBody=$(_doc.body);
		if(bShowBlocktag)
		{
			bodyClass+=' showBlocktag';
			_jBody.addClass('showBlocktag');
		}
		else
		{
			bodyClass=bodyClass.replace(' showBlocktag','');
			_jBody.removeClass('showBlocktag');
		}
	}
	this.toggleSource=function(state)
	{
		if(bSource===state)return;
		_jTools.find('[name=Source]').toggleClass('xheEnabled').toggleClass('xheActive');
		var _body=_doc.body,jBody=$(_body),sHtml;
		var sourceCode,cursorMark='<span id="_xhe_cursor"></span>',cursorPos=0;
		if(!bSource)
		{//轉為原始碼模式
			_this.pasteHTML(cursorMark,true);//標記當前位置
			sHtml=_this.getSource(true);
			cursorPos=sHtml.indexOf(cursorMark);
			if(!isOpera)cursorPos=sHtml.substring(0,cursorPos).replace(/\r/g,'').length;//修正非opera光標定位點
			sHtml=sHtml.replace(cursorMark,'');
			if(isIE)_body.contentEditable='false';
			else _doc.designMode = 'Off';
			jBody.attr('scroll','no').attr('class','sourceMode').html('<textarea id="sourceCode" wrap="soft" spellcheck="false" height="100%" />');
			sourceCode=$('#sourceCode',jBody).blur(_this.getSource)[0];
		}
		else
		{//轉為編輯模式
			sHtml=_this.getSource();
			jBody.html('').removeAttr('scroll').attr('class','editMode'+bodyClass);
			if(isIE)_body.contentEditable='true';
			else _doc.designMode = 'On';
			if(isMozilla)
			{
				_this._exec("inserthtml","-");//修正firefox原始碼切換回來無法刪除文字的問題
				$('#'+idFixFFCursor).show().focus().hide();//臨時修正Firefox 3.6光標丟失問題
			}
		}
		bSource=!bSource;
		_this.setSource(sHtml);
		_this.focus();
		if(bSource)//光標定位源碼
		{
			if(sourceCode.setSelectionRange)sourceCode.setSelectionRange(cursorPos, cursorPos);
			else
			{
				var rng = sourceCode.createTextRange();
				rng.move("character",cursorPos);
				rng.select();
			}
		}
		else _this.setCursorFirst(true);//定位最前面
		_jTools.find('[name=Source],[name=Preview]').toggleClass('xheEnabled');
		_jTools.find('.xheButton').not('[name=Source],[name=Fullscreen],[name=About]').toggleClass('xheEnabled');
		setTimeout(setOpts,300);
	}
	this.showPreview=function()
	{
		var beforeSetSource=settings.beforeSetSource,sContent=_this.getSource();
		if(beforeSetSource)sContent=beforeSetSource(sContent);
		var sHTML='<html><head>'+headHTML+'<title>預覽</title>'+(urlBase?'<base href="'+urlBase+'"/>':'')+'</head><body>' + sContent + '</body></html>';
		var screen=window.screen,oWindow=window.open('', 'xhePreview', 'toolbar=yes,location=no,status=yes,menubar=yes,scrollbars=yes,resizable=yes,width='+Math.round(screen.width*0.9)+',height='+Math.round(screen.height*0.8)+',left='+Math.round(screen.width*0.05)),oDoc=oWindow.document;
		oDoc.open();
		oDoc.write(sHTML);
		oDoc.close();
		oWindow.focus();
	}
	this.toggleFullscreen=function(state)
	{
		if(bFullscreen===state)return;
		var jLayout=$('#'+idContainer).find('.xheLayout'),jContainer=$('#'+idContainer),browserVer=jQuery.browser.version,isIE67=(isIE&&(browserVer==6||browserVer==7));
		if(bFullscreen)
		{//取消全屏
			if(isIE67)_jText.after(jContainer);
			jLayout.attr('style',sLayoutStyle);
			_jArea.height(editorHeight-_jTools.outerHeight());
			$(window).scrollTop(outerScroll);
			setTimeout(function(){$(window).scrollTop(outerScroll);},10);//Firefox需要延遲設置
		}
		else
		{//顯示全屏
			if(isIE67)$('body').append(jContainer);
			outerScroll=$(window).scrollTop();
			sLayoutStyle=jLayout.attr('style');
			jLayout.removeAttr('style');
			_jArea.height('100%');
			setTimeout(fixFullHeight,100);
		}
		if(isMozilla)//臨時修正Firefox 3.6原始碼光標丟失問題
		{
			$('#'+idFixFFCursor).show().focus().hide();
			setTimeout(_this.focus,1);
		}
		else if(isIE67)_this.setCursorFirst();
		bFullscreen=!bFullscreen;
		jContainer.toggleClass('xhe_Fullscreen');
		$('html').toggleClass('xhe_Fullfix');
		_jTools.find('[name=Fullscreen]').toggleClass('xheActive');
		setTimeout(setOpts,300);
	}
	this.showMenu=function(menuitems,callback)
	{
		var jMenu=$('<div class="xheMenu"></div>'),arrItem=[];
		$.each(menuitems,function(n,v){arrItem.push('<a href="javascript:void(0);" title="'+(v.t?v.t:v.s)+'" v="'+v.v+'">'+v.s+'</a>');});
		jMenu.append(arrItem.join(''));
		jMenu.click(function(ev){callback($(ev.target).closest('a').attr('v'));_this.hidePanel();return false;}).mousedown(returnFalse);
		_this.showPanel(jMenu);
	}
	this.showColor=function(callback)
	{
		var jColor=$('<div class="xheColor"></div>'),arrItem=[],count=0;
		$.each(itemColors,function(n,v)
		{
			if(count%7===0)arrItem.push((count>0?'</div>':'')+'<div>');
			arrItem.push('<a href="javascript:void(0);" xhev="'+v+'" title="'+v+'" style="background:'+v+'"></a>');
			count++;
		});
		arrItem.push('</div>');
		jColor.append(arrItem.join(''));
		jColor.click(function(ev){ev=ev.target;if(!$.nodeName(ev,'A'))return;callback($(ev).attr('xhev'));_this.hidePanel();return false;}).mousedown(returnFalse);
		_this.showPanel(jColor);
	}
	this.showPastetext=function()
	{
		var jPastetext=$(htmlPastetext),jValue=$('#xhePastetextValue',jPastetext),jSave=$('#xheSave',jPastetext);
		jSave.click(function(){
			_this.loadBookmark();
			var sValue=jValue.val();
			if(sValue)_this.pasteText(sValue);
			_this.hidePanel();
			return false;
		});
		_this.showDialog(jPastetext);
	}
	this.showLink=function()
	{
		var jLink=$(htmlLink),jParent=_this.getParent('a'),jText=$('#xheLinkText',jLink),jUrl=$('#xheLinkUrl',jLink),jTarget=$('#xheLinkTarget',jLink),jSave=$('#xheSave',jLink),selHtml=_this.getSelect();
		if(jParent.length===1)
		{
			jUrl.val(xheAttr(jParent,'href'));
			jTarget.attr('value',jParent.attr('target'));
		}
		else if(selHtml==='')jText.val(settings.defLinkText).closest('div').show();
		if(settings.upLinkUrl)_this.uploadInit(jUrl,settings.upLinkUrl,settings.upLinkExt);
		jSave.click(function(){
			_this.loadBookmark();
			var url=jUrl.val();
			if(url===''||jParent.length===0)_this._exec('unlink');
			if(url!==''&&url!=='http://')
			{
				var aUrl=url.split(' '),sTarget=jTarget.val(),sText=jText.val();
				if(aUrl.length>1)
				{//批量插入
					_this._exec('unlink');//批量前刪除當前鏈接並重新獲取選擇內容
					selHtml=_this.getSelect();
					var sTemplate='<a href="xhe_tmpurl"',sLink,arrLink=[];
					if(sTarget!=='')sTemplate+=' target="'+sTarget+'"';
					sTemplate+='>xhe_tmptext</a>';
					sText=(selHtml!==''?selHtml:(sText?sText:url));
					for(var i in aUrl)
					{
						url=aUrl[i];
						if(url!=='')
						{
							url=url.split('||');
							sLink=sTemplate;
							sLink=sLink.replace('xhe_tmpurl',url[0]);
							sLink=sLink.replace('xhe_tmptext',url[1]?url[1]:sText);
							arrLink.push(sLink);
						}
					}
					_this.pasteHTML(arrLink.join('&nbsp;'));
				}
				else
				{//單url模式
					url=aUrl[0].split('||');
					if(!sText)sText=url[0];
					sText=url[1]?url[1]:(selHtml!=='')?'':sText?sText:url[0];
					if(jParent.length===0)
					{
						if(sText)_this.pasteHTML('<a href="#xhe_tmpurl">'+sText+'</a>');
						else _this._exec('createlink','#xhe_tmpurl');
						jParent=$('a[href$="#xhe_tmpurl"]',_doc);
					}
					else if(sText&&!isSafari)jParent.text(sText);//safari改寫文本會導致光標丟失
					xheAttr(jParent,'href',url[0]);
					if(sTarget!=='')jParent.attr('target',sTarget);
					else jParent.removeAttr('target');
				}
			}
			_this.hidePanel();
			return false;
		});
		_this.showDialog(jLink);
	}
	this.showImg=function()
	{
		var jImg=$(htmlImg),jParent=_this.getParent('img'),jUrl=$('#xheImgUrl',jImg),jAlt=$('#xheImgAlt',jImg),jAlign=$('#xheImgAlign',jImg),jWidth=$('#xheImgWidth',jImg),jHeight=$('#xheImgHeight',jImg),jBorder=$('#xheImgBorder',jImg),jVspace=$('#xheImgVspace',jImg),jHspace=$('#xheImgHspace',jImg),jSave=$('#xheSave',jImg);
		if(jParent.length===1)
		{
			jUrl.val(xheAttr(jParent,'src'));
			jAlt.val(jParent.attr('alt'));
			jAlign.val(jParent.attr('align'));
			jWidth.val(jParent.attr('width'));
			jHeight.val(jParent.attr('height'));
			jBorder.val(jParent.attr('border'));
			var vspace=jParent.attr('vspace'),hspace=jParent.attr('hspace');
			jVspace.val(vspace<=0?'':vspace);
			jHspace.val(hspace<=0?'':hspace);
		}
		if(settings.upImgUrl)_this.uploadInit(jUrl,settings.upImgUrl,settings.upImgExt);
		jSave.click(function(){
			_this.loadBookmark();
			var url=jUrl.val();
			if(url!==''&&url!=='http://')
			{
				var aUrl=url.split(' '),sAlt=jAlt.val(),sAlign=jAlign.val(),sWidth=jWidth.val(),sHeight=jHeight.val(),sBorder=jBorder.val(),sVspace=jVspace.val(),sHspace=jHspace.val();;
				if(aUrl.length>1)
				{//批量插入
					var sTemplate='<img src="xhe_tmpurl"',sImg,arrImg=[];
					if(sAlt!=='')sTemplate+=' alt="'+sAlt+'"';
					if(sAlign!=='')sTemplate+=' align="'+sAlign+'"';
					if(sWidth!=='')sTemplate+=' width="'+sWidth+'"';
					if(sHeight!=='')sTemplate+=' height="'+sHeight+'"';
					if(sBorder!=='')sTemplate+=' border="'+sBorder+'"';
					if(sVspace!=='')sTemplate+=' vspace="'+sVspace+'"';
					if(sHspace!=='')sTemplate+=' hspace="'+sHspace+'"';
					sTemplate+=' />';
					for(var i in aUrl)
					{
						url=aUrl[i];
						if(url!=='')
						{
							url=url.split('||');
							sImg=sTemplate;
							sImg=sImg.replace('xhe_tmpurl',url[0]);
							if(url[1])sImg='<a href="'+url[1]+'" target="_blank">'+sImg+'</a>'
							arrImg.push(sImg);
						}
					}
					_this.pasteHTML(arrImg.join('&nbsp;'));
				}
				else if(aUrl.length===1)
				{//單URL模式
					url=aUrl[0];
					if(url!=='')
					{
						url=url.split('||');
						if(jParent.length===0)
						{
							_this.pasteHTML('<img src="'+url[0]+'#xhe_tmpurl" />');
							jParent=$('img[src$="#xhe_tmpurl"]',_doc);
						}
						xheAttr(jParent,'src',url[0])
						if(sAlt!=='')jParent.attr('alt',sAlt);
						if(sAlign!=='')jParent.attr('align',sAlign);
						else jParent.removeAttr('align');
						if(sWidth!=='')jParent.attr('width',sWidth);
						else jParent.removeAttr('width');
						if(sHeight!=='')jParent.attr('height',sHeight);
						else jParent.removeAttr('height');
						if(sBorder!=='')jParent.attr('border',sBorder);
						else jParent.removeAttr('border');
						if(sVspace!=='')jParent.attr('vspace',sVspace);
						else jParent.removeAttr('vspace');
						if(sHspace!=='')jParent.attr('hspace',sHspace);
						else jParent.removeAttr('hspace');
						if(url[1])
						{
							var jLink=jParent.parent('a');
							if(jLink.length===0)
							{
								jParent.wrap('<a></a>');
								jLink=jParent.parent('a');
							}
							xheAttr(jLink,'href',url[1]);
							jLink.attr('target','_blank');
						}
					}
				}
			}
			else if(jParent.length===1)jParent.remove();
			_this.hidePanel();
			return false;
		});
		_this.showDialog(jImg);
	}
	this.showEmbed=function(sType,sHtml,sMime,sClsID,sBaseAttrs,sUploadUrl,sUploadExt)
	{
		var jEmbed=$(sHtml),jParent=_this.getParent('embed[type="'+sMime+'"],embed[classid="'+sClsID+'"]'),jUrl=$('#xhe'+sType+'Url',jEmbed),jWidth=$('#xhe'+sType+'Width',jEmbed),jHeight=$('#xhe'+sType+'Height',jEmbed),jSave=$('#xheSave',jEmbed);
		if(sUploadUrl)_this.uploadInit(jUrl,sUploadUrl,sUploadExt);
		_this.showDialog(jEmbed);
		if(jParent.length===1)
		{
			jUrl.val(xheAttr(jParent,'src'));
			jWidth.val(jParent.attr('width'));
			jHeight.val(jParent.attr('height'));
		}
		jSave.click(function(){
			_this.loadBookmark();
			var url=jUrl.val();
			if(url!==''&&url!=='http://')
			{
				var w=jWidth.val(),h=jHeight.val(),reg=/^\d+%?$/;
				if(!reg.test(w))w=412;if(!reg.test(h))h=300;
				var sBaseCode='<embed type="'+sMime+'" classid="'+sClsID+'" src="xhe_tmpurl"'+sBaseAttrs;
				var aUrl=url.split(' ');
				if(aUrl.length>1)
				{//批量插入
					var sTemplate=sBaseCode+'',sEmbed,arrEmbed=[];
					sTemplate+=' width="xhe_width" height="xhe_height" />';
					for(var i in aUrl)
					{
						url=aUrl[i].split('||');
						sEmbed=sTemplate;
						sEmbed=sEmbed.replace('xhe_tmpurl',url[0])
						sEmbed=sEmbed.replace('xhe_width',url[1]?url[1]:w)
						sEmbed=sEmbed.replace('xhe_height',url[2]?url[2]:h)
						if(url!=='')arrEmbed.push(sEmbed);
					}
					_this.pasteHTML(arrEmbed.join('&nbsp;'));
				}
				else if(aUrl.length===1)
				{//單URL模式
					url=aUrl[0].split('||');
					if(jParent.length===0)
					{
						_this.pasteHTML(sBaseCode.replace('xhe_tmpurl',url[0]+'#xhe_tmpurl')+' />');
						jParent=$('embed[src$="#xhe_tmpurl"]',_doc);
					}
					xheAttr(jParent,'src',url[0]);
					jParent.attr('width',url[1]?url[1]:w);
					jParent.attr('height',url[2]?url[2]:h);
				}
			}
			else if(jParent.length===1)jParent.remove();
			_this.hidePanel();
			return false;
		});
	}
	this.showEmot=function(group)
	{
		var jEmot=$('<div class="xheEmot"></div>');

		group=group?group:(selEmotGroup?selEmotGroup:'default');
		var arrEmot=arrEmots[group];

		var sEmotPath=emotPath+group+'/',n=0,arrList=[],jList='';
		var ew=arrEmot.width,eh=arrEmot.height,line=arrEmot.line,count=arrEmot.count,list=arrEmot.list;
		if(count)
		{
			for(var i=1;i<=count;i++)
			{
				n++;
				arrList.push('<a href="javascript:void(0);" style="background-image:url('+sEmotPath+i+'.gif);" emot="'+group+','+i+'" xhev="">&nbsp;</a>');
				if(n%line===0)arrList.push('<br />');
			}
		}
		else
		{
			$.each(list,function(id,title)
			{
				n++;
				arrList.push('<a href="javascript:void(0);" style="background-image:url('+sEmotPath+id+'.gif);" emot="'+group+','+id+'" title="'+title+'" xhev="'+title+'">&nbsp;</a>');
				if(n%line===0)arrList.push('<br />');
			});
		}
		var w=line*(ew+12),h=Math.ceil(n/line)*(eh+12),mh=w*0.75;
		if(h<=mh)mh='';
		jList=$('<style>'+(mh?'.xheEmot div{width:'+(w+20)+'px;height:'+mh+'px;}':'')+'.xheEmot div a{width:'+ew+'px;height:'+eh+'px;}</style><div>'+arrList.join('')+'</div>').click(function(ev){ev=ev.target;var jA=$(ev);if(!$.nodeName(ev,'A'))return;_this.pasteHTML('<img emot="'+jA.attr('emot')+'" alt="'+jA.attr('xhev')+'">');_this.hidePanel();return false;}).mousedown(returnFalse);
		jEmot.append(jList);

		var gcount=0,arrGroup=['<ul>'],jGroup;//表情分類
		$.each(arrEmots,function(g,v){
			gcount++;
			arrGroup.push('<li'+(group===g?' class="cur"':'')+'><a href="javascript:void(0);" group="'+g+'">'+v.name+'</a></li>');
		});
		if(gcount>1)
		{
			arrGroup.push('</ul><br style="clear:both;" />');
			jGroup=$(arrGroup.join('')).click(function(ev){selEmotGroup=$(ev.target).attr('group');_this.exec('Emot');return false;}).mousedown(returnFalse);
			jEmot.append(jGroup);
		}

		_this.showPanel(jEmot);
	}
	this.showTable=function()
	{
		var jTable=$(htmlTable),jRows=$('#xheTableRows',jTable),jColumns=$('#xheTableColumns',jTable),jHeaders=$('#xheTableHeaders',jTable),jWidth=$('#xheTableWidth',jTable),jHeight=$('#xheTableHeight',jTable),jBorder=$('#xheTableBorder',jTable),jCellSpacing=$('#xheTableCellSpacing',jTable),jCellPadding=$('#xheTableCellPadding',jTable),jAlign=$('#xheTableAlign',jTable),jCaption=$('#xheTableCaption',jTable),jSave=$('#xheSave',jTable);
		jSave.click(function(){
			_this.loadBookmark();
			var sCaption=jCaption.val(),sBorder=jBorder.val(),sRows=jRows.val(),sCols=jColumns.val(),sHeaders=jHeaders.val(),sWidth=jWidth.val(),sHeight=jHeight.val(),sCellSpacing=jCellSpacing.val(),sCellPadding=jCellPadding.val(),sAlign=jAlign.val();
			var i,j,htmlTable='<table'+(sBorder!==''?' border="'+sBorder+'"':'')+(sWidth!==''?' width="'+sWidth+'"':'')+(sHeight!==''?' width="'+sHeight+'"':'')+(sCellSpacing!==''?' cellspacing="'+sCellSpacing+'"':'')+(sCellPadding!==''?' cellpadding="'+sCellPadding+'"':'')+(sAlign!==''?' align="'+sAlign+'"':'')+'>';
			if(sCaption!=='')htmlTable+='<caption>'+sCaption+'</caption>';
			if(sHeaders==='row'||sHeaders==='both')
			{
				htmlTable+='<tr>';
				for(i=0;i<sCols;i++)htmlTable+='<th scope="col"></th>';
				htmlTable+='</tr>';
				sRows--;
			}
			htmlTable+='<tbody>';
			for(i=0;i<sRows;i++)
			{
				htmlTable+='<tr>';
				for(j=0;j<sCols;j++)
				{
					if(j===0&&(sHeaders==='col'||sHeaders==='both'))htmlTable+='<th scope="row"></th>';
					else htmlTable+='<td></td>';
				}
				htmlTable+='</tr>';
			}
			htmlTable+='</tbody></table>';
			_this.pasteHTML(htmlTable);
			_this.hidePanel();
			return false;
		});
		_this.showDialog(jTable);
	}
	this.showAbout=function()
	{
		var jAbout=$(htmlAbout);
		_this.showDialog(jAbout);
	}
	this.addShortcuts=function(key,cmd)
	{
		key=key.toLowerCase();
		if(arrShortCuts[key]===undefined)arrShortCuts[key]=Array();
		arrShortCuts[key].push(cmd);
	}
	this.delShortcuts=function(key){delete arrShortCuts[key];}
	this.uploadInit=function(jText,toUrl,upext)
	{
		var jUpload=$('<span class="xheUpload"><input type="text" style="visibility:hidden;" tabindex="-1" /><input type="button" value="'+settings.upBtnText+'" class="xheBtn" tabindex="-1" /></span>'),jUpBtn=$('.xheBtn',jUpload);
		var bHtml5Upload=settings.html5Upload,upMultiple=bHtml5Upload?settings.upMultiple:1;
		jText.after(jUpload);jUpBtn.before(jText);
		toUrl=toUrl.replace(/{editorRoot}/ig,editorRoot);
		if(toUrl.substr(0,1)==='!')//自定義上傳管理頁
		{
			jUpBtn.click(function(){
				bShowPanel=false;//防止按鈕面板被關閉
				_this.showIframeModal('上傳文件',toUrl.substr(1),setUploadMsg,null,null,function(){bShowPanel=true;});
			});
		}
		else
		{//系統預設ajax上傳
			jUpload.append('<input type="file"'+(upMultiple>1?' multiple=""':'')+' class="xheFile" size="13" name="'+uploadInputname+'" tabindex="-1" />');
			var jFile=$('.xheFile',jUpload),arrMsg;
			jFile.change(function(){arrMsg=[];_this.startUpload(jFile[0],toUrl,upext,setUploadMsg);});
			setTimeout(function(){//拖放上傳
				jText.closest('.xheDialog').bind('dragenter dragover',returnFalse).bind('drop',function(ev){
					var dataTransfer=ev.originalEvent.dataTransfer,fileList;
					if(bHtml5Upload&&dataTransfer&&(fileList=dataTransfer.files)&&fileList.length>0)_this.startUpload(fileList,toUrl,upext,setUploadMsg);
					return false;
				});
			},10);
		}
		function setUploadMsg(arrMsg)
		{
			if(is(arrMsg,'string'))arrMsg=[arrMsg];//允許單URL傳遞
			var bImmediate=false,i,count=arrMsg.length,msg,url,arrUrl=[],onUpload=settings.onUpload;
			if(onUpload)onUpload(arrMsg);//用戶上傳回調
			for(i=0;i<count;i++)
			{
				msg=arrMsg[i];
				url=is(msg,'string')?msg:msg.url;
				if(url.substr(0,1)==='!'){bImmediate=true;url=url.substr(1);}
				arrUrl.push(url);
			}
			jText.val(arrUrl.join(' '));
			if(bImmediate)jText.closest('.xheDialog').find('#xheSave').click();
		}
	}
	this.startUpload=function(fromFiles,toUrl,limitExt,onUploadComplete)
	{
		var arrMsg=[],bHtml5Upload=settings.html5Upload,upMultiple=bHtml5Upload?settings.upMultiple:1;
		var upload,fileList,filename,jUploadTip=$('<div style="padding:22px 0;text-align:center;line-height:30px;">文件上傳中，請稍候……<br /></div>'),sLoading='<img src="'+skinPath+'img/loading.gif">';
		if(!bHtml5Upload||(fromFiles.nodeType&&!((fileList=fromFiles.files)&&fileList[0])))
		{
			if(!checkFileExt(fromFiles.value,limitExt))return;
			jUploadTip.append(sLoading);
			upload=new _this.html4Upload(fromFiles,toUrl,onUploadCallback);
		}
		else
		{
			if(!fileList)fileList=fromFiles;//拖放文件列表
			var i,len=fileList.length;
			if(len>upMultiple){alert('請不要一次上傳超過'+upMultiple+'個文件');return;}
			for(i=0;i<len;i++)if(!checkFileExt(fileList[i].fileName,limitExt))return;
			var jProgress=$('<div class="xheProgress"><div><span>0%</span></div></div>');
			jUploadTip.append(jProgress);
			upload=new _this.html5Upload(uploadInputname,fileList,toUrl,onUploadCallback,function(ev){
				if(ev.loaded>=0)
				{
					var sPercent=Math.round((ev.loaded * 100) / ev.total)+'%';
					$('div',jProgress).css('width',sPercent);
					$('span',jProgress).text(sPercent+' ( '+formatBytes(ev.loaded)+' / '+formatBytes(ev.total)+' )');
				}
				else jProgress.replaceWith(sLoading);//不支持進度
			});
		}
		var panelState=bShowPanel;
		if(panelState)bShowPanel=false;//防止面板被關閉
		_this.showModal('文件上傳中(Esc取消上傳)',jUploadTip,320,150,function(){bShowPanel=panelState;upload.remove();});
		upload.start();
		function onUploadCallback(sText,bFinish)
		{
			var data=Object,bOK=false;
			try{data=eval('('+sText+')');}catch(ex){};
			if(data.err===undefined||data.msg===undefined)alert(toUrl+' 上傳接口發生錯誤！\r\n\r\n返回的錯誤內容為: \r\n\r\n'+sText);
			else
			{
				if(data.err)alert(data.err);
				else
				{
					arrMsg.push(data.msg);
					bOK=true;//繼續下一個文件上傳
				}
			}
			if(!bOK||bFinish)_this.removeModal();
			if(bFinish&&bOK)onUploadComplete(arrMsg);//全部上傳完成
			return bOK;
		}
	}
	this.html4Upload=function(fromfile,toUrl,callback)
	{
		var uid = new Date().getTime(),idIO='jUploadFrame'+uid,_this=this;
		var jIO=$('<iframe name="'+idIO+'" class="xheHideArea" />').appendTo('body');
		var jForm=$('<form action="'+toUrl+'" target="'+idIO+'" method="post" enctype="multipart/form-data" class="xheHideArea"></form>').appendTo('body');
		var jOldFile = $(fromfile),jNewFile = jOldFile.clone().attr('disabled','true');
		jOldFile.before(jNewFile).appendTo(jForm);
		this.remove=function()
		{
			if(_this!==null)
			{
				jNewFile.before(jOldFile).remove();
				jIO.remove();jForm.remove();
				_this=null;
			}
		}
		this.onLoad=function(){
			var ifmDoc=jIO[0].contentWindow.document,result=$(ifmDoc.body).text();
			ifmDoc.write('');
			callback(result,true);
		}
		this.start=function(){jForm.submit();jIO.load(_this.onLoad);}
		return this;
	}
	this.html5Upload=function(inputname,fromFiles,toUrl,callback,onProgress)
	{
		var xhr,i=0,count=fromFiles.length,allLoaded=0,allSize=0,_this=this;
		for(var j=0;j<count;j++)allSize+=fromFiles[j].fileSize;
		this.remove=function(){if(xhr){xhr.abort();xhr=null;}}
		this.uploadNext=function(sText)
		{
			if(sText)//當前文件上傳完成
			{
				allLoaded+=fromFiles[i-1].fileSize;
				returnProgress(0);
			}
			if((!sText||(sText&&callback(sText,i===count)===true))&&i<count)postFile(fromFiles[i++],toUrl,_this.uploadNext,function(loaded){returnProgress(loaded);});
		}
		this.start=function(){_this.uploadNext();}
		function postFile(fromfile,toUrl,callback,onProgress)
		{
			xhr = new XMLHttpRequest(),upload=xhr.upload;
			xhr.onreadystatechange=function(){if(xhr.readyState===4)callback(xhr.responseText);};
			if(upload)upload.onprogress=function(ev){onProgress(ev.loaded);};
			else onProgress(-1);//不支持進度
			xhr.open("POST", toUrl);
			xhr.setRequestHeader('Content-Type', 'application/octet-stream');
			xhr.setRequestHeader('Content-Disposition', 'attachment; name="'+inputname+'"; filename="'+fromfile.fileName+'"');
			if(xhr.sendAsBinary)xhr.sendAsBinary(fromfile.getAsBinary());
			else xhr.send(fromfile);
		}
		function returnProgress(loaded){if(onProgress)onProgress({'loaded':allLoaded+loaded,'total':allSize});}
	}
	this.showIframeModal=function(title,url,callback,w,h,onRemove)
	{
		var jContent=$('<iframe frameborder="0" src="'+url.replace(/{editorRoot}/ig,editorRoot)+(/\?/.test(url)?'&':'?')+'editorhost='+location.hostname+'" style="width:100%;height:100%;display:none;" /><div class="xheModalIfmWait"></div>'),jIframe=$(jContent[0]),jWait=$(jContent[1]);
		_this.showModal(title,jContent,w,h,onRemove);
		var modalWin=jIframe[0].contentWindow,result;
		initModalWin();
		jIframe.load(function(){
			try{
				if(result=modalWin.name){//跨域
					modalWin.document.write('');
					result=eval('('+unescape(result)+')');
					return callbackModal(result);
				}
				else{//非跨域
					initModalWin();
					$(modalWin.document).keydown(checkEsc);
				}
			}catch(ex){};
			if(jWait.is(':visible')){//顯示內頁
				jIframe.show();
				jWait.remove();
			}
		});
		//初始化接口
		function initModalWin(){
			try{
				modalWin.callback=callbackModal;
				modalWin.unloadme=_this.removeModal;
			}catch(ex){}
		}
		//模式窗口回調
		function callbackModal(v){
			_this.removeModal();
			if(v!=null)callback(v);
		}
	}
	this.showModal=function(title,content,w,h,onRemove)
	{
		if(bShowModal)return false;//只能彈出一個模式窗口
		layerShadow=settings.layerShadow;
		w=w?w:settings.modalWidth;h=h?h:settings.modalHeight;
		jModal=$('<div class="xheModal" style="width:'+(w-1)+'px;height:'+h+'px;margin-left:-'+Math.ceil(w/2)+'px;'+(isIE&&browerVer<=7.0?'':'margin-top:-'+Math.ceil(h/2)+'px')+'">'+(settings.modalTitle?'<div class="xheModalTitle"><span class="xheModalClose" title="關閉 (Esc)"></span>'+title+'</div>':'')+'<div class="xheModalContent"></div></div>').appendTo('body');
		jOverlay=$('<div class="xheModalOverlay"></div>').appendTo('body');
		if(layerShadow>0)jModalShadow=$('<div class="xheModalShadow" style="width:'+jModal.outerWidth()+'px;height:'+jModal.outerHeight()+'px;margin-left:-'+(Math.ceil(w/2)-layerShadow-2)+'px;'+(isIE&&browerVer<=7.0?'':'margin-top:-'+(Math.ceil(h/2)-layerShadow-2)+'px')+'"></div>').appendTo('body');

		$('.xheModalContent',jModal).css('height',h-(settings.modalTitle?$('.xheModalTitle').outerHeight():0)).html(content);

		if(isIE&&browerVer===6.0)jHideSelect=$('select:visible').css('visibility','hidden');//隱藏覆蓋的select
		$('.xheModalClose',jModal).click(_this.removeModal);

		jOverlay.show();if(layerShadow>0)jModalShadow.show();jModal.show();
		bShowModal=true;
		onModalRemove=onRemove;
	}
	this.removeModal=function(){if(jHideSelect)jHideSelect.css('visibility','visible');jModal.html('').remove();if(layerShadow>0)jModalShadow.remove();jOverlay.remove();if(onModalRemove)onModalRemove();bShowModal=false;};
	this.showDialog=function(content)
	{
		var jDialog=$('<div class="xheDialog"></div>'),jContent=$(content),jSave=$('#xheSave',jContent);
		if(jSave.length===1)
		{
			jContent.find('input[type=text],select').keypress(function(ev){if(ev.which===13){jSave.click();return false;}});
			jContent.find('textarea').keydown(function(ev){if(ev.ctrlKey&&ev.which===13){jSave.click();return false;}});
			jSave.after(' <input type="button" id="xheCancel" value="取消" />');
			$('#xheCancel',jContent).click(_this.hidePanel);
			if(!settings.clickCancelDialog)
			{
				bClickCancel=false;//關閉點擊隱藏
				var jFixCancel=$('<div class="xheFixCancel"></div>').appendTo('body').mousedown(returnFalse);
				var xy=_jArea.offset();
				jFixCancel.css({'left':xy.left,'top':xy.top,width:_jArea.outerWidth(),height:_jArea.outerHeight()})
			}
			jDialog.mousedown(function(){bDisableHoverExec=true;})//點擊對話框禁止懸停執行
		}
		jDialog.append(jContent);
		_this.showPanel(jDialog);
		if(!isIE)setTimeout(function(){jDialog.find('input[type=text],textarea').filter(':visible').filter(function(){return $(this).css('visibility')!=='hidden';}).eq(0).focus();},10);//定位首個可見輸入表單項,延遲解決opera無法設置焦點
	}
	this.showPanel=function(content)
	{
		if(!ev.target)return false;
		_jPanel.html('').append(content).css('left',-999).css('top',-999);
		_jPanelButton=$(ev.target).closest('a').addClass('xheActive');
		var xy=_jPanelButton.offset();
		var x=xy.left,y=xy.top;y+=_jPanelButton.outerHeight()-1;
		_jCntLine.css({'left':x+1,'top':y,'width':_jPanelButton.width()}).show();
		if((x+_jPanel.outerWidth())>document.body.clientWidth)x-=(_jPanel.outerWidth()-_jPanelButton.outerWidth());//向左顯示面板
		var layerShadow=settings.layerShadow;
		if(layerShadow>0)_jShadow.css({'left':x+layerShadow,'top':y+layerShadow,'width':_jPanel.outerWidth(),'height':_jPanel.outerHeight()}).show();
		_jPanel.css({'left':x,'top':y}).show();
		bQuickHoverExec=bShowPanel=true;
	}
	this.hidePanel=function(){if(bShowPanel){_jPanelButton.removeClass('xheActive');_jShadow.hide();_jCntLine.hide();_jPanel.hide();bShowPanel=false;if(!bClickCancel){$('.xheFixCancel').remove();bClickCancel=true;};bQuickHoverExec=bDisableHoverExec=false;lastAngle=null;}}
	this.exec=function(cmd)
	{
		_this.focus();
		_this.hidePanel();
		_this.saveBookmark();
		var tool=arrTools[cmd];
		if(!tool)return false;//無效命令
		if(ev===null)//非鼠標點擊
		{
			ev={};
			var btn=_jTools.find('.xheButton[name='+cmd+']');
			if(btn.length===1)ev.target=btn;//設置當前事件焦點
		}
		if(tool.e)tool.e.call(_this)//插件事件
		else//內置工具
		{
			cmd=cmd.toLowerCase();
			switch(cmd)
			{
				case 'cut':
					try{_doc.execCommand(cmd);if(!_doc.queryCommandSupported(cmd))throw 'Error';}
					catch(ex){alert('您的瀏覽器安全設置不允許使用剪下操作，請使用鍵盤快捷鍵(Ctrl + X)來完成');};
					break;
				case 'copy':
					try{_doc.execCommand(cmd);if(!_doc.queryCommandSupported(cmd))throw 'Error';}
					catch(ex){alert('您的瀏覽器安全設置不允許使用複製操作，請使用鍵盤快捷鍵(Ctrl + C)來完成');}
					break;
				case 'paste':
					try{_doc.execCommand(cmd);if(!_doc.queryCommandSupported(cmd))throw 'Error';}
					catch(ex){alert('您的瀏覽器安全設置不允許使用貼上操作，請使用鍵盤快捷鍵(Ctrl + V)來完成');}
					break;
				case 'pastetext':
					if(window.clipboardData)_this.pasteText(window.clipboardData.getData('Text', true));
					else _this.showPastetext();
					break;
				case 'blocktag':
					var menuBlocktag=[];
					$.each(arrBlocktag,function(n,v){menuBlocktag.push({s:'<'+v.n+'>'+v.t+'</'+v.n+'>',v:'<'+v.n+'>',t:v.t});});
					_this.showMenu(menuBlocktag,function(v){_this._exec('formatblock',v);});
					break;
				case 'fontface':
					var menuFontname=[];
					$.each(arrFontname,function(n,v){v.c=v.c?v.c:v.n;menuFontname.push({s:'<span style="font-family:'+v.c+'">'+v.n+'</span>',v:v.c,t:v.n});});
					_this.showMenu(menuFontname,function(v){_this._exec('fontname',v);});
					break;
				case 'fontsize':
					var menuFontsize=[];
					$.each(arrFontsize,function(n,v){menuFontsize.push({s:'<span style="font-size:'+v.s+';">'+v.t+'('+v.s+')</span>',v:n+1,t:v.t});});
					_this.showMenu(menuFontsize,function(v){_this._exec('fontsize',v);});
					break;
				case 'fontcolor':
					_this.showColor(function(v){_this._exec('forecolor',v);});
					break;
				case 'backcolor':
					_this.showColor(function(v){if(isIE)_this._exec('backcolor',v);else{setCSS(true);_this._exec('hilitecolor',v);setCSS(false);}});
					break;
				case 'align':
					_this.showMenu(menuAlign,function(v){_this._exec(v);});
					break;
				case 'list':
					_this.showMenu(menuList,function(v){_this._exec(v);});
					break;
				case 'link':
					_this.showLink();
					break;
				case 'img':
					_this.showImg();
					break;
				case 'flash':
					_this.showEmbed('Flash',htmlFlash,'application/x-shockwave-flash','clsid:d27cdb6e-ae6d-11cf-96b8-4445535400000',' wmode="opaque" quality="high" menu="false" play="true" loop="true" allowfullscreen="true"',settings.upFlashUrl,settings.upFlashExt);
					break;
				case 'media':
					_this.showEmbed('Media',htmlMedia,'application/x-mplayer2','clsid:6bf52a52-394a-11d3-b153-00c04f79faa6',' enablecontextmenu="false" autostart="false"',settings.upMediaUrl,settings.upMediaExt);
					break;
				case 'emot':
					_this.showEmot();
					break;
				case 'table':
					_this.showTable();
					break;
				case 'source':
					_this.toggleSource();
					break;
				case 'preview':
					_this.showPreview();
					break;
				case 'print':
					_win.print();
					break;
				case 'fullscreen':
					_this.toggleFullscreen();
					break;
				case 'about':
					_this.showAbout();
					break;
				default:
					_this._exec(cmd);
					break;
			}
		}
		ev=null;
	}
	this._exec=function(cmd,param,noFocus)
	{
		if(!noFocus)_this.focus();
		var state;
		if(param!==undefined)state=_doc.execCommand(cmd,false,param);
		else state=_doc.execCommand(cmd,false,null);
		return state;
	}
	function checkDblClick(ev)
	{
		var target=ev.target,tool=arrDbClick[target.tagName.toLowerCase()];
		if(tool)
		{
			if(tool==='Embed')//自動識別Flash和多媒體
			{
				var arrEmbed={'application/x-shockwave-flash':'Flash','application/x-mplayer2':'Media'};
				tool=arrEmbed[target.type.toLowerCase()];
			}
			_this.exec(tool);
		}
	}
	function checkEsc(ev)
	{
		if(ev.which===27)
		{
			if(bShowModal)_this.removeModal();
			else if(bShowPanel)_this.hidePanel();
			return false;
		}
	}
	function loadReset(){setTimeout(_this.setSource,10);}
	function saveResult(){_this.getSource();};
	function cleanPaste(ev){
		var cleanPaste=settings.cleanPaste;
		if(cleanPaste===0||bSource||bCleanPaste)return true;
		bCleanPaste=true;//解決IE右鍵貼上重複產生paste的問題
		_this.saveBookmark();
		var tag=isIE?'pre':'div',jDiv=$('<'+tag+' class="xhe-paste" style="top:'+_jWin.scrollTop()+'px;">&nbsp;</'+tag+'>',_doc),div=jDiv[0],sel=_this.getSel(),rng=_this.getRng(true);
		$(_doc.body).append(jDiv);
		if(isIE){
			rng.moveToElementText(div);
			rng.select();
			rng.execCommand('Paste');
			ev.preventDefault();
		}
		else{
			rng.selectNodeContents(div);
			sel.removeAllRanges();
			sel.addRange(rng);
		}

		setTimeout(function(){
			var bText=(cleanPaste===3),sPaste;
			if(bText)sPaste=jDiv.text();
			else{
				var jTDiv=$('.xhe-paste',jDiv),arrHtml=[];
				if(jTDiv.length===0)jTDiv=jDiv;
				jTDiv.each(function(i,n){arrHtml.push(n.innerHTML);});
				sPaste=arrHtml.join('<br />');
			}
			jDiv.remove();
			_this.loadBookmark();
			if(bText)_this.pasteText(sPaste);
			else{
				sPaste=_this.cleanHTML(sPaste);
				sPaste=_this.cleanWord(sPaste);
				sPaste=_this.formatXHTML(sPaste);
				_this.pasteHTML(sPaste);
			}		
			bCleanPaste=false;
		},0);
	}
	function setCSS(css)
	{
		try{_this._exec('styleWithCSS',css,true);}
		catch(e)
		{try{_this._exec('useCSS',!css,true);}catch(e){}}
	}
	function setOpts()
	{
		if(bInit&&!bSource)
		{
			setCSS(false);
			try{_this._exec('enableObjectResizing',true,true);}catch(e){}
			//try{_this._exec('enableInlineTableEditing',false,true);}catch(e){}
			if(isIE)try{_this._exec('BackgroundImageCache',true,true);}catch(e){}
		}
	}
	function forcePtag(ev)
	{
		if(bSource||ev.which!==13||ev.shiftKey||ev.ctrlKey||ev.altKey)return true;
		var pNode=_this.getParent('p,h1,h2,h3,h4,h5,h6,pre,address,div,li');
		if(pNode.is('li'))return true;
		if(settings.forcePtag){if(pNode.length===0)_this._exec('formatblock','<p>');}
		else
		{
			_this.pasteHTML('<br />');
			if(isIE&&pNode.length>0&&_this.getRng().parentElement().childNodes.length===2)_this.pasteHTML('<br />');
			return false;
		}
	}
	function fixFullHeight()
	{
		if(!isMozilla&&!isSafari)
		{
			if(bFullscreen)_jArea.height('100%').css('height',_jArea.outerHeight()-_jTools.outerHeight());
			if(isIE)_jTools.hide().show();
		}
	}
	function fixAppleSel(e)
	{
		e=e.target;
		if(e.tagName.match(/(img|embed)/i))
		{
			var sel=_this.getSel(),rng=_this.getRng(true);
			rng.selectNode(e);
			sel.removeAllRanges();
			sel.addRange(rng);
		}
	}
	function xheAttr(jObj,n,v)
	{
		if(!n)return false;
		var kn='_xhe_'+n;
		if(v)//設置屬性
		{
			if(urlType)v=getLocalUrl(v,urlType,urlBase);
			jObj.attr(n,urlBase?getLocalUrl(v,'abs',urlBase):v).removeAttr(kn).attr(kn,v);
		}
		return jObj.attr(kn)||jObj.attr(n);
	}
	function clickCancelPanel(){if(bClickCancel)_this.hidePanel();}
	function checkShortcuts(event)
	{
		if(bSource)return true;
		var code=event.which,special=specialKeys[code],sChar=special?special:String.fromCharCode(code).toLowerCase();
		sKey='';
		sKey+=event.ctrlKey?'ctrl+':'';sKey+=event.altKey?'alt+':'';sKey+=event.shiftKey?'shift+':'';sKey+=sChar;

		var cmd=arrShortCuts[sKey],c;
		for(c in cmd)
		{
			c=cmd[c];
			if($.isFunction(c)){if(c.call(_this)===false)return false;}
			else{_this.exec(c);return false;}//按鈕獨佔快捷鍵
		}
	}
	function is(o,t)
	{
		var n = typeof(o);
		if (!t)return n != 'undefined';
		if (t === 'array' && (o.hasOwnProperty && o instanceof Array))return true;
		return n === t;
	}
	function getLocalUrl(url,urlType,urlBase)//絕對地址：abs,根地址：root,相對地址：rel
	{
		if( url.match(/^(\w+):\/\//i) && !url.match(/^https?:/i) )return url;
		var baseUrl=urlBase?$('<a href="'+urlBase+'" />')[0]:location,protocol=baseUrl.protocol,host=baseUrl.host,hostname=baseUrl.hostname,port=baseUrl.port,path=baseUrl.pathname.replace(/\\/g,'/').replace(/[^\/]+$/i,'');
		if(port==='')port='80';
		if(path==='')path='/';
		else if(path.charAt(0)!=='/')path='/'+path;//修正IE path
		url=$.trim(url);
		//刪除域路徑
		if(urlType!=='abs')url=url.replace(new RegExp(protocol+'\\/\\/'+hostname.replace(/\./g,'\\.')+'(?::'+port+')'+(port==='80'?'?':'')+'(\/|$)','i'),'/');
		//刪除根路徑
		if(urlType==='rel')url=url.replace(new RegExp('^'+path.replace(/([\/\.\+\[\]\(\)])/g,'\\$1'),'i'),'');
		//加上根路徑
		if(urlType!=='rel')
		{
			if(!url.match(/^(https?:\/\/|\/)/i))url=path+url;
			if(url.charAt(0)==='/')//處理根路徑中的..
			{
				var arrPath=[],arrFolder = url.split('/'),folder,i,l=arrFolder.length;
				for(i=0;i<l;i++)
				{
					folder=arrFolder[i];
					if(folder==='..')arrPath.pop();
					else if(folder!==''&&folder!=='.')arrPath.push(folder);
				}
				if(arrFolder[l-1]==='')arrPath.push('');
				url='/'+arrPath.join('/');
			}
		}
		//加上域路徑
		if(urlType==='abs'&&!url.match(/^https?:\/\//i))url=protocol+'//'+host+url;
		url=url.replace(/(https?:\/\/[^:\/?#]+):80(\/|$)/i,'$1$2');//省略80端口
		return url;
	}
	function checkFileExt(filename,limitExt)
	{
		if(limitExt==='*'||filename.match(new RegExp('\.('+limitExt.replace(/,/g,'|')+')$','i')))return true;
		else
		{
			alert('上傳文件擴展名必需為: '+limitExt);
			return false;
		}
	}
	function formatBytes(bytes)
	{
		var s = ['Byte', 'KB', 'MB', 'GB', 'TB', 'PB'];
		var e = Math.floor(Math.log(bytes)/Math.log(1024));
		return (bytes/Math.pow(1024, Math.floor(e))).toFixed(2)+s[e];
	}
	function returnFalse(){return false;}
}
$(function(){
	$.fn.oldVal=$.fn.val;
	$.fn.val=function(value)
	{
		var _this=this,editor;
		if(value===undefined)if(_this[0]&&(editor=_this[0].xheditor))return editor.getSource();else return _this.oldVal();//讀
		return _this.each(function(){if(editor=this.xheditor)editor.setSource(value);else _this.oldVal(value);});//寫
	}
	$('textarea').each(function(){
		var self=$(this),xhClass=self.attr('class').match(/(?:^|\s)xheditor(?:\-(m?full|simple|mini))?(?:\s|$)/i);
		if(xhClass)self.xheditor(xhClass[1]?{tools:xhClass[1]}:null);
	});
});

})(jQuery);