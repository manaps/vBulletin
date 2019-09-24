<?php
if (self::$vbulletin->options['dbtech_thanks_shoutbox'])
{
	$thankstab = 'thanks_' . self::$vbulletin->userinfo['userid'] . '_';	
	if (is_array($unsortedTabs))
	{
		// 6.x
		$unsortedTabs[$thankstab . $instance['instanceid']] = array(
			'text' 		=> $vbphrase['dbtech_thanks_thanks'],
			'canclose' 	=> '0',
		);		
	}
	else
	{
		// 5.4.x or lower
		$chattabs[$thankstab] = "vBShout" . $instance['instanceid'] . ".add_tab('" . $thankstab . "', '" . $vbphrase['dbtech_thanks_thanks'] . "', false);";
	}
}
?>