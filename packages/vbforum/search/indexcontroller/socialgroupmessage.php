<?php if (!defined('VB_ENTRY')) die('Access denied.');

/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * @package vBulletin
 * @subpackage Search
 * @author Kevin Sours, vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
require_once (DIR."/vb/search/core.php");


/**
 * Index Controller for group Messages
 *
 * @package vBulletin
 * @subpackage Search
 */
class vBForum_Search_IndexController_SocialGroupMessage extends vB_Search_IndexController
{
  public function __construct()
  {
     $this->contenttypeid = vB_Search_Core::get_instance()->get_contenttypeid("vBForum", "SocialGroupMessage");
     $this->groupcontenttypeid = vB_Search_Core::get_instance()->get_contenttypeid("vBForum", "SocialGroupDiscussion");
  }

	public function get_max_id()
	{
		global $vbulletin;
		$row = $vbulletin->db->query_first_slave("
			SELECT max(gmid) AS max FROM " . TABLE_PREFIX . "groupmessage"
		);
		return $row['max'];
	}

	/**
	 * Index group message
	 *
	 * @param int $id
	 */
	public function index($id)
	{
		//We tweaked this query to avoid the groupdiscussion table because it appears that
		//there are case where the group message record gets added before the to the
		global $vbulletin;

		$row = $vbulletin->db->query_first(
			$this->get_query("m.gmid = " . intval($id))
		);
		
		vB_Search_Core::get_instance()->get_core_indexer()->index($this->record_to_indexfields($row));
	}

	/**
	 * Index group message range
	 *
	 * @param int $start
	 * @param int $end
	 */
	public function index_id_range($start, $end)
	{
		global $vbulletin;
		$set = $vbulletin->db->query(
			$this->get_query("m.gmid >= " . intval($start) . " AND m.gmid <= " . intval($end))
		);

		$indexer = vB_Search_Core::get_instance()->get_core_indexer();
		while ($row = $vbulletin->db->fetch_array($set))
		{
			$indexer->index($this->record_to_indexfields($row));
		}
		$vbulletin->db->free_result($set);
	}

	private function get_query($where)
	{
			return "
				SELECT m.*, 
					d.firstpostid,
					fp.title as discussiontitle, 
					fp.postuserid as discussionuserid,
					fp.postusername as discussionusername, 
					fp.dateline as discussiondateline
				FROM " . TABLE_PREFIX . "groupmessage AS m JOIN " . 
					TABLE_PREFIX . "discussion d ON m.discussionid = d.discussionid JOIN " . 
					TABLE_PREFIX . "groupmessage fp ON d.firstpostid = fp.gmid
				WHERE $where";
	}


	//is this even possible?
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $oldid
	 * @param unknown_type $newid
	 */
	public function merge_groups($oldid, $newid)
	{
	}


	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 */
	public function delete_group($id)
	{
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 */
	public function index_category($id)
	{
	}

	//is this even possible?
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $oldid
	 * @param unknown_type $newid
	 */
	public function merge_categories($oldid, $newid)
	{
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $id
	 */
	public function delete_category($id)
	{
	}

	/**
	 * Convert the basic table row to the index fieldset
	 *
	 * @param array $record
	 * @return return index fields
	 */
	private function record_to_indexfields($record)
	{
		//make it easy to switch default fields
		//but with the current database structure it doesn't work
		//$default = '';
		//common fields
		$fields['contenttypeid'] = $this->get_contenttypeid();
		$fields['primaryid'] = $record['gmid'];
		$fields['groupcontenttypeid'] = $this->groupcontenttypeid;
		$fields['groupid'] = $record['discussionid'];
		
		$fields['dateline'] = $record['dateline'];
		$fields['groupdateline'] = $record['discussiondateline'];
		$fields['defaultdateline'] = $record['discussiondateline'];

		$fields['userid'] = $record['postuserid'];
		$fields['groupuserid'] = $record['discussionuserid'];
		$fields['defaultuserid'] = $record["discussionuserid"];

		$fields['username'] = $record['postusername'];
		$fields['groupusername'] = $record['discussionusername'];
		$fields['defaultusername'] = $record['discussionusername'];

		$fields['ipaddress'] = $record['ipaddress'];
		$fields['grouptitle'] = $record['discussiontitle'];

		$fields['keywordtext'] = $record['title'] . " " . $record['pagetext'];
		return $fields;
	}

	protected $contenttypeid;
	protected $groupcontenttypeid;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
