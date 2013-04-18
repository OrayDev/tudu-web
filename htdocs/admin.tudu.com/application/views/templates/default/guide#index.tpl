<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度后台新手指引</title>
<style type="text/css">
body{margin:0;}
.pic{display:inline-block;vertical-align:middle;width:1280px;height:800px;}
.pic02{background:url("{{$basepath}}/img/guide/home.gif") no-repeat;}
.pic03{background:url("{{$basepath}}/img/guide/adduser.gif") no-repeat;}
.pic04{background:url("{{$basepath}}/img/guide/dept_1.gif") no-repeat;}
.pic05{background:url("{{$basepath}}/img/guide/dept_2.gif") no-repeat;}
.pic06{background:url("{{$basepath}}/img/guide/dept_3.gif") no-repeat;}
.pic07{background:url("{{$basepath}}/img/guide/dept_4.gif") no-repeat;}
.pic08{background:url("{{$basepath}}/img/guide/addmore.gif") no-repeat;}
.pic09{background:url("{{$basepath}}/img/guide/group.gif") no-repeat;}
.pic10{background:url("{{$basepath}}/img/guide/create_group.gif") no-repeat;}
.pic11{background:url("{{$basepath}}/img/guide/finish.gif") no-repeat;}
#pic02 .view{top:300px;left:15px;position:absolute;width:400px;height:125px;}
#pic02 .view a{display:block;width:400px;height:125px;}
#pic03 .view{top:236px;left:193px;position:absolute;width:98px;height:22px;}
#pic03 .view a{display:block;width:98px;height:22px;}
#pic08 .view{top:607px;left:270px;position:absolute;width:186px;height:58px;}
#pic08 .view a{display:block;width:186px;height:58px;}
#pic11 .view{top:420px;left:564px;position:absolute;width:143px;height:40px;}
#pic11 .close{top:293px;left:780px;position:absolute;width:36px;height:36px;cursor:pointer;}
#pic11 .view a{display:block;width:143px;height:40px;}
</style>
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/guide.js" type="text/javascript"></script>
<script type=text/javascript>
<!--
$(function(){
    Guide.initView();
});
-->
</script>
</head>
<body>
    <div id="pic" style="position: absolute;width:1280px;height:800px;">
        <div id="pic02">
            <span class="pic pic02"></span>
            <div class="view"><a href="javascript:void(0)"></a></div>
        </div>
        <div id="pic03" style="display:none;">
            <span class="pic pic03"></span>
            <div class="view"><a href="javascript:void(0)"></a></div>
        </div>
        <div id="pic04" style="display:none;">
            <span class="pic pic04"></span>
        </div>
        <div id="pic05" style="display:none;">
            <span class="pic pic05"></span>
        </div>
        <div id="pic06" style="display:none;">
            <span class="pic pic06"></span>
        </div>
        <div id="pic07" style="display:none;">
            <span class="pic pic07"></span>
        </div>
        <div id="pic08" style="display:none;">
            <span class="pic pic08"></span>
            <div class="view"><a href="javascript:void(0)"></a></div>
        </div>
        <div id="pic09" style="display:none;">
            <span class="pic pic09"></span>
        </div>
        <div id="pic10" style="display:none;">
            <span class="pic pic10"></span>
        </div>
        <div id="pic11" style="display:none;">
            <span class="pic pic11"></span>
            <div class="close"></div>
            <div class="view"><a href="javascript:void(0)"></a></div>
        </div>
    </div>
    <div id="fix-height" style="position: absolute;height: 100%;width: 1px;left: -100;top:0"></div>
</body>
</html>