<?php

class vBSocial_Notification_View_Dbtech_Thanks extends vBSocial_Notification_View_Base
{
	private $titleLength = 50;

	public function process()
	{
		global $vbphrase;

		if (empty($this->content))
		{
			return;
		}

		$infoArray = $this->registry->db->query_read_slave("
			SELECT entry.*, button.*
			FROM " . TABLE_PREFIX . "dbtech_thanks_entry AS entry
			LEFT JOIN " . TABLE_PREFIX . "dbtech_thanks_button AS button ON(button.varname = entry.varname)
			WHERE entry.entryid IN (" . implode(",", array_keys($this->content)) . ")
		");

		while ($info = $this->registry->db->fetch_array($infoArray))
		{
			$info['title'] = $vbphrase['dbtech_thanks_button_' . $info['varname'] . '_title'];

			$this->content[$info['entryid']] = $info;
		}
	}

	/**
	* Prepare the item for output and check whether item exist or not.
	*
	* @return bool
	*/
	public function prepareItem(&$result)
	{
		$contentId = $result['content_id'];
		if (!is_array($this->content[$contentId]))
		{
			return;
		}

		$content =& $this->content[$contentId];

		if (file_exists(DIR . '/dbtech/thanks/includes/sql/vbsocial.php'))
		{
			// Grab ze code
			require(DIR . '/dbtech/thanks/includes/sql/vbsocial.php');

			// Hoping this is the right way round
			$mergedContent = array_merge($content, $sourceContent);

			// Parses the row and sets title / etc
			THANKS::parseRow($mergedContent);

			$title = $mergedContent['title'];
			if ($mergedContent['url'])
			{
				// Also add link
				$title = '<a href="' . $mergedContent['url'] . '" target="_blank">' . $title . '</a>';
			}

			$args = array(
				'<a href="' . ($result['userid'] ? ('member.php?' . $this->registry->session->vars['sessionurl'] . 'u=' . $result['userid']) : 'javascript://') . '">' . $result['username'] . '</a>',
				$content['title'],
				$title
			);
			$result['description'] = $this->getDescriptionFromPhrase($this->vbphrase['dbtech_thanks_x_clicked_y_for_z_' . $mergedContent['contenttype'] . '_b'], $args);
		}
		else
		{
			$args = array(
				$result['userid'] ? ('member.php?' . $this->registry->session->vars['sessionurl'] . 'u=' . $result['userid']) : 'javascript://',
				$result['username'],
				$content['title']
			);
			$result['description'] = $this->getDescriptionFromPhrase($this->vbphrase['dbtech_thanks_vbsocial_received_button_click_from_user'], $args);
		}

		$result['time'] = $this->vbphrase['fa_comment_icon'] . " $result[time]";

		return true;
	}
}