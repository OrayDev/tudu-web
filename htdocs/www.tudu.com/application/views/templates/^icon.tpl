<link rel="shortcut icon" href="{{$smarty.const.PROTOCOL}}//{{if $user.orgid}}{{$user.orgid}}.tudu.com{{else}}{{$smarty.server.HTTP_HOST}}{{/if}}/favicon.ico" type="image/x-icon" />
{{assign var="ua" value=$smarty.server.HTTP_USER_AGENT|strtolower}}
{{if false !== strpos($ua, 'iphone') || false !== strpos($ua, 'ipad')}}
<link rel="apple-touch-icon-precomposed" href="{{$options.sites.static}}/images/icon/mobile/ios52.png" />
<link rel="apple-touch-icon-precomposed" size="72x72" href="{{$options.sites.static}}/images/icon/mobile/ios72.png" />
<link rel="apple-touch-icon-precomposed" size="114x114" href="{{$options.sites.static}}/images/icon/mobile/ios114.png" />
<link rel="apple-touch-icon-precomposed" size="144x144" href="{{$options.sites.static}}/images/icon/mobile/ios144.png" />
{{/if}}
{{if false !== strpos($ua, 'android')}}
<link rel="icon" href="{{$smarty.const.PROTOCOL}}//{{$user.orgid}}.tudu.com/favicon.ico" type="image/x-icon" />
{{/if}}