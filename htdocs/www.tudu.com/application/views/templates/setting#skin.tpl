<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.change_skin}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
</head>
<body>
	<div class="position">
    	<p><strong class="title">{{$LANG.change_skin}}</strong></p>
    </div>
<form action="" method="post" class="tab_panel">
	{{include file="setting^tab.tpl" tab="skin"}}
	<div class="tab-panel-body">
        	<div class="settingbox">
            	<div class="skinsetting" id="skinlist">
            	    <div{{if null === $user.option.skin || $user.option.skin == '8'}} class="skin_select"{{/if}}>
                       <input type="hidden" name="skin" value="8" />
                        <table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face8" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_8}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if null !== $user.option.skin && $user.option.skin == 0}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="0" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face0" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_0}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 4}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="4" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face4" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_4}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 2}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="2" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face2" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_2}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 1}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="1" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face1" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_1}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 6}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="6" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face6" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_6}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 5}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="5" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face5" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_5}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 3}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="3" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face3" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_3}}</td>
                          </tr>
                        </table>
                    </div>
                	<div{{if $user.option.skin == 7}} class="skin_select"{{/if}}>
                	   <input type="hidden" name="skin" value="7" />
                    	<table cellspacing="0" cellpadding="0">
                          <tr>
                            <td><img class="face7" src="../../images/icon/spacer.gif"></td>
                          </tr>
                          <tr>
                            <td>{{$LANG.skin_7}}</td>
                          </tr>
                        </table>
                    </div>

                </div>
            </div>
      	<div class="toolbar">
        	<div class="toolbar_tips"><p>{{$LANG.skin_tips}}</p></div>
        </div>
    </div>
</form>
<script type="text/javascript">
<!--
$(function(){
	TOP.Label.focusLabel('');
	TOP.Frame.title('{{$LANG.change_skin}}');
	TOP.Frame.hash('m=setting/skin');

	$('#skinlist div').click(function(){
		$('.skin_select').removeClass('skin_select');
		$(this).addClass('skin_select');

		var skin = $(this).find(':hidden[name="skin"]').val();

		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: {skin: skin},
			url: '/setting/save',
			success: function(ret){
				TOP.showMessage(ret.message, 5000, 'success');

				TOP.Frame.skin(skin);
				$('link[rel="stylesheet"]:eq(1)').attr('href', '{{$options.sites.static}}/css/skin_' + skin + '.css');
			},
			error: function() {
			    TOP.showMessage(TOP.TEXT.PRECESS_ERROR);
			}
		});
	});
});
-->
</script>
</body>
</html>
