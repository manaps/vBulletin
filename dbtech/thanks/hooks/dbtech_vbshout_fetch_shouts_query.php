<?php
if ($args['types'] & 16)
{
	// We're in the tab
	$hook_query_and .= " AND (vbshout.userid = '" . intval(self::$vbulletin->userinfo['userid']) . "' OR vbshout.id = '" . intval(self::$vbulletin->userinfo['userid']) . "')";
}
?>