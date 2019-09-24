<?php
// Fetch required classes
require_once(DIR . '/dbtech/thanks/includes/class_core.php');
require_once(DIR . '/dbtech/thanks/includes/class_cache.php');
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/thanks/includes/class_template.php');
}

if (isset($this) AND is_object($this))
{
	// Loads the cache class
	THANKS_CACHE::init($vbulletin, $this->datastore_entries);
}
else
{
	// Loads the cache class
	THANKS_CACHE::init($vbulletin, $specialtemplates);
}

// Initialise thanks
THANKS::init($vbulletin);

//if (THANKS::$permissions['canview'])
//{
	$show['thanks'] = $vbulletin->options['dbtech_thanks_navbar'];
	$show['thanks_ispro'] = THANKS::$isPro;
	if ($vbulletin->options['dbtech_thanks_integration'] & 1)
	{
		$show['thanks_ql'] = true;
	}
	if ($vbulletin->options['dbtech_thanks_integration'] & 2)
	{
		$show['thanks_com'] = true;
	}
//}

/*DBTECH_BRANDING_START*/
// Show branding or not
$show['dbtech_thanks_producttype'] = (THANKS::$isPro ? ' (Pro)' : ' (Lite)');

if (defined('THIS_SCRIPT') && in_array(THIS_SCRIPT, array('thanks', 'showthread', 'blog', 'dbtech_gallery')) AND !$show['_dbtech_branding_override'])
{
	$brandingVariables = array(
		'flavour' 			=> 'Feedback Buttons provided by ',
		'productid' 		=> 22,
		'utm_source' 		=> str_replace('www.', '', htmlspecialchars_uni($_SERVER['HTTP_HOST'])),
		'utm_content' 		=> (THANKS::$isPro ? 'Pro' : 'Lite'),
		'referrerid' 		=> $vbulletin->options['dbtech_thanks_referral'],
		'title' 			=> 'Advanced Post Thanks / Like',
		'displayversion' 	=> $vbulletin->options['dbtech_thanks_displayversion'],
		'version' 			=> '3.6.3',
		'producttype' 		=> $show['dbtech_thanks_producttype']
	);

	$str = $brandingVariables['flavour'] . '
		<a rel="nofollow" href="https://www.dragonbyte-tech.com/vbecommerce.php' . ($brandingVariables['productid'] ? '?productid=' . $brandingVariables['productid'] . '&do=product&' : '?') . 'utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=product&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">' . $brandingVariables['title'] . ($brandingVariables['displayversion'] ? ' v' . $brandingVariables['version'] : '') . $brandingVariables['producttype'] . '</a> -
		<a rel="nofollow" href="https://www.dragonbyte-tech.com/?utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=site&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">vBulletin Mods &amp; Addons</a> Copyright &copy; ' . date('Y') . ' DragonByte Technologies Ltd.';
	// $vbulletin->options['copyrighttext'] = (trim($vbulletin->options['copyrighttext']) != '' ? $str . '<br />' . $vbulletin->options['copyrighttext'] : $str);
}
/*DBTECH_BRANDING_END*/