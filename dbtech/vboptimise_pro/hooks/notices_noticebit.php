<?php
if ($vbulletin->noticecache["$_noticeid"]['override_dismiss'] && $vbulletin->userinfo['userid'])
{
	$show['dismiss_link'] = true;
}
?>