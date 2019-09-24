<?php
// Fetch required classes
require_once(DIR . '/dbtech/registration/includes/class_core.php');
require_once(DIR . '/dbtech/registration/includes/class_cache.php');
if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
{
	// We need the template class
	require_once(DIR . '/dbtech/registration/includes/class_template.php');
}

if (isset($this) AND is_object($this))
{
	// Loads the cache class
	REGISTRATION_CACHE::init($vbulletin, $this->datastore_entries);
}
else
{
	// Loads the cache class
	REGISTRATION_CACHE::init($vbulletin, $specialtemplates);
}

// Initialise better registration
REGISTRATION::init($vbulletin);

if (REGISTRATION::$permissions['canview'])
{
	$show['registration'] = $vbulletin->options['dbtech_registration_navbar'];
	$show['registration_ispro'] = REGISTRATION::$isPro;
	if ($vbulletin->options['dbtech_registration_integration'] & 1)
	{
		$show['registration_ql'] = true;
	}
	if ($vbulletin->options['dbtech_registration_integration'] & 2)
	{
		$show['registration_com'] = true;
	}
}

/*DBTECH_BRANDING_START*/
// Show branding or not
$show['dbtech_registration_producttype'] = (REGISTRATION::$isPro ? ' (Pro)' : ' (Lite)');

if (defined('THIS_SCRIPT') && (THIS_SCRIPT == 'registration' OR THIS_SCRIPT == 'register') AND !$show['_dbtech_branding_override'])
{
	$brandingVariables = array(
		'flavour' 			=> 'Customised Registration Page provided by ',
		'productid' 		=> 194,
		'utm_source' 		=> str_replace('www.', '', htmlspecialchars_uni($_SERVER['HTTP_HOST'])),
		'utm_content' 		=> (REGISTRATION::$isPro ? 'Pro' : 'Lite'),
		'referrerid' 		=> $vbulletin->options['dbtech_registration_referral'],
		'title' 			=> 'Advanced Registration',
		'displayversion' 	=> $vbulletin->options['dbtech_registration_displayversion'],
		'version' 			=> '2.0.7 Patch Level 2',
		'producttype' 		=> $show['dbtech_registration_producttype']
	);

	$str = $brandingVariables['flavour'] . '
		<a rel="nofollow" href="https://www.dragonbyte-tech.com/vbecommerce.php' . ($brandingVariables['productid'] ? '?productid=' . $brandingVariables['productid'] . '&do=product&' : '?') . 'utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=product&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">' . $brandingVariables['title'] . ($brandingVariables['displayversion'] ? ' v' . $brandingVariables['version'] : '') . $brandingVariables['producttype'] . '</a> -
		<a rel="nofollow" href="https://www.dragonbyte-tech.com/?utm_source=' . $brandingVariables['utm_source'] . '&utm_campaign=site&utm_medium=' . urlencode(str_replace(' ', '+', $brandingVariables['title'])) . '&utm_content=' . $brandingVariables['utm_content'] . ($brandingVariables['referrerid'] ? '&referrerid=' . $brandingVariables['referrerid'] : '') . '" target="_blank">vBulletin Mods &amp; Addons</a> Copyright &copy; ' . date('Y') . ' DragonByte Technologies Ltd.';
	// $vbulletin->options['copyrighttext'] = (trim($vbulletin->options['copyrighttext']) != '' ? $str . '<br />' . $vbulletin->options['copyrighttext'] : $str);
}
/*DBTECH_BRANDING_END*/

if	($vbulletin->options['dbtech_registration_active'] AND empty($vbulletin->userinfo['userid']) AND !$vbulletin->options['allowregistration']
		AND ($vbulletin->options['dbtech_registration_verify_email'] AND $vbulletin->options['dbtech_registration_verifyemail_override'])
		AND ($vbulletin->options['dbtech_registration_invites'] /*DBTECH_PRO_START*/AND $vbulletin->options['dbtech_registration_invites_override']/*DBTECH_PRO_END*/)
	)
{
	// show register link
	$vbulletin->options['allowregistration'] = 1;
}