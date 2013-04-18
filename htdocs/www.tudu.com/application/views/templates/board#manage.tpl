<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{{$LANG.board_manage}}</title>
{{include file="^style.tpl"}}
<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/jquery.extend.js?1009" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/frame.js?1030" type="text/javascript"></script>
<script src="{{$options.sites.static}}/js/selectboard.control.js" type="text/javascript"></script>

<script type="text/javascript">
<!--
var LH = 'm=board/manage';
if (top == this) {
    location = '/frame#' + LH;
}
-->
</script>

</head>
<body>
    <div class="position">
      <p><strong class="title">{{$LANG.manage_tudu_board}}</strong></p>
</div>
<form action="/board/attention" id="theform" method="post" class="tab_panel">
    {{include file="setting^tab.tpl" tab="board"}}
    <div class="tab-panel-body">
       <div class="settingbox">
         <div class="setting_account">
           <div class="settingdiv">
             <h3 class="setting_tit">{{$LANG.create_board_quick}}</h3>
             <div class="line_bold"></div>
             <div id="board-selector"></div>
           </div>
         </div>
       </div>
    </div>
    <div class="toolbar_position">
        <div class="toolbar">
            <div><button class="btn" type="submit">{{$LANG.save}}</button><button class="btn" type="button" name="cancel">{{$LANG.cancel}}</button></div>
        </div>
    </div>
</form>
<script type="text/javascript">
<!--
$(function(){
    TOP.Label.focusLabel('');
    TOP.Frame.title('{{$LANG.board_manage}}');
    TOP.Frame.hash(LH);

    var selected = [];
    {{foreach item=board from=$boards}}
    selected.push(
        {boardid:'{{$board.boardid}}'}
    );
    {{/foreach}}

    new BoardSelector({appendTo: $('#board-selector'), selected: selected, sort: true});
    $('div.selector_left_title').text('{{$LANG.board_sum}}{{$LANG.cln}}');
    $('div.selector_right_title').text('{{$LANG.already_add_board_quick}}{{$LANG.cln}}');

    $('button[name="cancel"]').click(function(){
        location = '/frame/home';
    });

    $('#theform').submit(function(){return false;});
    $('#theform').submit(function(){
        var form = $(this);
        var data = form.serializeArray();

        form.find('input, button').attr('disabled', true);

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: form.attr('action'),
            data: data,
            success: function(ret) {
                TOP.showMessage(ret.message, 5000, 'success');
                form.find('input, button').attr('disabled', false);

                if (ret.success) {
                    var _$ = TOP.getJQ();
                    _$('#user_board_list ul').empty();
                    if (ret.data) {
                        var boards = ret.data;

                        _$('#user_board_list').show();
                        _$('#user_board_title .tree-ec-icon').addClass('tree-elbow-minus');

                        for (var i = 0; i< boards.length; i++) {
                            TOP.Frame.Boards.append(boards[i].boardid, boards[i].boardname);
                        }
                    } else {
                        _$('#user_board_list ul').append('<li name="attention-board"><a href="/board/manage" target="main">{{$LANG.add_attention_board}}</a></li>');
                    }
                }
            },
            error: function(res) {
                TOP.showMessage(TOP.TEXT.PROCESS_ERROR);
                form.find('input, button').attr('disabled', false);
            }
        });
    });
});
-->
</script>
</body>
</html>