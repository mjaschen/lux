(function($){$.ui=$.ui||{};$.ui.accordion={};$.extend($.ui.accordion,{defaults:{selectedClass:"selected",alwaysOpen:true,animated:'slide',event:"click",header:"a"},animations:{slide:function(settings,additions){settings=$.extend({easing:"swing",duration:300},settings,additions);if(!settings.toHide.size()){settings.toShow.animate({height:"show"},{duration:settings.duration,easing:settings.easing,complete:settings.finished});return}var hideHeight=settings.toHide.height(),showHeight=settings.toShow.height(),difference=showHeight/hideHeight;settings.toShow.css({height:0,overflow:'hidden'}).show();settings.toHide.filter(":hidden").each(settings.finished).end().filter(":visible").animate({height:"hide"},{step:function(n){settings.toShow.height(Math.ceil((hideHeight-(n))*difference))},duration:settings.duration,easing:settings.easing,complete:settings.finished})},bounceslide:function(settings){this.slide(settings,{easing:settings.down?"bounceout":"swing",duration:settings.down?1000:200})},easeslide:function(settings){this.slide(settings,{easing:"easeinout",duration:700})}}});$.fn.extend({nextUntil:function(expr){var match=[];this.each(function(){for(var i=this.nextSibling;i;i=i.nextSibling){if(i.nodeType!=1)continue;if($.filter(expr,[i]).r.length)break;match.push(i)}});return this.pushStack(match)},accordion:function(settings){if(!this.length)return this;settings=$.extend({},$.ui.accordion.defaults,settings);if(settings.navigation){var current=this.find("a").filter(function(){return this.href==location.href});if(current.length){if(current.filter(settings.header).length){settings.active=current}else{settings.active=current.parent().parent().prev();current.addClass("current")}}}var container=this,headers=container.find(settings.header),active=findActive(settings.active),running=0;if(settings.fillSpace){var maxHeight=this.parent().height();headers.each(function(){maxHeight-=$(this).outerHeight()});var maxPadding=0;headers.nextUntil(settings.header).each(function(){maxPadding=Math.max(maxPadding,$(this).innerHeight()-$(this).height())}).height(maxHeight-maxPadding)}else if(settings.autoheight){var maxHeight=0;headers.nextUntil(settings.header).each(function(){maxHeight=Math.max(maxHeight,$(this).height())}).height(maxHeight)}headers.not(active||"").nextUntil(settings.header).hide();active.parent().andSelf().addClass(settings.selectedClass);function findActive(selector){return selector!=undefined?typeof selector=="number"?headers.filter(":eq("+selector+")"):headers.not(headers.not(selector)):selector===false?$("<div>"):headers.filter(":eq(0)")};function toggle(toShow,toHide,data,clickedActive,down){var finished=function(cancel){running=cancel?0:--running;if(running)return;container.trigger("change",data)};running=toHide.size()==0?toShow.size():toHide.size();if(settings.animated){if(!settings.alwaysOpen&&clickedActive){toShow.slideToggle(settings.animated);finished(true)}else{$.ui.accordion.animations[settings.animated]({toShow:toShow,toHide:toHide,finished:finished,down:down})}}else{if(!settings.alwaysOpen&&clickedActive){toShow.toggle()}else{toHide.hide();toShow.show()}finished(true)}};function clickHandler(event){if(!event.target&&!settings.alwaysOpen){active.toggleClass(settings.selectedClass);var toHide=active.nextUntil(settings.header);var toShow=active=$([]);toggle(toShow,toHide);return}var clicked=$(event.target);if(clicked.parents(settings.header).length)while(!clicked.is(settings.header))clicked=clicked.parent();var clickedActive=clicked[0]==active[0];if(running||(settings.alwaysOpen&&clickedActive)||!clicked.is(settings.header))return;active.parent().andSelf().toggleClass(settings.selectedClass);if(!clickedActive){clicked.parent().andSelf().addClass(settings.selectedClass)}var toShow=clicked.nextUntil(settings.header),toHide=active.nextUntil(settings.header),data=[clicked,active,toShow,toHide],down=headers.index(active[0])>headers.index(clicked[0]);active=clickedActive?$([]):clicked;toggle(toShow,toHide,data,clickedActive,down);return!toShow.length};function activateHandler(event,index){if(arguments.length==1)return;clickHandler({target:findActive(index)[0]})};return container.bind(settings.event,clickHandler).bind("activate",activateHandler)},activate:function(index){return this.trigger('activate',[index])}})})(jQuery);