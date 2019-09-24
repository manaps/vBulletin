<?php

class vBSocial_Notification_Manage_Dbtech_Thanks extends vBSocial_Notification_Manage_Base
{
	/* Used to prevent multiple notifications for same postid */
	public $notifiedMembers = array();

	public $info = array();

	public function notifyThanked()
	{
		if (empty($this->info))
		{
			return;
		}

		$params = array(
			'userid' => $this->info['source']['userid'],
			'username' => $this->info['source']['username'],
			'content_type' => 'dbtech_thanks',
			'content_id'   => $this->info['entryid'],
			'event_date'   => TIMENOW,
			'action'	=> $this->info['button']['varname'],
			'notification_userid' => $this->info['target']['userid']
		);

		$this->save($params);

		$this->notifiedMembers[$this->info['target']['userid']] = 1;
	}
}