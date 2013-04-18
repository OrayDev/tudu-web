<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<script type="text/javascript">
<!--
{{if $referer}}
var url = '{{$url|escape:"javascript"}}'.replace('%referer', encodeURIComponent(self == top ? window.location : top.location));
{{else}}
var url = '{{$url|escape:"javascript"}}';
{{/if}}
if(self != top) {
	top.location = url;
} else {
	window.location = url;
}
//-->
</script>
</head>
</html>