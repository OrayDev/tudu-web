<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.init_password}}</title>
<style type="text/css">
<!--
html, body, form, div,p{
    margin:0;
    padding:0;
}
body{
    font-family: Arial, Helvetica, sans-serif;
    font-size:12px;
    line-height:1.6em;
}
.f14{
    font-size:14px;
}
.red{
    color:#f00;
}
.gray{
    color:#999;
}
.lock-content{
    background:url(/images/icon/panel_bg.gif) repeat-y;
}
.lock-inner{
    background:url(/images/icon/panel_top.gif) 0 top no-repeat;
}
.lock{
    background:url(/images/icon/panel_bottom.gif) 0 bottom no-repeat;
    padding:30px 20px;
    min-height:110px;
    _height:110px;
    position:relative;
}
.lock input.text{
    width:180px;
    border:1px solid #737373;
    background:url(/images/icon/dot.gif) #fff repeat-x;
    vertical-align:middle;
    margin-right:5px;
    font-family:verdana,simsun,sans-serif;
    font-size:14px;
    font-weight:bold;
    height:22px;
    line-height:22px;
    padding:2px 3px;
}
.lock input.focus{
    background:url(/images/icon/line_yellow.jpg) repeat-x;
    background-color:#fefbbd;
    border:1px solid #ac8539;
}
.lock-btn{
    height: 26px;
    line-height: normal;
    padding: 3px 10px;
    vertical-align: middle;
    font-weight:bold;
    font-size:14px;
}
.lock-logo{
    margin-bottom:10px;
}
.lock-title{
    background:url(/images/icon/lock.gif) 0 0 no-repeat;
    color:#2b425d;
    font-size:14px;
    height:50px;
    line-height:50px;
    padding-left:50px;
    margin-bottom:15px;
}
.tips{
    position:absolute;
    color:#f00;
    top:75px;
    left:124px;
    text-align:center;
}
.lock th{
    font-weight:normal;
    font-size:14px;
}
.lock td,th{
    padding:8px 0;
}

ul li {
	margin-bottom:10px
}
-->
</style>
<script type="text/javascript" src="{{$options.sites.static}}/js/jquery-1.4.4.js"></script>
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1003" type="text/javascript"></script>
</head>
<body>

<div style="width:428px;margin:135px auto 0;">
    <div class="lock-logo"><img src="/logo?oid={{$org.orgid}}" /></div>
    <div class="lock-content" id="install-ie" style="display:none;">
        <div class="lock-inner">
            <div class="lock">
            <table border="0" cellspacing="1" cellpadding="4">
                <tr>
                    <td>
                    <p><strong style="color:#2b425d; font-size:16px;">在线安装截屏插件</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <ul style="padding:0 0 0 14px;margin:0;line-height:22px;font-size:14px"><li>在安装过程中，请留意您浏览器是否出现提示信息，若出现，请点击“允许”或“继续”完成安装。</li><li>若在线安装不成功，请下载<a href="/download/activex_prtscrn_18706.exe">安装文件</a>进行手动安装</li><li>待安装成功后，点击[返回]即可使用。</li></ul>
                    </td>
                </tr>
                <tr>
                    <td><button name="back" type="button">返回</button></td>
                </tr>
            </table>
            </div>
        </div>
    </div>

    <div class="lock-content" id="install-ff" style="display:none;">
        <div class="lock-inner">
            <div class="lock">
            <table border="0" cellspacing="1" cellpadding="4">
                <tr>
                    <td>
                    <p><strong style="color:#2b425d; font-size:16px;">在线安装截屏插件</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>
                    <p style="font-size:14px;margin-bottom:5px;"><strong>FireFox</strong>用户请下载并安装以下文件，并重启浏览器生效</p>
                    <ul style="padding:0 0 0 14px;;margin:0;line-height:22px;font-size:14px"><li><a href="/download/activex_prtscrn_18706.exe">在线截屏控件</a></li><li><a href="https://addons.mozilla.org/addon/sunlogin-controller-plugin/">Oray插件适配器</a></li></ul>
                    </td>
                </tr>
                <tr>
                    <td><button name="back" type="button">返回</button></td>
                </tr>
            </table>
            </div>
        </div>
    </div>

    <div class="lock-content" id="success-div" style="display:none;">
        <div class="lock-inner">
            <div class="lock">
            <table border="0" cellspacing="1" cellpadding="4">
                <tr>
                    <td colspan="2">
                    <p style="text-align:center"><strong style="color:#2b425d; font-size:16px;">截屏插件已成功安装</strong></p>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                    <ul style="padding:0 0 0 14px;;margin:0;line-height:22px;"><li>你已经安装在线截屏插件，现在您可以体验在线截屏功能了</li></ul>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><button name="back" type="button">返回</button></td>
                </tr>
            </table>
            </div>
        </div>
    </div>

    <div class="lock-content" id="unsupport-div" style="display:none;">
        <div class="lock-inner">
            <div class="lock">
            <table border="0" cellspacing="1" cellpadding="4">
                <tr>
                    <td colspan="2">
                    <p style="text-align:center"><strong style="color:#2b425d; font-size:16px;">不能检测您的浏览器版本或尚不支持</strong></p>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                    <ul style="padding:0 0 0 14px;margin:0;line-height:22px;"><li>截屏功能暂时只支持Firefox和IE内核浏览器</li><li>你可以尝试下载安装包进行手工安装</li></ul>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><button name="back" type="button">返回</button></td>
                </tr>
            </table>
            </div>
        </div>
    </div>
