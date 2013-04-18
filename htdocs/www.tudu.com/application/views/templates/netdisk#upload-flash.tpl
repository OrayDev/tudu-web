<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.netdisk}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script type="text/javascript">
function getTop() {
	return parent;
}

var TOP = getTop();
</script>
<script src="{{$options.sites.static}}/js/upload2.js?1001" type="text/javascript"></script>

</head>
<body style="padding:0;margin:0">
<div class="upload_place"><button name="upload">{{$LANG.browse}}</button><div class="upload_place_replacer"><span id="nd-upload-holder"></span></div><span class="gray" style="margin:5px;">{{$LANG.ajax_upload_hint}}</span></div>


<script type="text/javascript">
<!--
$(function(){
	var upload = new TuduUpload({
        uploadUrl: '{{$options.sites.file}}{{$uploadurl}}',
        flashUrl: '/images/swfupload.swf',
        buttonWidth: '50',
        buttonHeight: '20',
        buttonPlaceholderId: 'nd-upload-holder',
        onFileQueued: function(){this.startUpload();}
    });

    var uploadQueue = new UploadQueue({upload: upload, jq: TOP.getJQ()});
    uploadQueue.appendTo(document.body);

    upload.bind('uploadComplete', function(){
    	TOP.NDUpload.uploaded = true;
    	TOP.NDUpload.uploading = false;
        TOP.NDUpload.confirmEnabled(true);
        TOP.NDUpload.setSpeed('0KB/s');
        TOP.NDUpload.setFilecount(this.getFileNum(), this.getCompleteNum());
    }).bind('uploadStart', function(){
    	TOP.NDUpload.confirmEnabled(false);
    	TOP.NDUpload.uploading = true;
    }).bind('uploadProgress', function(file, uploaded, total){
    	var costTime = this.getUploadTime();

        var speed = uploaded / costTime;

        TOP.NDUpload.setProgress(Math.round(this.totalProgress() * 100/100) + '%');
        TOP.NDUpload.setSpeed(formatFileSize(speed * 1000) + '/s');
        TOP.NDUpload.setFilecount(this.getFileNum(), this.getCompleteNum());
    });
});
-->
</script>
</body>
</html>
