<?php
$post['contenttype'] = $entryinfo['contenttype'];

// Parse the content row
THANKS::parseRow($post);

// Set this
$link = ($post['url'] ? $post['url'] : '');

if (class_exists('VBSHOUT') AND $vbulletin->options['dbtech_thanks_shoutbox'])
{
	foreach ((array)VBSHOUT::$cache['instance'] as $instanceid => $instance)
	{
		// Initialise BBCode Permissions
		$permarray = array(
			'bbcode' => (method_exists('VBSHOUT', 'loadInstanceBbcodePermissions') ? VBSHOUT::loadInstanceBbcodePermissions($instance, $vbulletin->userinfo) : VBSHOUT::load_instance_bbcodepermissions($instance, $vbulletin->userinfo))
		);
		
		if ($post['url'] AND ((int)$permarray['bbcode']['bit'] & 64))
		{
			// We had an URL
			$text = '[URL="' . $post['url'] . '"]' . $post['threadtitle'] . '[/URL]';
			$user = '[URL="' . $vbulletin->options['bburl'] . '/member.php?u=' . $post['userid'] . '"]' . $post['username'] . '[/URL]';
		}
		else
		{
			// No URL
			$text = $post['threadtitle'];
			$user = $post['username'];
		}

		// Init the Shout DM
		$shout = VBSHOUT::initDataManager('Shout', $vbulletin, ERRTYPE_STANDARD);
			$shout->set_info('automated', true);				
			$shout->set('message', '/me ' . construct_phrase($vbphrase['dbtech_thanks_x_clicked_y_for_z_' . $post['contenttype'] . '_a'],
				'',
				$vbphrase['dbtech_thanks_button_' . $entryinfo['varname'] . '_title'],
				$text,
				$user
			))
			->set('userid', $vbulletin->userinfo['userid'])
			->set('id', $post['userid'])
			->set('type', VBSHOUT::$shouttypes['custom'])
			->set('instanceid', $instanceid);
		
		// Get the shout id
		$shoutid = $shout->save();
		unset($shout);
	}
}
?>