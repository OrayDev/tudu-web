<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.edit_contact_group}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>

</head>
<body>
<form id="theform" action="/contact/group.save" method="post" class="tab-panel">
<div class="tab_panel">
	<div class="toolbar">
        <div>
        <button class="btn" type="submit" name="save">{{$LANG.save}}</button><button class="btn" type="button" name="back">{{$LANG.back}}</button>
        </div>
    </div>
	<div class="tab-panel-body">
	    <div class="settingbox" style="padding:18px">
		<div style="padding:10px 5px">
		<label for="groupname">
		{{$LANG.group_name}}{{$LANG.cln}}
		<input type="text" class="input_text" name="groupname" id="groupname" value="{{$group.groupname}}" />
		<input type="hidden" name="groupid" value="{{$group.groupid}}"/>
		<input type="hidden" name="editmember" value="1"/>
		<input type="hidden" name="bgcolor" value="{{$group.bgcolor}}"/>
		</label>
		</div>
		<div id="contact-selector-ct"></div>
        </div>
    </div>
    <div class="toolbar">
        <div>
        <button class="btn" type="submit" name="save">{{$LANG.save}}</button><button class="btn" type="button" name="back">{{$LANG.back}}</button>
        </div>
    </div>
</div>
</form>
<script type="text/javascript">
$(function(){

	TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.edit_contact_group}}');
    {{if $group.groupid}}
	TOP.Frame.hash('m=contact/group.modify&gid={{$group.groupid}}');
	{{else}}
	TOP.Frame.hash('m=contact/group.modify');
	{{/if}}

	var selected = [];
	{{foreach item=contact from=$contacts}}
	selected.push(
		{contactid:'{{$contact.contactid}}'}
	);
	{{/foreach}}

	var selector = new TOP.ContactSelector({appendTo: $('#contact-selector-ct'), enableGroup: false, selected: selected, jq: $});

	var panel = TOP.Cookie.get('CONTACT-PANEL');
	if (!panel) {
		panel = 'lastcontact';
	}
	selector.switchPanel(panel);

	$('button[name="back"]').click(function(){
		history.back();
	});

	$('#theform').submit(function(){return false;});
	$('#theform').submit(function(){
		if (!$('input[name="groupname"]').val()) {
			$('input[name="groupname"]').val();
            return TOP.showMessage(TOP.TEXT.MISSING_CONTACT_GROUP_NAME);
        }

		var form = $(this);
		var data = form.serializeArray();
		TOP.showMessage(TOP.TEXT.POSTING_DATA, 0, 'success');
        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: data,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, ret.success ? 'success' : null);
                if(ret.success) {
                	TOP.Contact.clear();
                	{{if $back}}
                	location = '{{$back}}';
                	{{else}}
                    location = '/contact/?type=contact&groupid=' + ret.data.groupid;
                    {{/if}}
                }
                form.find('input, button').attr('disabled', false);
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESSING_ERROR);
                form.find('input, button').attr('disabled', false);
            }
        });
	});

});

</script>
</body>
</html>
