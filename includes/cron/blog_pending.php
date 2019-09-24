<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin Blog 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
	exit;
}

require_once(DIR . '/includes/blog_functions.php');

$blogman = datamanager_init('Blog_Firstpost', $vbulletin, ERRTYPE_SILENT, 'blog');

$blogids = array();
$pendingposts = $vbulletin->db->query_read_slave("
	SELECT blog.*, blog_text.pagetext, blog_user.bloguserid
	FROM " . TABLE_PREFIX . "blog AS blog
	INNER JOIN " . TABLE_PREFIX . "blog_user AS blog_user ON (blog_user.bloguserid = blog.userid)
	LEFT JOIN " . TABLE_PREFIX . "blog_text AS blog_text ON (blog.firstblogtextid = blog_text.blogtextid)
	WHERE blog.pending = 1
		AND blog.dateline <= " . TIMENOW . "
");
while ($blog = $vbulletin->db->fetch_array($pendingposts))
{
	$blogman->set_existing($blog);

	// This sets bloguserid for the post_save_each_blogtext() function
	$blogman->set_info('user', $blog);
	$blogman->set_info('send_notification', true);
	$blogman->set_info('skip_build_category_counters', true);
	$blogman->save();

	if ($blog['state'] == 'visible')
	{
		$blogids[] = $blog['blogid'];
		$userids["$blog[userid]"] = $blog['userid'];
	}
}

if (!empty($blogids))
{
	// Update Counters
	foreach ($userids AS $userid)
	{
		build_blog_user_counters($userid);
	}
}

log_cron_action('', $nextitem, 1);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
?>
