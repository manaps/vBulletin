vBOptimise=new function()
{this.tests=new Array();this.test=-1;this.items=0;this.run_test=function()
{this.test++;if(typeof this.tests[this.test]=='undefined')
{this.completed();return false;}
YAHOO.util.Connect.asyncRequest('POST','vboptimise.php',{success:this.ran_test,failure:this.test_error,timeout:vB_Default_Timeout,scope:this},'adminhash='+ADMINHASH+'&securitytoken='+SECURITYTOKEN+'&do=test&act='+this.tests[this.test]);}
this.ran_test=function(ajax)
{var failed=true;var xml_result=ajax.responseXML.getElementsByTagName('result');var xml_message=ajax.responseXML.getElementsByTagName('message');if(ajax.responseXML&&xml_result.length>0)
{var result=xml_result[0].firstChild.nodeValue;var message='Test completed successfully';if(xml_message.length>0)
{message=xml_message[0].firstChild.nodeValue;}
if(result=='OK')
{failed=false;}}
fetch_object('vbo_'+this.tests[this.test]).getElementsByTagName('img')[0].src='../dbtech/vboptimise/images/vboptimise_'+(failed?'bad':'good')+'.png';if(message!='')
{var insert_message=fetch_object('vbo_'+this.tests[this.test]).appendChild(document.createElement('span'));insert_message.className='smallfont';insert_message.innerHTML='   '+message;}
this.run_test();}
this.test_error=function(ajax)
{if(this.retry)
{this.cdn(this.retry);return false;};alert('AJAX Failure\n\n'+ajax.statusText);}
this.completed=function()
{}
this.cdn=function(sync)
{this.retry=sync;YAHOO.util.Connect.asyncRequest('POST','vboptimise.php',{success:this.cdn_response,failure:this.test_error,timeout:vB_Default_Timeout,scope:this},'adminhash='+ADMINHASH+'&securitytoken='+SECURITYTOKEN+'&do=cdn&operation=sync&sync='+sync);}
this.cdn_progress=function(foraction,progress)
{currentpercent=parseInt(YAHOO.util.Dom.get('cdn_progress_'+foraction).style.width);currentpercent+=3;if(currentpercent>progress)
{currentpercent=progress;}
YAHOO.util.Dom.get('cdn_progress_'+foraction).style.width=parseInt(currentpercent)+'%';if(currentpercent<progress)
{setTimeout(function()
{vBOptimise.cdn_progress(foraction,progress);},5);}}
this.cdn_response=function(ajax)
{var result=ajax.responseXML.getElementsByTagName('result');var action=ajax.responseXML.getElementsByTagName('action');var next=false;var finish=false;if(ajax.responseXML&&result.length>0)
{result=result[0].firstChild.nodeValue;action=action[0].firstChild.nodeValue;switch(action)
{case'items':{this.cdn_progress('item',100);this.items=parseInt(result);next='sync&at=0';fetch_object('cdn_progress_details').style.display='';fetch_object('cdn_progress_details').innerHTML='(0 / '+this.items+')';}
break;case'sync':{percent=(parseInt(result)/(this.items / 100));if(parseInt(result)==parseInt(this.items))
{percent=100;next='styles';}
else
{next='sync&at='+parseInt(result);}
fetch_object('cdn_progress_details').innerHTML='('+result+' / '+this.items+')';this.cdn_progress('sync',percent);}
break;case'styles':{this.cdn_progress('style',100);finish=true;}
break;}}
else
{alert(ajax.responseText);}
if(next)
{setTimeout(function()
{vBOptimise.cdn(next);},50);}
if(finish)
{setTimeout(function()
{window.location.href='vboptimise.php?do=cdn';},500);}}}();