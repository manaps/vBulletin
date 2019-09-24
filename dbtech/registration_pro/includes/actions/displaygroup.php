<?php
class Registration_Displaygroup extends REGISTRATION_REGISTER
{
	protected static function can_exec($userinfo, $action)
	{
		global $vbulletin;

		if ($userinfo['displaygroupid'] == $action['value'])
		{
			// Already set
			return false;
		}
				
		if (empty($vbulletin->usergroupcache[$action['value']]))
		{
			// Invalid usergroup
			return false;
		}

		return true;
	}
	
	public static function exec_action($userinfo, $action)
	{
		global $vbulletin;

		if (!self::can_exec($userinfo, $action))
		{
			// Action can't be executed
			return false;
		}
	
		$userdata =& datamanager_init('User', $vbulletin, ERRTYPE_SILENT);
			$userdata->set_existing($userinfo);
						
			// set displaygroupid
			$userdata->set('displaygroupid', $action['value']);
					
		$userdata->save();
	}
}
?>