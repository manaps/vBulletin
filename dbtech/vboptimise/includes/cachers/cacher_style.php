<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

function vboptimise_fetch_style($styleid, $selection = '')
{
	global $vbulletin;

	if (($styleinfo = vb_optimise::$cache->get('style_' . $styleid)) !== false)
	{
		$style = is_array($styleinfo) ? $styleinfo : unserialize($styleinfo);

		if (!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) AND !$selection && intval($style['userselect']) != 1)
		{
			unset($styleinfo, $style);

			return false;
		}

		unset($styleinfo);

		return $style;
	}

	return false;
}

if (($style = vboptimise_fetch_style($argumentb, $argumentc)) !== false)
{
	$argument = $style;

	vb_optimise::report('Fetched Style from cache (ID: ' . $argumentb . ')');
	vb_optimise::stat(1);
}
else
{
	$argument = $vbulletin->db->query_first_slave("
		SELECT *
		FROM " . TABLE_PREFIX . "style
		WHERE (styleid = $argumentb" . iif(!($vbulletin->userinfo['permissions']['adminpermissions'] & $vbulletin->bf_ugp_adminpermissions['cancontrolpanel']) AND !$argumentc, ' AND userselect = 1') . ")
			OR styleid = " . $vbulletin->options['styleid'] . "
		ORDER BY styleid " . iif($argumentb > $vbulletin->options['styleid'], 'DESC', 'ASC') . "
		LIMIT 1
	");

	vb_optimise::$cache->set('style_' . $argumentb, serialize($argument));
	vb_optimise::report('Cached Style (ID: ' . $argumentb . ')');
}