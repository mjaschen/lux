(function($){$.fn.ajaxSubmit=function(o){if(typeof o=='function')o={success:o};o=$.extend({url:this.attr('action')||window.location,type:this.attr('method')||'GET'},o||{});var p={};$.event.trigger('form.pre.serialize',[this,o,p]);if(p.veto)return this;var a=this.formToArray(o.semantic);if(o.data){for(var n in o.data)a.push({name:n,value:o.data[n]})}if(o.beforeSubmit&&o.beforeSubmit(a,this,o)===false)return this;$.event.trigger('form.submit.validate',[a,this,o,p]);if(p.veto)return this;var q=$.param(a);if(o.type.toUpperCase()=='GET'){o.url+=(o.url.indexOf('?')>=0?'&':'?')+q;o.data=null}else o.data=q;var r=this,callbacks=[];if(o.resetForm)callbacks.push(function(){r.resetForm()});if(o.clearForm)callbacks.push(function(){r.clearForm()});if(!o.dataType&&o.target){var u=o.success||function(){};callbacks.push(function(a){if(this.evalScripts)$(o.target).attr("innerHTML",a).evalScripts().each(u,arguments);else $(o.target).html(a).each(u,arguments)})}else if(o.success)callbacks.push(o.success);o.success=function(a,b){for(var i=0,max=callbacks.length;i<max;i++)callbacks[i](a,b,r)};var v=$('input:file',this).fieldValue();var w=false;for(var j=0;j<v.length;j++)if(v[j])w=true;if(o.iframe||w)fileUpload();else $.ajax(o);$.event.trigger('form.submit.notify',[this,o]);return this;function fileUpload(){var d=r[0];var f=$.extend({},$.ajaxSettings,o);var h='jqFormIO'+$.fn.ajaxSubmit.counter++;var i=$('<iframe id="'+h+'" name="'+h+'" />');var j=i[0];var k=$.browser.opera&&window.opera.version()<9;if($.browser.msie||k)j.src='javascript:false;document.write("");';i.css({position:'absolute',top:'-1000px',left:'-1000px'});var l={responseText:null,responseXML:null,status:0,statusText:'n/a',getAllResponseHeaders:function(){},getResponseHeader:function(){},setRequestHeader:function(){}};var g=f.global;if(g&&!$.active++)$.event.trigger("ajaxStart");if(g)$.event.trigger("ajaxSend",[l,f]);var m=0;var n=0;setTimeout(function(){i.appendTo('body');j.attachEvent?j.attachEvent('onload',cb):j.addEventListener('load',cb,false);var a=d.encoding?'encoding':'enctype';var t=r.attr('target');r.attr({target:h,method:'POST',action:f.url});d[a]='multipart/form-data';if(f.timeout)setTimeout(function(){n=true;cb()},f.timeout);d.submit();r.attr('target',t)},10);function cb(){if(m++)return;j.detachEvent?j.detachEvent('onload',cb):j.removeEventListener('load',cb,false);var a=true;try{if(n)throw'timeout';var b,doc;doc=j.contentWindow?j.contentWindow.document:j.contentDocument?j.contentDocument:j.document;l.responseText=doc.body?doc.body.innerHTML:null;l.responseXML=doc.XMLDocument?doc.XMLDocument:doc;if(f.dataType=='json'||f.dataType=='script'){var c=doc.getElementsByTagName('textarea')[0];b=c?c.value:l.responseText;if(f.dataType=='json')eval("data = "+b);else $.globalEval(b)}else if(f.dataType=='xml'){b=l.responseXML;if(!b&&l.responseText!=null)b=toXml(l.responseText)}else{b=l.responseText}}catch(e){a=false;$.handleError(f,l,'error',e)}if(a){f.success(b,'success');if(g)$.event.trigger("ajaxSuccess",[l,f])}if(g)$.event.trigger("ajaxComplete",[l,f]);if(g&&!--$.active)$.event.trigger("ajaxStop");if(f.complete)f.complete(l,a?'success':'error');setTimeout(function(){i.remove();l.responseXML=null},100)};function toXml(s,a){if(window.ActiveXObject){a=new ActiveXObject('Microsoft.XMLDOM');a.async='false';a.loadXML(s)}else a=(new DOMParser()).parseFromString(s,'text/xml');return(a&&a.documentElement&&a.documentElement.tagName!='parsererror')?a:null}}};$.fn.ajaxSubmit.counter=0;$.fn.ajaxForm=function(a){return this.ajaxFormUnbind().submit(submitHandler).each(function(){this.formPluginId=$.fn.ajaxForm.counter++;$.fn.ajaxForm.optionHash[this.formPluginId]=a;$(":submit,input:image",this).click(clickHandler)})};$.fn.ajaxForm.counter=1;$.fn.ajaxForm.optionHash={};function clickHandler(e){var a=this.form;a.clk=this;if(this.type=='image'){if(e.offsetX!=undefined){a.clk_x=e.offsetX;a.clk_y=e.offsetY}else if(typeof $.fn.offset=='function'){var b=$(this).offset();a.clk_x=e.pageX-b.left;a.clk_y=e.pageY-b.top}else{a.clk_x=e.pageX-this.offsetLeft;a.clk_y=e.pageY-this.offsetTop}}setTimeout(function(){a.clk=a.clk_x=a.clk_y=null},10)};function submitHandler(){var a=this.formPluginId;var b=$.fn.ajaxForm.optionHash[a];$(this).ajaxSubmit(b);return false};$.fn.ajaxFormUnbind=function(){this.unbind('submit',submitHandler);return this.each(function(){$(":submit,input:image",this).unbind('click',clickHandler)})};$.fn.formToArray=function(b){var a=[];if(this.length==0)return a;var c=this[0];var d=b?c.getElementsByTagName('*'):c.elements;if(!d)return a;for(var i=0,max=d.length;i<max;i++){var e=d[i];var n=e.name;if(!n)continue;if(b&&c.clk&&e.type=="image"){if(!e.disabled&&c.clk==e)a.push({name:n+'.x',value:c.clk_x},{name:n+'.y',value:c.clk_y});continue}var v=$.fieldValue(e,true);if(v&&v.constructor==Array){for(var j=0,jmax=v.length;j<jmax;j++)a.push({name:n,value:v[j]})}else if(v!==null&&typeof v!='undefined')a.push({name:n,value:v})}if(!b&&c.clk){var f=c.getElementsByTagName("input");for(var i=0,max=f.length;i<max;i++){var g=f[i];var n=g.name;if(n&&!g.disabled&&g.type=="image"&&c.clk==g)a.push({name:n+'.x',value:c.clk_x},{name:n+'.y',value:c.clk_y})}}return a};$.fn.formSerialize=function(a){return $.param(this.formToArray(a))};$.fn.fieldSerialize=function(b){var a=[];this.each(function(){var n=this.name;if(!n)return;var v=$.fieldValue(this,b);if(v&&v.constructor==Array){for(var i=0,max=v.length;i<max;i++)a.push({name:n,value:v[i]})}else if(v!==null&&typeof v!='undefined')a.push({name:this.name,value:v})});return $.param(a)};$.fn.fieldValue=function(a){for(var b=[],i=0,max=this.length;i<max;i++){var c=this[i];var v=$.fieldValue(c,a);if(v===null||typeof v=='undefined'||(v.constructor==Array&&!v.length))continue;v.constructor==Array?$.merge(b,v):b.push(v)}return b};$.fieldValue=function(b,c){var n=b.name,t=b.type,tag=b.tagName.toLowerCase();if(typeof c=='undefined')c=true;if(c&&(!n||b.disabled||t=='reset'||t=='button'||(t=='checkbox'||t=='radio')&&!b.checked||(t=='submit'||t=='image')&&b.form&&b.form.clk!=b||tag=='select'&&b.selectedIndex==-1))return null;if(tag=='select'){var d=b.selectedIndex;if(d<0)return null;var a=[],ops=b.options;var e=(t=='select-one');var f=(e?d+1:ops.length);for(var i=(e?d:0);i<f;i++){var g=ops[i];if(g.selected){var v=$.browser.msie&&!(g.attributes['value'].specified)?g.text:g.value;if(e)return v;a.push(v)}}return a}return b.value};$.fn.clearForm=function(){return this.each(function(){$('input,select,textarea',this).clearFields()})};$.fn.clearFields=$.fn.clearInputs=function(){return this.each(function(){var t=this.type,tag=this.tagName.toLowerCase();if(t=='text'||t=='password'||tag=='textarea')this.value='';else if(t=='checkbox'||t=='radio')this.checked=false;else if(tag=='select')this.selectedIndex=-1})};$.fn.resetForm=function(){return this.each(function(){if(typeof this.reset=='function'||(typeof this.reset=='object'&&!this.reset.nodeType))this.reset()})}})(jQuery);