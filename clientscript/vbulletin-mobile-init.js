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
$(document).bind("mobileinit",function(){$.extend($.mobile,{ajaxEnabled:false,hashListeningEnabled:false});$("div.ui-page").live("pagecreate",function(B,D){var A=0;var C=$("#dummylist li span",this);for(i=0;i<C.length;i++){A+=parseInt($(C[i]).text())}$(".notifications_total_count",this).html(A);if(A>0){$(".notifications_total",this).removeClass("hidden")}})});