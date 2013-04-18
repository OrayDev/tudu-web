<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.netdisk}}</title>
{{include file="^style.tpl"}}
<script type="text/javascript">
function getTop() {
    return parent;
}

var TOP = getTop();
</script>
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.ajaxupload.js?1001" type="text/javascript"></script>

</head>
<body style="padding:0;margin:0">
<div class="nd_ajax_upload_form">
<form id="uploadform" method="post" enctype="multipart/form-data" target="uploadframe" action="{{$options.sites.file}}{{$uploadurl}}&format=script&callback=uploadCallback">
<p class="gray" style="margin:5px;">{{$LANG.ajax_upload_hint}}</p>
<input type="file" id="filedata" name="filedata" /><input type="submit" value="{{$LANG.upload}}">
</form>
<iframe name="uploadframe" frameborder="0" style="display:none" src="javascript:void(0)"></iframe>
<div id="upload-status" class="upload-status" style="line-height:20px;display: none;">
<p><span class="icon icon_loading"></span>{{$LANG.uploading}}<span class="gray">({{$LANG.uploading_hint}})</span></p>
<p id="upload-file-name"></p>
</div>
<div id="upload-info" class="upload-status" style="line-height:20px;display: none;">
<p><span id="upload-result"></span><a href="javascript:void(0)" id="upload-next" style="margin-left:15px;">{{$LANG.go_on_upload}}</a></p>
<p id="uploaded-file-name"></p>
</div>
</div>

<div id="file-queue" class="upload_queue" style="height:220px;margin-top:10px;">

</div>

<script type="text/javascript">
<!--
var _AUTO_ID = 0;

function uploadCallback(data) {
	var filename =$('#filedata').val();

    $('#upload-info').show();
    $('#upload-status').hide();
    $('#uploaded-file-name').text(filename);

    if (data.success) {
        $('#upload-result').text('{{$LANG.upload_success}}');
        $('#file-queue').append('<div class="upload_item" id="file-'+_AUTO_ID+'"><p>'+filename+'</p><p class="item_info">{{$LANG.upload_success}}</p></div>');
        TOP.NDUpload.uploaded = true;
        TOP.NDUpload.uploading = false;
    } else {
        $('#upload-result').text('{{$LANG.upload_failure}}');
        $('#file-queue').append('<div class="upload_item" id="file-'+_AUTO_ID+'"><p>'+filename+'</p><p class="item_info red">{{$LANG.upload_failure}}</p></div>');
    }

    TOP.NDUpload.uploading = false;
    TOP.NDUpload.confirmEnabled(true);
}

$('#uploadform').submit(function(e){
    var form = $(this),
        filename = $('#filedata').val();
    form.hide();

    $('#upload-file-name').text(filename);
    $('#upload-status').show();
    TOP.NDUpload.confirmEnabled(false);
    TOP.NDUpload.uploading = true;

    _AUTO_ID++;
});

$('#upload-next').bind('click', function(){
	$('#uploadform').show();
	$('#filedata').val('');
	$('#upload-status').hide();
	$('#upload-info').hide();
});
-->
</script>
</body>
</html>
