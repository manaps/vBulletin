<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/
if (!VB_API) die;

class vB_APIMethod_api_cmscategorylist extends vBI_APIMethod
{
	public function output()
	{
		global $vbulletin;

		$sectionid = 1;

		//First we'll generate the category list

		//compose the sql
		$rst = vB::$vbulletin->db->query_read($sql = "SELECT parent.category AS parentcat, cat.categoryid, cat.category,
		cat.catleft, cat.catright, info.title AS node, parentnode.nodeid, count(nodecat.nodeid) as qty
	FROM " . TABLE_PREFIX . "cms_node AS node
		INNER JOIN " . TABLE_PREFIX . "cms_node AS parentnode ON (node.nodeleft >= parentnode.nodeleft AND node.nodeleft <= parentnode.noderight)
		INNER JOIN " . TABLE_PREFIX . "cms_nodeinfo AS info ON info.nodeid = parentnode.nodeid
	INNER JOIN " . TABLE_PREFIX . "cms_category AS parent on parent.parentnode = node.nodeid
		INNER JOIN " . TABLE_PREFIX . "cms_category AS cat ON (cat.catleft >= parent.catleft AND cat.catleft <= parent.catright)
		LEFT JOIN " . TABLE_PREFIX . "cms_nodecategory AS nodecat ON nodecat.categoryid = cat.categoryid
		WHERE parentnode.nodeid = " . $sectionid . " AND " . vBCMS_Permissions::getPermissionString() . "
		GROUP BY parent.category, cat.categoryid, cat.category,
		cat.catleft, cat.catright, info.title, parentnode.nodeid
		ORDER BY node.nodeleft, catleft;");

		$parents = array();
		$level = 0;
		$nodes = array();
		if ($record = vB::$vbulletin->db->fetch_array($rst))
		{
			$record['level'] = $level;
			$record['route_info'] = $record['categoryid'] .
				($record['category'] != '' ? '-' . str_replace(' ', '-', $record['category']) : '');
			$nodes[strtolower($record['category'])] = $parents[0] = $record;
			$last_category = -1;

			while($record = vB::$vbulletin->db->fetch_array($rst))
			{
				$record['route_info'] = $record['categoryid'] .
					($record['category'] != '' ? '-' . str_replace(' ', '-', $record['category']) : '');

				if ($record['categoryid'] == $last_category )
				{
					continue;
				}

				//note that since we're already sorted by by catleft we don't need to check that.
				while((intval($record['catright']) > intval($parents['level']['catright'])) AND $level > 0)
				{
					$level--;
				}
				$level++;
				$record['level'] = $level;

				$nodes[strtolower($record['category'])] = $parents[$level] = $record;
				$last_category = $record['categoryid'];
			}
		}
		ksort($nodes);

		return $nodes;
		
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
