<?php
global $vbulletin, $vbphrase, $template_hook;

if (REGISTRATION::$permissions['canview'] AND version_compare($vbulletin->versionnumber, '4.2.0', '<'))
{
	if (THIS_SCRIPT == 'registration')
	{
		$vbulletin->options['selectednavtab'] = 'dbtech_registration';
	}
	
	if (intval($vbulletin->versionnumber) == 3 AND !class_exists('vB_Template'))
	{
		// We need the template class
		require_once(DIR . '/dbtech/registration/includes/class_template.php');
	}	
	
	if (intval($vbulletin->versionnumber) > 3 AND (defined('CMS_SCRIPT') OR defined('VBA_SCRIPT')) AND !defined('REGISTRATION_NAV_LOOPED') AND THIS_SCRIPT != 'registration')
	{
		// vB4 have an awkward design quirk with the Suite, we'll fire the plugin elsewhere
		define('REGISTRATION_NAV_LOOPED', true);
		$vbulletin->pluginlist['process_templates_complete'] .= "\r\nrequire(DIR . '/dbtech/registration/hooks/process_templates_complete.php');";
		vBulletinHook::set_pluginlist($vbulletin->pluginlist);
	}
	else
	{
		if ($vbulletin->options['dbtech_registration_integration'] & 1)
		{
			$template_hook['navbar_quick_links_menu_pos4'] .= vB_Template::create('dbtech_registration_quicklinks_link')->render();
		}
		if ($vbulletin->options['dbtech_registration_integration'] & 2)
		{
			$template_hook['navbar_community_menu_end'] .= vB_Template::create('dbtech_registration_quicklinks_link')->render();
		}
		
		if ($vbulletin->options['dbtech_registration_navbar'])
		{
			if (intval($vbulletin->versionnumber) == 3)
			{
				$template_hook['navbar_buttons_right'] .= vB_Template::create('dbtech_registration_navbar_link')->render();
			}
			else
			{
				// Hook into nav tab
				$template_hook['navtab_middle'] .= vB_Template::create('dbtech_registration_navbar_link')->render();
			}
		}
	}
}
?>