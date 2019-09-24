<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright 2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * The vB core class.
 * Everything required at the core level should be accessible through this.
 *
 * The core class performs initialisation for error handling, exception handling,
 * application instatiation and optionally debug handling.
 *
 * @TODO: Much of what goes on in global.php and init.php will be handled, or at
 * least called here during the initialisation process.  This will be moved over as
 * global.php is refactored.
 *
 * @package vBulletin
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vB_dB_MYSQL_Query extends vB_dB_Query
{
	/*Properties====================================================================*/

	protected $db_type = 'MYSQL';



	/** This is the definition for queries we will process through.  We could also
	 * put them in the database, but this eliminates a query.
	 * **/
	protected $query_data = array(
		'select_section' => array('querytype'=> 's',
			'query_string' =>  'SELECT {sql_calc} node.nodeid AS itemid,
					(node.nodeleft = 1) AS isroot, node.nodeid, node.contenttypeid, node.contentid, node.url, node.parentnode, node.styleid, node.userid,
					node.layoutid, node.publishdate, node.setpublish, node.issection, parent.permissionsfrom as parentpermissions,
					node.permissionsfrom, node.publicpreview, node.showtitle, node.showuser, node.showpreviewonly, node.showall,
					node.showupdated, node.showviewcount, node.showpublishdate, node.settingsforboth, node.includechildren, node.editshowchildren,
					node.shownav, node.hidden, node.nosearch, node.nodeleft,
					info.description, info.title, info.html_title, info.viewcount, info.creationdate, info.workflowdate,
					info.workflowstatus, info.workflowcheckedout, info.workflowlevelid, info.associatedthreadid,
					user.username, sectionorder.displayorder, thread.replycount, parentinfo.title AS parenttitle
					{$hook_query_fields}
				FROM {TABLE_PREFIX}cms_node AS node
				INNER JOIN {TABLE_PREFIX}cms_nodeinfo AS info ON info.nodeid = node.nodeid
				{$hook_query_join}
				LEFT JOIN {TABLE_PREFIX}user AS user ON user.userid = node.userid
				LEFT JOIN {TABLE_PREFIX}thread AS thread ON thread.threadid = info.associatedthreadid
				LEFT JOIN {TABLE_PREFIX}cms_sectionorder AS sectionorder ON sectionorder.sectionid = {filter_node}
					AND sectionorder.nodeid = node.nodeid
				LEFT JOIN {TABLE_PREFIX}cms_node AS parent ON parent.nodeid = node.parentnode
				LEFT JOIN {TABLE_PREFIX}cms_nodeinfo AS parentinfo ON parentinfo.nodeid = parent.nodeid
				INNER JOIN {TABLE_PREFIX}cms_node AS rootnode
					ON rootnode.nodeid = {filter_node} AND (node.nodeleft >= rootnode.nodeleft AND node.nodeleft <= rootnode.noderight) AND node.nodeleft != rootnode.nodeleft
				  {$extrasql} AND node.contenttypeid <> {sectiontype} AND node.new != 1'),
		'updt_nodeconfig' => array('querytype'=> 'u',
			'query_string' =>  "UPDATE {TABLE_PREFIX}cms_nodeconfig SET value='{value}' WHERE nodeid={nodeid} AND name='{name}';"),
		'del_nodeconfig' => array('querytype'=> 'd',
			'query_string' =>  'DELETE FROM {TABLE_PREFIX}cms_nodeconfig WHERE nodeid={nodeid} AND name=\'{name}\''),
		'ins_nodeconfig' => array('querytype'=> 'i',
			'query_string' =>  "INSERT INTO {TABLE_PREFIX}cms_nodeconfig (nodeid, name, value, serialized)
			VALUES({nodeid}, '{name}','{value}', {serialized});"),
		'sel_nodeconfig' => array('querytype'=> 's',
			'query_string' =>  'SELECT * FROM {TABLE_PREFIX}cms_nodeconfig WHERE nodeid={nodeid} ORDER BY name;'),
		'get_user_theme' => array('querytype'=> 's',
			'query_string' => 'SELECT prof2.* FROM {TABLE_PREFIX}customprofile prof1 INNER JOIN
			{TABLE_PREFIX}customprofile prof2 ON prof2.customprofileid = prof1.themeid WHERE prof1.userid
			= {userid}')	);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