</div>

<div id="activex-ct">
</div>
<script type="text/javascript">
<!--
var ua = navigator.userAgent.toLowerCase(),
    protocol = location.protocol,
    host     = location.host;

var isIE = -1 != ua.indexOf('msie'),
    isFF = -1 != ua.indexOf('firefox'),
    isWEBKIT = -1 != ua.indexOf('webkit') || -1 != ua.indexOf('khtml'),
    isOPERA  = -1 != ua.indexOf('opera');

var plugins = {
    capture: {id: 'capture', 'classid': 'CLSID:05B7C812-0B49-4626-AF71-8082013509A7', 'codebase': protocol + '//'+host+'/download/activex_prtscrn_18706.cab#version=2,2,9,18706', attrs: {width: 1, height: 1}},
    upload: {id: 'upload', 'classid': 'CLSID:86EDE142-7BC9-48F2-91E4-E3FE9F598484', 'codebase': protocol + '//'+host+'/download/activex_prtscrn_18706.cab#version=2,2,9,18706', attrs: {width: 1, height: 1}},
    npffadapter: {id: 'npffadapter', 'classid': 'CLSID:0CFECBBF-CC45-4EF4-97D9-984963438F01', 'codebase': '', attrs: {width: 1, height: 1}}
};

window.onload = function() {

    // 不支持
    if (!isIE && !isFF) {
        document.getElementById('unsupport-div').style.display = '';
    } else {
        for(var k in plugins) {
            if (isIE && k == 'npffadapter') {
                continue ;
            }

            document.getElementById('activex-ct').innerHTML += createActiveX(plugins[k].id, plugins[k].classid, plugins[k].codebase, plugins[k].attrs);
        }

        try {
            var obj = document.getElementById('capture');

            if (!obj.getVersion()) {
                if (isIE) {
             	    document.getElementById('install-ie').style.display = '';
                } else {
                	document.getElementById('install-ff').style.display = '';
                }
            } else {
            	document.getElementById('success-div').style.display = '';
            }
        } catch (e) {
           	 if (isIE) {
                 document.getElementById('install-ie').style.display = '';
             } else if (isFF) {
                 document.getElementById('install-ff').style.display = '';
             } else {
           	     document.getElementById('unsupport-div').style.display = '';
             }
        }
    }

    var btns = document.getElementsByName('back');
    for (var i = 0, c = btns.length; i < c; i++) {
        btns[i].onclick = function() {
            location = '/frame{{$back|default:''}}';
        }
    }
}

// 创建ActiveX对象
function createActiveX(id, classId, codebase, attrs) {
	var tagName = 'object';

    if (isFF) {
        tagName = 'embed';
    }

    var html = [
        '<' + tagName,
        'id="' + id + '"',
        'classid="' + classId + '"',
        'codebase="' + codebase + '"'
    ];

    if (isFF) {
        var protocol = location.protocol,
            host     = location.host;
        html.push('type="application/x-oray-npffadapter" pluginspage="'+protocol+'//'+host+'/download/npffadapter.xpi"');
    }

    if (typeof(attrs) == 'object') {
        for (var k in attrs) {
            html.push(k + '="' + attrs[k] + '"');
        }
    }

    html.push('>');

    html = html.join(' ') + '</' + tagName + '>';

    return html;
}

-->
</script>
</body>
</html>