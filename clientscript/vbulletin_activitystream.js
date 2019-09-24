/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/
if(AJAX_Compatible&&(typeof vb_disable_ajax=="undefined"||vb_disable_ajax<2)){vBulletin.events.systemInit.subscribe(function(){var A=new vB_ActivityStream()})}function vB_ActivityStream(){this.activetab=null;this.ajaxreq=null;this.init_tabs();this.options=activity_stream_options;this.hidemore={};this.updatetimer=null;this.idletimer=null;this.idle=false;this.loadnew=true;if(this.options.refresh*60000>300000){this.idlerefresh=this.options.refresh*60000}else{this.idlerefresh=300000}if(this.options.sortby!="popular"){YAHOO.util.Event.addListener(document,"mousemove",this.reset_idle_timer,this);this.start_time()}this.newitemlist=[];this.prevnewmark=null;this.itemlimit=1000;var B=YAHOO.util.Dom.get("activitylist");var A=YAHOO.util.Dom.getElementsByClassName("activitybit","li",B);this.itemsloaded=A.length;this.loadnomore=false}vB_ActivityStream.prototype.reset_idle_timer=function(A,B){if(B.idle==true){B.start_time();B.idle=false;B.new_activity(true);console.log("Activity Stream has Gone Active")}else{B.idle=false}clearTimeout(B.idletimer);B.idletimer=setTimeout(function(){B.go_idle()},B.idlerefresh)};vB_ActivityStream.prototype.go_idle=function(){console.log("Activity Stream has Gone Idle");this.idle=true};vB_ActivityStream.prototype.show_new_activity=function(D,F){F.start_time();YAHOO.util.Dom.addClass("newactivity_container","hidden");if(F.newitemlist.length==0){return }var A=YAHOO.util.Dom.get("olderactivity");var C=YAHOO.util.Dom.get("activitylist");F.newitemlist.reverse();var E=true;YAHOO.util.Dom.removeClass(A,"hidden");for(x in F.newitemlist){if(E){if(!C.hasChildNodes()){C.appendChild(A)}else{var B=C.insertBefore(A,C.firstChild)}E=false}if(!C.hasChildNodes()){F.add_node_to_list(F.newitemlist[x],true)}else{F.add_node_to_list(F.newitemlist[x],true,C.firstChild)}}F.newitemlist=[];if(!YAHOO.util.Dom.hasClass("moreactivitylink","hidden")){YAHOO.util.Dom.addClass("moreactivitylink","hidden");YAHOO.util.Dom.removeClass("noresults","hidden")}};vB_ActivityStream.prototype.start_time=function(A){if(this.options.sortby=="popular"||!this.loadnew){return }clearTimeout(this.updatetimer);thisC=this;this.updatetimer=setTimeout(function(){thisC.new_activity()},this.options.refresh*60000);console.log("Activity Stream Update Timer Started")};vB_ActivityStream.prototype.init_tabs=function(){var A=YAHOO.util.Dom.get("activity_tab_container");if(A){var C=A.getElementsByTagName("dd");for(var B=0;B<C.length;B++){if(!this.activetab&&YAHOO.util.Dom.hasClass(C[B],"selected")){this.activetab=C[B]}YAHOO.util.Event.addListener(C[B],"click",this.tab_click,this)}}YAHOO.util.Event.addListener("moreactivitylink","click",this.more_activity,this);YAHOO.util.Event.addListener("newactivitylink","click",this.show_new_activity,this)};vB_ActivityStream.prototype.more_activity=function(B,C){YAHOO.util.Event.stopPropagation(B);YAHOO.util.Event.stopEvent(B);if(YAHOO.util.Connect.isCallInProgress(C.ajaxreq)){return }YAHOO.util.Dom.addClass("moreactivitylink","hidden");YAHOO.util.Dom.removeClass("moreactivityprogress","hidden");var D={failure:vBulletin_AJAX_Error_Handler,timeout:vB_Default_Timeout,success:C.update_tab,scope:C,argument:{updatetype:"fetchpast"}};var A=SESSIONURL+"securitytoken="+SECURITYTOKEN+"&pp="+C.options.perpage+"&mindateline="+C.options.mindateline+"&minscore="+C.options.minscore+"&minid="+C.options.minid;if(C.options.type=="member"){A+="&do=loadactivitytab&u="+THISUSERID+"&tab="+C.activetab.id}else{A+="&sortby="+C.options.sortby+"&time="+C.options.time+"&show="+C.options.show}C.ajaxreq=YAHOO.util.Connect.asyncRequest("POST",fetch_ajax_url("activity.php"),D,A)};vB_ActivityStream.prototype.new_activity=function(B){if(!this.loadnew){return }if(this.idle||YAHOO.util.Connect.isCallInProgress(this.ajaxreq)){this.start_time();return }var C={failure:vBulletin_AJAX_Error_Handler,timeout:vB_Default_Timeout,success:this.update_tab,scope:this,argument:{updatetype:"fetchfuture",shownew:B}};var A=SESSIONURL+"securitytoken="+SECURITYTOKEN+"&pp="+this.options.perpage+"&maxdateline="+this.options.maxdateline+"&maxid="+this.options.maxid;if(this.options.type=="member"){A+="&do=loadactivitytab&u="+THISUSERID+"&tab="+this.activetab.id}else{A+="&sortby="+this.options.sortby+"&time="+this.options.time+"&show="+this.options.show}this.ajaxreq=YAHOO.util.Connect.asyncRequest("POST",fetch_ajax_url("activity.php"),C,A)};vB_ActivityStream.prototype.tab_click=function(C,D){YAHOO.util.Event.stopPropagation(C);YAHOO.util.Event.stopEvent(C);var A=D.activetab;D.activetab=this;if(this==A||YAHOO.util.Connect.isCallInProgress(D.ajaxreq)){D.activetab=A;return }var E={failure:vBulletin_AJAX_Error_Handler,timeout:vB_Default_Timeout,success:D.update_tab,scope:D,argument:{updatetype:"replace",newtab:this,oldtab:A}};var B=SESSIONURL+"do=loadactivitytab&securitytoken="+SECURITYTOKEN+"&u="+THISUSERID+"&pp="+D.options.perpage+"&tab="+this.id;D.ajaxreq=YAHOO.util.Connect.asyncRequest("POST",fetch_ajax_url("activity.php?do=loadactivitytab&u="+THISUSERID+"&pp="+D.options.perpage+"&tab="+this.id),E,B)};vB_ActivityStream.prototype.add_node_to_list=function(E,A,D){var F=YAHOO.util.Dom.get("activitylist");this.itemsloaded++;if(D){F.insertBefore(E,D)}else{F.appendChild(E)}if(this.itemsloaded>=this.itemlimit){this.loadnomore=true;if(A){var C=YAHOO.util.Dom.getElementsByClassName("activitybit","li",F);var B=C[C.length-1];F.removeChild(B)}}};vB_ActivityStream.prototype.update_tab=function(H){if(H.responseXML){if(fetch_tag_count(H.responseXML,"error")){alert(H.responseXML.getElementsByTagName("error")[0].firstChild.nodeValue);return }YAHOO.util.Dom.addClass("moreactivityprogress","hidden");if(H.argument.updatetype=="replace"){YAHOO.util.Dom.addClass("olderactivity","hidden");YAHOO.util.Dom.addClass("newactivity_container","hidden");YAHOO.util.Dom.addClass("newactivity_nomore","hidden");this.newitemlist=[];this.loadnew=true}var F=YAHOO.util.Dom.get("activitylist");if(H.argument.updatetype=="replace"){var G=YAHOO.util.Dom.getElementsByClassName("activitybit","li",F);if(G.length>0){for(var D=0;D<G.length;D++){F.removeChild(G[D])}}YAHOO.util.Dom.removeClass(H.argument.oldtab,"selected");YAHOO.util.Dom.addClass(H.argument.newtab,"selected");this.itemsloaded=0}if(fetch_tag_count(H.responseXML,"nada")){if(H.argument.shownew){this.show_new_activity(null,this)}else{this.start_time()}YAHOO.util.Dom.addClass("moreactivitylink","hidden");YAHOO.util.Dom.addClass("noresults","hidden");YAHOO.util.Dom.removeClass("noactivity","hidden");return }var E=0;if(H.argument.updatetype=="replace"||H.argument.updatetype=="fetchpast"){var I=H.responseXML.getElementsByTagName("bit");if(I.length){for(var D=0;D<I.length;D++){if(I[D].firstChild){var C=string_to_node(I[D].firstChild.nodeValue);this.add_node_to_list(C,false);E++}}}}else{if(H.argument.updatetype=="fetchfuture"){var B=[];var I=H.responseXML.getElementsByTagName("bit");if(I.length){for(var D=0;D<I.length;D++){if(I[D].firstChild){var C=string_to_node(I[D].firstChild.nodeValue);B.push(C);E++}}}if(B.length>0){this.newitemlist=B.concat(this.newitemlist)}}}var A=H.responseXML.getElementsByTagName("totalcount")[0].firstChild.nodeValue;if(A>0){if(H.argument.updatetype=="replace"||H.argument.updatetype=="fetchpast"){this.options.minid=H.responseXML.getElementsByTagName("minid")[0].firstChild.nodeValue;this.options.mindateline=H.responseXML.getElementsByTagName("mindateline")[0].firstChild.nodeValue;this.options.minscore=H.responseXML.getElementsByTagName("minscore")[0].firstChild.nodeValue}if(H.argument.updatetype=="replace"||H.argument.updatetype=="fetchfuture"){this.options.maxid=H.responseXML.getElementsByTagName("maxid")[0].firstChild.nodeValue;this.options.maxdateline=H.responseXML.getElementsByTagName("maxdateline")[0].firstChild.nodeValue}if(H.argument.updatetype=="fetchfuture"&&E>0){if(this.newitemlist.length>200){this.loadnew=false;YAHOO.util.Dom.removeClass("newactivity_nomore","hidden");YAHOO.util.Dom.addClass("newactivity_container","hidden")}else{YAHOO.util.Dom.get("newactivitycount").innerHTML=this.newitemlist.length;YAHOO.util.Dom.removeClass("newactivity_container","hidden")}}}else{if(H.argument.updatetype=="replace"||H.argument.updatetype=="fetchpast"){this.options.mindateline=0;this.options.minscore=0;this.options.minid=""}if(H.argument.updatetype=="replace"){this.options.maxdateline=0;this.options.maxid=""}}if(H.argument.updatetype=="replace"||H.argument.updatetype=="fetchpast"){if(A==0||H.responseXML.getElementsByTagName("moreresults")[0].firstChild.nodeValue==0){YAHOO.util.Dom.addClass("moreactivitylink","hidden");if(H.argument.updatetype=="fetchpast"){YAHOO.util.Dom.addClass("noactivity","hidden");YAHOO.util.Dom.removeClass("noresults","hidden")}else{if(A>0&&H.responseXML.getElementsByTagName("moreresults")[0].firstChild.nodeValue==0){YAHOO.util.Dom.addClass("noactivity","hidden");YAHOO.util.Dom.removeClass("noresults","hidden")}else{YAHOO.util.Dom.removeClass("noactivity","hidden");YAHOO.util.Dom.addClass("noresults","hidden")}}}else{YAHOO.util.Dom.removeClass("moreactivitylink","hidden");YAHOO.util.Dom.addClass("noresults","hidden");YAHOO.util.Dom.addClass("noactivity","hidden")}}if(H.argument.updatetype=="fetchpast"&&this.loadnomore){YAHOO.util.Dom.addClass("moreactivitylink","hidden");YAHOO.util.Dom.removeClass("noresults","hidden")}if(H.argument.shownew){this.show_new_activity(null,this)}else{this.start_time()}}};