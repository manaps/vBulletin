<?php
if (vb_optimise::check_cache('notices'))
{
	if ($_POST['do'] == 'dismissnotice')
	{
		vb_optimise::$cache->set('notices_' . $vbulletin->userinfo['userid'], '');
	}
}
?>