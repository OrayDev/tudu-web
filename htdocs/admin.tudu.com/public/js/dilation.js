(function($){$.extend($.fn,{validate:function(options){if(!this.length){options&&options.debug&&window.console&&console.warn("nothing selected, can't validate, returning nothing");return;}var validator=$.data(this[0],'validator');if(validator){return validator;}validator=new $.validator(options,this[0]);$.data(this[0],'validator',validator);if(validator.settings.onsubmit){this.find("input, button").filter(".cancel").click(function(){validator.cancelSubmit=true;});this.submit(function(event){if(validator.settings.debug)event.preventDefault();function handle(){if(validator.settings.submitHandler){validator.settings.submitHandler.call(validator,validator.currentForm);return false;}return true;}if(validator.cancelSubmit){validator.cancelSubmit=false;return handle();}if(validator.form()){if(validator.pendingRequest){validator.formSubmitted=true;return false;}return handle();}else{validator.focusInvalid();return false;}});}return validator;},valid:function(){if($(this[0]).is('form')){return this.validate().form();}else{var valid=false;var validator=$(this[0].form).validate();this.each(function(){valid|=validator.element(this);});return valid;}},removeAttrs:function(attributes){var result={},$element=this;$.each(attributes.split(/\s/),function(index,value){result[value]=$element.attr(value);$element.removeAttr(value);});return result;},rules:function(command,argument){var element=this[0];if(command){var settings=$.data(element.form,'validator').settings;var staticRules=settings.rules;var existingRules=$.validator.staticRules(element);switch(command){case"add":$.extend(existingRules,$.validator.normalizeRule(argument));staticRules[element.name]=existingRules;if(argument.messages)settings.messages[element.name]=$.extend(settings.messages[element.name],argument.messages);break;case"remove":if(!argument){delete staticRules[element.name];return existingRules;}var filtered={};$.each(argument.split(/\s/),function(index,method){filtered[method]=existingRules[method];delete existingRules[method];});return filtered;}}var data=$.validator.normalizeRules($.extend({},$.validator.metadataRules(element),$.validator.classRules(element),$.validator.attributeRules(element),$.validator.staticRules(element)),element);if(data.required){var param=data.required;delete data.required;data=$.extend({required:param},data);}return data;}});$.extend($.expr[":"],{blank:function(a){return!$.trim(a.value);},filled:function(a){return!!$.trim(a.value);},unchecked:function(a){return!a.checked;}});$.format=function(source,params){if(arguments.length==1)return function(){var args=$.makeArray(arguments);args.unshift(source);return $.format.apply(this,args);};if(arguments.length>2&&params.constructor!=Array){params=$.makeArray(arguments).slice(1);}if(params.constructor!=Array){params=[params];}$.each(params,function(i,n){source=source.replace(new RegExp("\\{"+i+"\\}","g"),n);});return source;};$.validator=function(options,form){this.settings=$.extend({},$.validator.defaults,options);this.currentForm=form;this.init();};$.extend($.validator,{defaults:{messages:{},groups:{},rules:{},errorClass:"error",errorElement:"label",focusInvalid:true,errorContainer:$([]),errorLabelContainer:$([]),onsubmit:true,ignore:[],ignoreTitle:false,onfocusin:function(element){this.lastActive=element;if(this.settings.focusCleanup&&!this.blockFocusCleanup){this.settings.unhighlight&&this.settings.unhighlight.call(this,element,this.settings.errorClass);this.errorsFor(element).hide();}},onfocusout:function(element){if(!this.checkable(element)&&(element.name in this.submitted||!this.optional(element))){this.element(element);}},onkeyup:function(element){if(element.name in this.submitted||element==this.lastElement){this.element(element);}},onclick:function(element){if(element.name in this.submitted)this.element(element);},highlight:function(element,errorClass){$(element).addClass(errorClass);},unhighlight:function(element,errorClass){$(element).removeClass(errorClass);}},setDefaults:function(settings){$.extend($.validator.defaults,settings);},messages:{required:"This field is required.",remote:"Please fix this field.",email:"Please enter a valid email address.",url:"Please enter a valid URL.",date:"Please enter a valid date.",dateISO:"Please enter a valid date (ISO).",dateDE:"Bitte geben Sie ein gltiges Datum ein.",number:"Please enter a valid number.",numberDE:"Bitte geben Sie eine Nummer ein.",digits:"Please enter only digits",creditcard:"Please enter a valid credit card number.",equalTo:"Please enter the same value again.",accept:"Please enter a value with a valid extension.",maxlength:$.format("Please enter no more than {0} characters."),minlength:$.format("Please enter at least {0} characters."),rangelength:$.format("Please enter a value between {0} and {1} characters long."),range:$.format("Please enter a value between {0} and {1}."),max:$.format("Please enter a value less than or equal to {0}."),min:$.format("Please enter a value greater than or equal to {0}.")},autoCreateRanges:false,prototype:{init:function(){this.labelContainer=$(this.settings.errorLabelContainer);this.errorContext=this.labelContainer.length&&this.labelContainer||$(this.currentForm);this.containers=$(this.settings.errorContainer).add(this.settings.errorLabelContainer);this.submitted={};this.valueCache={};this.pendingRequest=0;this.pending={};this.invalid={};this.reset();var groups=(this.groups={});$.each(this.settings.groups,function(key,value){$.each(value.split(/\s/),function(index,name){groups[name]=key;});});var rules=this.settings.rules;$.each(rules,function(key,value){rules[key]=$.validator.normalizeRule(value);});function delegate(event){var validator=$.data(this[0].form,"validator");validator.settings["on"+event.type]&&validator.settings["on"+event.type].call(validator,this[0]);}$(this.currentForm).delegate("focusin focusout keyup",":text, :password, :file, select, textarea",delegate).delegate("click",":radio, :checkbox",delegate);if(this.settings.invalidHandler)$(this.currentForm).bind("invalid-form.validate",this.settings.invalidHandler);},form:function(){this.checkForm();$.extend(this.submitted,this.errorMap);this.invalid=$.extend({},this.errorMap);if(!this.valid())$(this.currentForm).triggerHandler("invalid-form",[this]);this.showErrors();return this.valid();},checkForm:function(){this.prepareForm();for(var i=0,elements=(this.currentElements=this.elements());elements[i];i++){this.check(elements[i]);}return this.valid();},element:function(element){element=this.clean(element);this.lastElement=element;this.prepareElement(element);this.currentElements=$(element);var result=this.check(element);if(result){delete this.invalid[element.name];}else{this.invalid[element.name]=true;}if(!this.numberOfInvalids()){this.toHide=this.toHide.add(this.containers);}this.showErrors();return result;},showErrors:function(errors){if(errors){$.extend(this.errorMap,errors);this.errorList=[];for(var name in errors){this.errorList.push({message:errors[name],element:this.findByName(name)[0]});}this.successList=$.grep(this.successList,function(element){return!(element.name in errors);});}this.settings.showErrors?this.settings.showErrors.call(this,this.errorMap,this.errorList):this.defaultShowErrors();},resetForm:function(){if($.fn.resetForm)$(this.currentForm).resetForm();this.submitted={};this.prepareForm();this.hideErrors();this.elements().removeClass(this.settings.errorClass);},numberOfInvalids:function(){return this.objectLength(this.invalid);},objectLength:function(obj){var count=0;for(var i in obj)count++;return count;},hideErrors:function(){this.addWrapper(this.toHide).hide();},valid:function(){return this.size()==0;},size:function(){return this.errorList.length;},focusInvalid:function(){if(this.settings.focusInvalid){try{$(this.findLastActive()||this.errorList.length&&this.errorList[0].element||[]).filter(":visible").focus();}catch(e){}}},findLastActive:function(){var lastActive=this.lastActive;return lastActive&&$.grep(this.errorList,function(n){return n.element.name==lastActive.name;}).length==1&&lastActive;},elements:function(){var validator=this,rulesCache={};return $([]).add(this.currentForm.elements).filter(":input").not(":submit, :reset, :image, [disabled]").not(this.settings.ignore).filter(function(){!this.name&&validator.settings.debug&&window.console&&console.error("%o has no name assigned",this);if(this.name in rulesCache||!validator.objectLength($(this).rules()))return false;rulesCache[this.name]=true;return true;});},clean:function(selector){return $(selector)[0];},errors:function(){return $(this.settings.errorElement+"."+this.settings.errorClass,this.errorContext);},reset:function(){this.successList=[];this.errorList=[];this.errorMap={};this.toShow=$([]);this.toHide=$([]);this.formSubmitted=false;this.currentElements=$([]);},prepareForm:function(){this.reset();this.toHide=this.errors().add(this.containers);},prepareElement:function(element){this.reset();this.toHide=this.errorsFor(element);},check:function(element){element=this.clean(element);if(this.checkable(element)){element=this.findByName(element.name)[0];}var rules=$(element).rules();var dependencyMismatch=false;for(method in rules){var rule={method:method,parameters:rules[method]};try{var result=$.validator.methods[method].call(this,element.value.replace(/\r/g,""),element,rule.parameters);if(result=="dependency-mismatch"){dependencyMismatch=true;continue;}dependencyMismatch=false;if(result=="pending"){this.toHide=this.toHide.not(this.errorsFor(element));return;}if(!result){this.formatAndAdd(element,rule);return false;}}catch(e){this.settings.debug&&window.console&&console.log("exception occured when checking element "+element.id+", check the '"+rule.method+"' method");throw e;}}if(dependencyMismatch)return;if(this.objectLength(rules))this.successList.push(element);return true;},customMetaMessage:function(element,method){if(!$.metadata)return;var meta=this.settings.meta?$(element).metadata()[this.settings.meta]:$(element).metadata();return meta&&meta.messages&&meta.messages[method];},customMessage:function(name,method){var m=this.settings.messages[name];return m&&(m.constructor==String?m:m[method]);},findDefined:function(){for(var i=0;i<arguments.length;i++){if(arguments[i]!==undefined)return arguments[i];}return undefined;},defaultMessage:function(element,method){return this.findDefined(this.customMessage(element.name,method),this.customMetaMessage(element,method),!this.settings.ignoreTitle&&element.title||undefined,$.validator.messages[method],"<strong>Warning: No message defined for "+element.name+"</strong>");},formatAndAdd:function(element,rule){var message=this.defaultMessage(element,rule.method);if(typeof message=="function")message=message.call(this,rule.parameters,element);this.errorList.push({message:message,element:element});this.errorMap[element.name]=message;this.submitted[element.name]=message;},addWrapper:function(toToggle){if(this.settings.wrapper)toToggle=toToggle.add(toToggle.parents(this.settings.wrapper));return toToggle;},defaultShowErrors:function(){for(var i=0;this.errorList[i];i++){var error=this.errorList[i];this.settings.highlight&&this.settings.highlight.call(this,error.element,this.settings.errorClass);this.showLabel(error.element,error.message);}if(this.errorList.length){this.toShow=this.toShow.add(this.containers);}if(this.settings.success){for(var i=0;this.successList[i];i++){this.showLabel(this.successList[i]);}}if(this.settings.unhighlight){for(var i=0,elements=this.validElements();elements[i];i++){this.settings.unhighlight.call(this,elements[i],this.settings.errorClass);}}this.toHide=this.toHide.not(this.toShow);this.hideErrors();this.addWrapper(this.toShow).show();},validElements:function(){return this.currentElements.not(this.invalidElements());},invalidElements:function(){return $(this.errorList).map(function(){return this.element;});},showLabel:function(element,message){var label=this.errorsFor(element);if(label.length){label.removeClass().addClass(this.settings.errorClass);label.attr("generated")&&label.html(message);}else{label=$("<"+this.settings.errorElement+"/>").attr({"for":this.idOrName(element),generated:true}).addClass(this.settings.errorClass).html(message||"");if(this.settings.wrapper){label=label.hide().show().wrap("<"+this.settings.wrapper+"/>").parent();}if(!this.labelContainer.append(label).length)this.settings.errorPlacement?this.settings.errorPlacement(label,$(element)):label.insertAfter(element);}if(!message&&this.settings.success){label.text("");typeof this.settings.success=="string"?label.addClass(this.settings.success):this.settings.success(label);}this.toShow=this.toShow.add(label);},errorsFor:function(element){return this.errors().filter("[for='"+this.idOrName(element)+"']");},idOrName:function(element){return this.groups[element.name]||(this.checkable(element)?element.name:element.id||element.name);},checkable:function(element){return/radio|checkbox/i.test(element.type);},findByName:function(name){var form=this.currentForm;return $(document.getElementsByName(name)).map(function(index,element){return element.form==form&&element.name==name&&element||null;});},getLength:function(value,element){switch(element.nodeName.toLowerCase()){case'select':return $("option:selected",element).length;case'input':if(this.checkable(element))return this.findByName(element.name).filter(':checked').length;}return value.length;},depend:function(param,element){return this.dependTypes[typeof param]?this.dependTypes[typeof param](param,element):true;},dependTypes:{"boolean":function(param,element){return param;},"string":function(param,element){return!!$(param,element.form).length;},"function":function(param,element){return param(element);}},optional:function(element){return!$.validator.methods.required.call(this,$.trim(element.value),element)&&"dependency-mismatch";},startRequest:function(element){if(!this.pending[element.name]){this.pendingRequest++;this.pending[element.name]=true;}},stopRequest:function(element,valid){this.pendingRequest--;if(this.pendingRequest<0)this.pendingRequest=0;delete this.pending[element.name];if(valid&&this.pendingRequest==0&&this.formSubmitted&&this.form()){$(this.currentForm).submit();}else if(!valid&&this.pendingRequest==0&&this.formSubmitted){$(this.currentForm).triggerHandler("invalid-form",[this]);}},previousValue:function(element){return $.data(element,"previousValue")||$.data(element,"previousValue",previous={old:null,valid:true,message:this.defaultMessage(element,"remote")});}},classRuleSettings:{required:{required:true},email:{email:true},url:{url:true},date:{date:true},dateISO:{dateISO:true},dateDE:{dateDE:true},number:{number:true},numberDE:{numberDE:true},digits:{digits:true},creditcard:{creditcard:true}},addClassRules:function(className,rules){className.constructor==String?this.classRuleSettings[className]=rules:$.extend(this.classRuleSettings,className);},classRules:function(element){var rules={};var classes=$(element).attr('class');classes&&$.each(classes.split(' '),function(){if(this in $.validator.classRuleSettings){$.extend(rules,$.validator.classRuleSettings[this]);}});return rules;},attributeRules:function(element){var rules={};var $element=$(element);for(method in $.validator.methods){var value=$element.attr(method);if(value){rules[method]=value;}}if(rules.maxlength&&/-1|2147483647|524288/.test(rules.maxlength)){delete rules.maxlength;}return rules;},metadataRules:function(element){if(!$.metadata)return{};var meta=$.data(element.form,'validator').settings.meta;return meta?$(element).metadata()[meta]:$(element).metadata();},staticRules:function(element){var rules={};var validator=$.data(element.form,'validator');if(validator.settings.rules){rules=$.validator.normalizeRule(validator.settings.rules[element.name])||{};}return rules;},normalizeRules:function(rules,element){$.each(rules,function(prop,val){if(val===false){delete rules[prop];return;}if(val.param||val.depends){var keepRule=true;switch(typeof val.depends){case"string":keepRule=!!$(val.depends,element.form).length;break;case"function":keepRule=val.depends.call(element,element);break;}if(keepRule){rules[prop]=val.param!==undefined?val.param:true;}else{delete rules[prop];}}});$.each(rules,function(rule,parameter){rules[rule]=$.isFunction(parameter)?parameter(element):parameter;});$.each(['minlength','maxlength','min','max'],function(){if(rules[this]){rules[this]=Number(rules[this]);}});$.each(['rangelength','range'],function(){if(rules[this]){rules[this]=[Number(rules[this][0]),Number(rules[this][1])];}});if($.validator.autoCreateRanges){if(rules.min&&rules.max){rules.range=[rules.min,rules.max];delete rules.min;delete rules.max;}if(rules.minlength&&rules.maxlength){rules.rangelength=[rules.minlength,rules.maxlength];delete rules.minlength;delete rules.maxlength;}}if(rules.messages){delete rules.messages}return rules;},normalizeRule:function(data){if(typeof data=="string"){var transformed={};$.each(data.split(/\s/),function(){transformed[this]=true;});data=transformed;}return data;},addMethod:function(name,method,message){$.validator.methods[name]=method;$.validator.messages[name]=message;if(method.length<3){$.validator.addClassRules(name,$.validator.normalizeRule(name));}},methods:{required:function(value,element,param){if(!this.depend(param,element))return"dependency-mismatch";switch(element.nodeName.toLowerCase()){case'select':var options=$("option:selected",element);return options.length>0&&(element.type=="select-multiple"||($.browser.msie&&!(options[0].attributes['value'].specified)?options[0].text:options[0].value).length>0);case'input':if(this.checkable(element))return this.getLength(value,element)>0;default:return $.trim(value).length>0;}},remote:function(value,element,param){if(this.optional(element))return"dependency-mismatch";var previous=this.previousValue(element);if(!this.settings.messages[element.name])this.settings.messages[element.name]={};this.settings.messages[element.name].remote=typeof previous.message=="function"?previous.message(value):previous.message;param=typeof param=="string"&&{url:param}||param;if(previous.old!==value){previous.old=value;var validator=this;this.startRequest(element);var data={};data[element.name]=value;$.ajax($.extend(true,{url:param,mode:"abort",port:"validate"+element.name,dataType:"json",data:data,success:function(response){if(response){var submitted=validator.formSubmitted;validator.prepareElement(element);validator.formSubmitted=submitted;validator.successList.push(element);validator.showErrors();}else{var errors={};errors[element.name]=response||validator.defaultMessage(element,"remote");validator.showErrors(errors);}previous.valid=response;validator.stopRequest(element,response);}},param));return"pending";}else if(this.pending[element.name]){return"pending";}return previous.valid;},minlength:function(value,element,param){return this.optional(element)||this.getLength($.trim(value),element)>=param;},maxlength:function(value,element,param){return this.optional(element)||this.getLength($.trim(value),element)<=param;},rangelength:function(value,element,param){var length=this.getLength($.trim(value),element);return this.optional(element)||(length>=param[0]&&length<=param[1]);},min:function(value,element,param){return this.optional(element)||value>=param;},max:function(value,element,param){return this.optional(element)||value<=param;},range:function(value,element,param){return this.optional(element)||(value>=param[0]&&value<=param[1]);},email:function(value,element){return this.optional(element)||/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i.test(value);},url:function(value,element){return this.optional(element)||/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);},date:function(value,element){return this.optional(element)||!/Invalid|NaN/.test(new Date(value));},dateISO:function(value,element){return this.optional(element)||/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}$/.test(value);},dateDE:function(value,element){return this.optional(element)||/^\d\d?\.\d\d?\.\d\d\d?\d?$/.test(value);},number:function(value,element){return this.optional(element)||/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(value);},numberDE:function(value,element){return this.optional(element)||/^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d+)?$/.test(value);},digits:function(value,element){return this.optional(element)||/^\d+$/.test(value);},creditcard:function(value,element){if(this.optional(element))return"dependency-mismatch";if(/[^0-9-]+/.test(value))return false;var nCheck=0,nDigit=0,bEven=false;value=value.replace(/\D/g,"");for(n=value.length-1;n>=0;n--){var cDigit=value.charAt(n);var nDigit=parseInt(cDigit,10);if(bEven){if((nDigit*=2)>9)nDigit-=9;}nCheck+=nDigit;bEven=!bEven;}return(nCheck%10)==0;},accept:function(value,element,param){param=typeof param=="string"?param:"png|jpe?g|gif";return this.optional(element)||value.match(new RegExp(".("+param+")$","i"));},equalTo:function(value,element,param){return value==$(param).val();}}});})(jQuery);;(function($){var ajax=$.ajax;var pendingRequests={};$.ajax=function(settings){settings=$.extend(settings,$.extend({},$.ajaxSettings,settings));var port=settings.port;if(settings.mode=="abort"){if(pendingRequests[port]){pendingRequests[port].abort();}return(pendingRequests[port]=ajax.apply(this,arguments));}return ajax.apply(this,arguments);};})(jQuery);;(function($){$.each({focus:'focusin',blur:'focusout'},function(original,fix){$.event.special[fix]={setup:function(){if($.browser.msie)return false;this.addEventListener(original,$.event.special[fix].handler,true);},teardown:function(){if($.browser.msie)return false;this.removeEventListener(original,$.event.special[fix].handler,true);},handler:function(e){arguments[0]=$.event.fix(e);arguments[0].type=fix;return $.event.handle.apply(this,arguments);}};});$.extend($.fn,{delegate:function(type,delegate,handler){return this.bind(type,function(event){var target=$(event.target);if(target.is(delegate)){return handler.apply(target,arguments);}});},triggerEvent:function(type,target){return this.triggerHandler(type,[$.event.fix({type:type,target:target})]);}})})(jQuery);
(function(a){a.fn.warning=function(c,b){if(c==undefined||c==""){return this.html("").removeClass("warning").hide()}return this.each(function(){var d=a(this);if(d.attr("timeout")){clearTimeout(d.attr("timeout"));d.removeAttr("timeout")}d.html(c).addClass("warning").show();if(b){d.attr("timeout",setTimeout(function(){d.warning()},b))}})};a.fn.keyhint=function(d,c){if(!c){c="gray"}var b=a(this);b.focus(function(){b.filter("."+c).removeClass(c).val("")}).blur(function(){if(this.value==""){b.addClass(c).val(this.title)}}).each(function(){if(this.value==""||this.value==this.title){b.addClass(c).val(this.title)}});b.parents("form:eq(0)").submit(function(){if(d){b.filter("."+c).removeClass(c).val("")}else{if(b.hasClass(c)||b.val()==""){alert(b.attr("title"));b.focus();return false}}});return this};a.fn.rowover=function(b,c){if(!b){b="over"}if(!c){c="odd"}this.filter(":odd").addClass(c);return this.hover(function(){a(this).addClass(b)},function(){a(this).removeClass(b)})};a.fn.scrollToY=function(){window.scrollTo(0,this.offset().top);return this};a.fn.disabled=function(){return this.find(":input").attr("disabled",true).end()};a.fn.enabled=function(){return this.find(":input").not("[_disabled]").attr("disabled",false).end()};a.cookie=function(c,j,n){if(typeof j!="undefined"){n=n||{};if(j===null){j="";n.expires=-1}var e="";if(n.expires&&(typeof n.expires=="number"||n.expires.toUTCString)){var f;if(typeof n.expires=="number"){f=new Date();f.setTime(f.getTime()+(n.expires*24*60*60*1000))}else{f=n.expires}e="; expires="+f.toUTCString()}var m=n.path?"; path="+n.path:"";var g=n.domain?"; domain="+n.domain:"";var b=n.secure?"; secure":"";document.cookie=[c,"=",encodeURIComponent(j),e,m,g,b].join("")}else{var k=null;if(document.cookie&&document.cookie!=""){var l=document.cookie.split(";");for(var h=0;h<l.length;h++){var d=a.trim(l[h]);if(d.substring(0,c.length+1)==(c+"=")){k=decodeURIComponent(d.substring(c.length+1));break}}}return k}};a.func={ismobile:function(c){var b=/^1[358]{1}[0-9]{9}$|^852[69]{1}[0-9]{7}$|^88609[0-9]{8}$|^853[6]{1}[0-9]{7}$/;return b.test(c)},isemail:function(c){var b=/^[\w-+\.]+@([\w-]+\.)+[\w-]{2,}$/i;return b.test(c)},iscnchar:function(c){var b=/^[\u4E00-\u9FA5]+$/;return b.test(c)},isbyte:function(c){var b=/^[\x00-\xff]+$/;return b.test(c)},isdomainstr:function(d,c){var b=/^[0-9a-z\u4E00-\u9FA5]+(-[0-9a-z\u4E00-\u9FA5]+)*$/;if(!b.test(d)){return false}if(c=="en"&&!(/^[0-9a-z-]+$/i).test(d)){return false}if(c=="cn"&&!(/[\u4E00-\u9FA5]/).test(d)){return false}return true},isdomain:function(e,d){var c=/^([^-]+(-[^-]+)*)(\.([^-]+(-[^-]+)*))*(\.([a-z\u4E00-\u9FA5]+))$/i;var b=/^[-0-9a-z\u4E00-\u9FA5\.]+$/i;if(d=="en"){b=/^[-0-9a-z\.]+$/i}if(d=="cn"){b=/[\u4E00-\u9FA5]/}return(c.test(e)&&b.test(e))},isip:function(c){var b=/^(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\.(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])){3}$/;return b.test(c)},isInt:function(c){var b=/^\d+$/;return b.test(c)},checkInt:function(b){b=parseInt(b,10);return isNaN(b)?0:b},parseStr:function(d){var c=d.replace(/^#+|^\?/,"");var b=[];a(c.split("&")).each(function(){var e=this.split("=",2);b[e[0]]=e[1]?decodeURIComponent(e[1].replace(/\+/g," ")):null});return b},parseDateTime:function(b){var e=new Date();var c=0;var d;var f;b=b.split(" ");if(b.length==2){d=b[0];f=b[1]}else{if(b.length=1){d=b[0];f=" "}else{return null}}d=d.split("-");if(d.length!=3){return null}else{c=parseInt(d[0],10);if(c!=NaN){e.setFullYear(c)}c=parseInt(d[1],10);if(c!=NaN){e.setMonth(c-1)}c=parseInt(d[2],10);if(c!=NaN){e.setDate(c)}}f=f.split(":");if(f.length==3){c=parseInt(f[0],10);if(c!=NaN){e.setHours(c)}c=parseInt(f[1],10);if(c!=NaN){e.setMinutes(c)}c=parseInt(f[2],10);if(c!=NaN){e.setSeconds(c)}}return e}};a.validator.methods.account=function(e,c,f){var d=/^[0-9a-zA-Z]+(-[0-9a-zA-Z]+)*$/;var b=/^\d+$/;return this.optional(c)||(d.test(e)&&!b.test(e))};a.validator.methods.minLengthW=function(e,d,f){var b=0;for(var c=0;c<e.length;c++){charCode=e.charCodeAt(c);if(charCode<0||charCode>255){b+=2}else{b++}}return this.optional(d)||(b>=f)};a.validator.methods.password=function(d,b,e){var c=/^\d+$/;return this.optional(b)||(!c.test(d))};a.validator.methods.question=function(c,b,d){return this.optional(b)||false};a.validator.methods.mobile=function(c,b,d){return this.optional(b)||a.func.ismobile(c)};a.validator.methods.cnname=function(d,b,e){var c=/^[\u4E00-\u9FA5]+$/;return this.optional(b)||c.test(d)};a.validator.methods.enname=function(d,b,e){var c=/^[a-zA-Z]+[ ]{1}[A-Za-z]+$/;return this.optional(b)||c.test(d)};a.validator.methods.isbyte=function(c,b,d){return this.optional(b)||a.func.isbyte(c)};a.validator.methods.containCnChar=function(d,b,e){var c=/[\u4E00-\u9FA5]/;return this.optional(b)||c.test(d)};a.validator.methods.dateLT=function(c,b,d){if(Number(a.func.parseDateTime(a(d).val()))>Number(a.func.parseDateTime(c))){return this.optional(b)||false}return true}})(jQuery);

$.func = {
    /**
     * Is mobile number format
     *
     * @param {Object} str
     */
    isMobile: function(str){
        var reg = /^1[3458]{1}[0-9]{9}$|^852[69]{1}[0-9]{7}$|^88609[0-9]{8}$|^853[6]{1}[0-9]{7}$/;
        return reg.test(str);
    }
};

/**
 * 图度扩容 Javascript
 * 
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com
 * @version    $Id: dilation.js 2206 2012-10-11 07:06:15Z web_op $
 */
var Dilation = {
    /**
     * 成功窗口
     */
    successWin: null,

    /**
     * 窗口模板
     */
    tplWin: '<div class="dilation-win" style="fong-size:14px;color: #666666"><span class="icon-success"></span>恭喜您，获取了<span class="quote"></span>的空间容量！</div>',

    /**
     * 初始化页面
     */
    init: function() {
        $('input.text-big').bind('focus blur mouseover mouseout', function(e) {
            if (e.type == 'focus' || e.type == 'mouseover') {
                $(this).addClass('text-hover');
            } else {
                if (e.type == 'blur' || document.activeElement != this) {
                    $(this).removeClass('text-hover');
                }
            }
        });

        // 分享到新浪微博
        $('#weiboshare').bind('click', function(){
            var nickname = $('#nickname').val().replace(/\s+/, '');

            if (!nickname.length) {
                $('#nickname').focus();
                Message.show('请输入您的微博昵称');
                return false;
            }

            var param = {
                title:'图度云办公系统，颠覆传统OA。协助企业管理者，项目组建立清晰的工作流程，明确责任人与工作进度，是工作管理最佳拍档。目前已有太平洋电脑网、CNBETA等多家企业与项目团队使用。加入图度，乐享轻松工作，从这个点击开始（tudu.com） http://www.tudu.com', /**分享的文字内容(可选，默认为所在页面的title)*/
                pic:'http://www.tudu.com/img/index_box_2.jpg', /**分享图片的路径(可选)*/
                ralateUid:'2257289534', /**关联用户的UID，分享微博会@该用户(可选)*/
                language:'zh_cn', /**设置语言，zh_cn|zh_tw(可选)*/
                rnd:new Date().valueOf()
            }

            var temp = [];
            for( var p in param ){
                temp.push(p + '=' + encodeURIComponent( param[p] || '' ) )
            }

            window.open('http://service.weibo.com/share/share.php?' + temp.join('&'));

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: _TOP.SITES.tudu + BASE_PATH + '/settings/dilation/weibo-quota',
                data: {nickname: nickname},
                success: function(ret) {
                    var success = ret.success ? true : false;
                    Dilation.showSuccessWin(0, success, ret.message);
                },
                error: function(res) {
                    Message.show(Message.PROCESSING_ERROR);
                    return false;
                }
            });
        });

        // 提交微博分享验证
        $('#valid-weibo').bind('click', function(){
            Dilation.submitWeibo();
        });

        // 发送手机验证码
        $('#sendcode').bind('click', function(){
            if (this.timer) return;
            var mobile= $('#mobile').val();
            if (!$.func.isMobile(mobile)) {
                $('#mobile').focus();
                Message.show('请输入正确的手机号码');
                return false;
            }
            $(this).attr('disabled', true).attr('_disabled', true);
            sendCode(mobile);
            timer(this, this.value, 60);
        });

        // 提交绑定手机
        $('#valid-phone').bind('click', function(){
            Dilation.submitMobile();
        });

        // 图度数增长量
        $('#valid-tudu').bind('click', function(){
            Dilation.submitTuduCount();
        });

        /**
         *
         * @param {Object} btn
         * @param {Object} txt
         * @param {Object} time
         */
        function timer(btn, txt, time) {
            var t = txt;
            if (time > 0) {
                t += '(' + time + ')';
            }
            $(btn).val(t);
            if (time <= 0) {
                $(btn).removeAttr('disabled').removeAttr('_disabled');
                btn.timer = null;
                return;
            }
            time--;
            btn.timer = setTimeout(function(){timer(btn, txt, time)}, 1000);
        }

        /**
         * 发送短信验证码
         */
        function sendCode(mobile) {
            Message.show('正在发送验证码，稍后请留意短信', true, 10);

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: _TOP.SITES.tudu + BASE_PATH + '/settings/dilation/send-code',
                data: {mobile: mobile},
                success: function(ret) {
                    Message.show(ret.message, 5000, ret.success);
                },
                error: function(res) {
                    Message.show(Message.PROCESSING_ERROR);
                    return false;
                }
            });
        }
    },

    /**
     * 提交绑定手机
     */
    submitMobile: function() {
        var mobile= $('#mobile').val().replace(/\s+/, ''),
            code  = $('#seccode').val().replace(/\s+/, '');
        if (!$.func.isMobile(mobile)) {
            $('#mobile').focus();
            Message.show('请输入正确的手机号码');
            return false;
        }

        if (!code.length) {
            $('#seccode').focus();
            Message.show('请输入验证码');
            return false;
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: _TOP.SITES.tudu + BASE_PATH + '/settings/dilation/bind-mobile',
            data: {mobile: mobile, seccode: code},
            success: function(ret) {
                if (ret.success && ret.data) {
                    if (ret.data.quota) {
                        Dilation.showSuccessWin(1);
                    } else {
                        Message.show(ret.message, 5000, true);
                    }
                } else {
                    Message.show(ret.message);
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    },

    /**
     * 方法三：图度数增长量
     */
    submitTuduCount: function() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: _TOP.SITES.tudu + BASE_PATH + '/settings/dilation/valid-tudu',
            data: {},
            success: function(ret) {
                if (ret.success) {
                    Dilation.showSuccessWin(2);
                } else {
                    Message.show(ret.message);
                }
            },
            error: function(res) {
                Message.show(Message.PROCESSING_ERROR);
                return false;
            }
        });
    },

    /**
     * 弹窗成功窗口
     */
    showSuccessWin: function(method, success, message) {
        var me = this;

        if (null === me.successWin) {
            me.successWin = Admin.window({
                width: 400,
                id: 'dilation-win',
                title: '提示框',
                body: me.tplWin,
                footer: '<input name="close" type="button" class="btn close" value="确定" />',
                draggable: true,
                onShow: function() {},
                onClose: function() {
                    me.successWin.destroy();
                    me.successWin = null;
                },
                init: function() {
                    if (method == 0) {
                        if (success) {
                            $('#weiboshare').attr('disabled', true);
                            this.find('.dilation-win').empty().html('<span class="icon-success"></span>' + message);
                        } else {
                            this.find('.dilation-win').empty().html(message);
                        }
                    } else if (method == 1) {
                        this.find('.quote').text('2G');
                    } else if (method == 2) {
                        this.find('.quote').text('4G');
                    }
                }
            });
        }

        me.successWin.show();
    }
};
