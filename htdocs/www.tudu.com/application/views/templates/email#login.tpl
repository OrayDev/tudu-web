<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.email_login}}</title>

</head>
<body>
{{$loginform}}
<div id="unsupport" style="display:none">{{$LANG.unsupported_login_mailbox|sprintf:$email.address}}</div>
<script type="text/javascript">
<!--
window.onload = function(){
	var form = document.getElementsByTagName('form');

	if (form.length <= 0) {
	    document.getElementById('unsupport').style.display = '';
	} else {
	    form[0].submit();
	}
};
-->
</script>
</body>
</html>
