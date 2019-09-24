<?php
/*DBTECH_BRANDING_START*/
// Show branding or not
$show['vboptimise_producttype'] = ' (Lite)';
/*DBTECH_PRO_START*/
$show['vboptimise_producttype'] = ' (Pro)';
/*DBTECH_PRO_END*/

if (!$show['_dbtech_branding_override'])
{
	$brandingVariables = array(
		'flavour' 			=> 'vBulletin Optimisation provided by ',
		'productid' 		=> 1,
		'utm_source' 		=> str_replace('www.', '', htmlspecialchars_uni($_SERVER['HTTP_HOST'])),
		'utm_content' 		=> ($show['vboptimise_producttype'] != ' (Pro)' ? 'Lite' : 'Pro'),
		'referrerid' 		=> $vbulletin->options['vbo_referral'],
		'title' 			=> 'vB Optimise',
		'displayversion' 	=> $vbulletin->options['vbo_displayversion'],
		'version' 			=> '2.7.1',
		'producttype' 		=> $show['vboptimise_producttype']
	);

	$str = $brandingVariables['flavour'] . '
		<a rel="nofollow" href="https://www.dragonbyte-tech.com/vbecommerce.php' . ($brandingVariables['productid'] ? '?productid=' . $brandingVariables['productid'] . '&do=product&' : '?') . 'utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=product&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">' . $brandingVariables['title'] . ($brandingVariables['displayversion'] ? ' v' . $brandingVariables['version'] : '') . $brandingVariables['producttype'] . '</a> -
		<a rel="nofollow" href="https://www.dragonbyte-tech.com/?utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=site&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">vBulletin Mods &amp; Addons</a> Copyright &copy; ' . date('Y') . ' DragonByte Technologies Ltd.';
	// $vbulletin->options['copyrighttext'] = (trim($vbulletin->options['copyrighttext']) != '' ? $str . '<br />' . $vbulletin->options['copyrighttext'] : $str);
}
/*DBTECH_BRANDING_END*/

if ($vbulletin->options['vbo_footer_info'] OR $vbulletin->options['vbo_footer_stats'])
{
	$str = '';
	if ($vbulletin->options['vbo_footer_info'])
	{
		// We're adding saved resources
		$str .= '<!--VBO_SAVED-->';
	}

	// Ensure this is an array
	$vbulletin->options['vbo_footer_stats_usergroups'] = is_array($vbulletin->options['vbo_footer_stats_usergroups']) ? $vbulletin->options['vbo_footer_stats_usergroups'] : @unserialize($vbulletin->options['vbo_footer_stats_usergroups']);
	$vbulletin->options['vbo_footer_stats_usergroups'] = is_array($vbulletin->options['vbo_footer_stats_usergroups']) ? $vbulletin->options['vbo_footer_stats_usergroups'] : array();

	if ($vbulletin->options['vbo_footer_stats'] AND is_member_of($vbulletin->userinfo, $vbulletin->options['vbo_footer_stats_usergroups']))
	{
		// We're adding the stat stuff
		$str .= '<!--VBO_STATS-->';
	}

	$vbulletin->options['copyrighttext'] = (trim($vbulletin->options['copyrighttext']) != '' ? $str . '<br />' . $vbulletin->options['copyrighttext'] : $str);
}
?>