<link href="{{$options.sites.static}}/css/common.css?1040" type="text/css" rel="stylesheet" />
{{if $user.option.language == 'en_US'}}
<link href="{{$options.sites.static}}/css/common_fixed_en.css?1013" type="text/css" rel="stylesheet" />
{{/if}}
<link href="{{$options.sites.static}}/css/skin_{{if null !== $user.option.skin}}{{$user.option.skin}}{{else}}8{{/if}}.css?1018" type="text/css" rel="stylesheet" />
