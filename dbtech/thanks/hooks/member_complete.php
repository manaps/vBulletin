<?php
do
{
	if (THIS_SCRIPT == 'fblhome')
	{
		// Disabled integration
		break;
	}

	if ($vbulletin->options['dbtech_thanks_disabledintegration'] & 16)
	{
		// Disabled integration
		break;
	}

	// Extract the variables from the entry processer
	list($colorOptions, $thanksEntries) = THANKS::processEntries();

	// Begin list of JS phrases
	$jsphrases = array(
		'dbtech_thanks_must_wait_x_seconds'	=> $vbphrase['dbtech_thanks_must_wait_x_seconds'],
		'dbtech_thanks_people_who_clicked'	=> $vbphrase['dbtech_thanks_people_who_clicked'],
		'dbtech_thanks_loading'				=> $vbphrase['dbtech_thanks_loading'],
		'dbtech_thanks_noone_clicked'		=> $vbphrase['dbtech_thanks_noone_clicked'],
	);

	// Escape them
	THANKS::jsEscapeString($jsphrases);

	$escapedJsPhrases = '';
	foreach ($jsphrases as $varname => $value)
	{
		// Replace phrases with safe values
		$escapedJsPhrases .= "vbphrase['$varname'] = \"$value\"\n\t\t\t\t\t";
	}

	$footer .= THANKS::js($escapedJsPhrases . '
		var thanksOptions = ' . THANKS::encodeJSON(array(
			'threadId' 		=> $userinfo['userid'],
			'vbversion' 	=> intval($vbulletin->versionnumber),
			'thanksEntries' => $thanksEntries,
			'contenttype' 	=> 'visitormessage',
			'floodTime' 	=> (int)$vbulletin->options['dbtech_thanks_floodcheck'],
		)) . ';
	', false, false);
	$footer .= THANKS::js('.version', true, false);
	$footer .= '<script type="text/javascript"> (window.jQuery && __versionCompare(window.jQuery.fn.jquery, "' . THANKS::$jQueryVersion . '", ">=")) || document.write(\'<script src="' . THANKS::jQueryPath() . '">\x3C/script>\'); </script>';
	$footer .= '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/thanks/clientscript/jquery.qtip.min.js"></script>';
	$footer .= THANKS::js('', true, false);
}
while (false);
?>