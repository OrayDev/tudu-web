{{include file="^icon.tpl"}}
<script src="/static/js?f=lang&lang={{$user.option.language}}&ver=1015" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/kindeditor-4.1.1/kindeditor-min.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/kindeditor-4.1.1/lang/zh_CN.js?1001" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/all.js?1037" type="text/javascript"></script>

<script src="{{$options.sites.static}}/js/talk.js?1001" type="text/javascript"></script>
<script type="text/javascript">
var _FILECGI = {
	upload: '{{$options.sites.file}}{{$upload.cgi.upload}}',
	download: '{{$options.sites.file}}{{$upload.cgi.download}}',
	swfupload: '/images/swfupload.swf'
};
var _ORGNAME = '{{$org.orgname}}';
</script>