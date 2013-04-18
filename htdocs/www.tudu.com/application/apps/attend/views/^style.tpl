<link href="{{$options.sites.static}}/css/common.css?1024" type="text/css" rel="stylesheet" />
{{if $user.option.language == 'en_US'}}
<link href="{{$options.sites.static}}/css/common_fixed_en.css?1009" type="text/css" rel="stylesheet" />
{{/if}}
<link href="{{$options.sites.static}}/css/skin_{{if null !== $user.option.skin}}{{$user.option.skin}}{{else}}8{{/if}}.css?1014" type="text/css" rel="stylesheet" />
