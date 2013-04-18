<script src="{{$options.sites.static}}/js/jquery-1.4.4.js" type="text/javascript"></script>
<script type="text/javascript">
function getCookie(name) {
    var reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)", "gi");
    var tmp = reg.exec(document.cookie);
    return unescape((tmp || [])[2] || "");
}

function setCookie(name, value) {
    document.cookie = name + '=' + escape(value)
            + ";expires=" + (new Date(2099, 12, 31)).toGMTString();
}

function refreshSeccode() {
    document.getElementById('img-seccode').src = '{{$options.sites.www}}/seccode/?ns=login&sz=100x36&rand=' + Math.random();
    document.getElementById('seccode-tr').style.display = '';
}

function checkSeccode(isshow) {
    if (isshow) {
        refreshSeccode();
    }
}

function login() {
    var name = document.getElementById("uid").value;
    if (name.indexOf('@') < 0) {
        alert('无效的登录用户名');
        document.getElementById("uid").focus();
        return false;
    }

    setCookie("uid", name);
    return true;
}

function setLocation(elm, n) {
    if(n > elm.value.length)
        n = elm.value.length;
    if(elm.createTextRange) {   // IE
        var textRange = elm.createTextRange();
        textRange.moveStart('character', n);
        textRange.collapse();
        textRange.select();
    } else if(elm.setSelectionRange) { // Firefox
        elm.setSelectionRange(n, n);
        elm.focus();
    }
}
</script>