<?php
switch ($filename)
{
	case 'registration.php':
		if ($values['do'] == 'main' OR !$values['do'])
		{
			$userinfo['activity'] = 'dbtech_vbanalytics_';
		}
		break;
}
?>