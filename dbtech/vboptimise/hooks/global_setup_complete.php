<?php
if (THIS_SCRIPT == 'search')
{
	global $vbulletin;
	
	if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN' AND $vbulletin->options['vbo_search_loadlimit'] > 0)
	{
		if (!is_array($vbulletin->loadcache) OR $vbulletin->loadcache['lastcheck'] < (TIMENOW - 60))
		{
			update_loadavg();
		}

		if ($vbulletin->loadcache['loadavg'] > $vbulletin->options['vbo_search_loadlimit'])
		{
			$servertoobusy = true;

			if (intval($vbulletin->versionnumber) == 4 AND !($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']))
			{
				$vbulletin->options['useforumjump'] = 0;
				standard_error(fetch_error('toobusy'));
			}
		}
	}
}
?>