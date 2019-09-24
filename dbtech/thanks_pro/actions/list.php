<?php
/*======================================================================*\
|| #################################################################### ||
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2013 Fillip Hannisdal AKA Revan/NeoRevan/Belazor 	  # ||
|| # All Rights Reserved. 											  # ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------------------------------------------------------- # ||
|| # You are not allowed to use this on your server unless the files  # ||
|| # you downloaded were done so with permission.					  # ||
|| # ---------------------------------------------------------------- # ||
|| #################################################################### ||
\*======================================================================*/

// Various optional limitations
$varname 			= $vbulletin->input->clean_gpc('r', 'varname', 			TYPE_STR);
$userid 			= $vbulletin->input->clean_gpc('r', 'userid', 			TYPE_UINT);
$receiveduserid 	= $vbulletin->input->clean_gpc('r', 'receiveduserid', 	TYPE_UINT);
//$contenttype 		= $vbulletin->input->clean_gpc('r', 'contenttype', 		TYPE_STR);
$contenttype 		= 'post';
$contentid 			= $vbulletin->input->clean_gpc('r', 'contentid', 		TYPE_UINT);

if (!$vbulletin->options['dbtech_thanks_enablelist']/* AND !$varname AND !$contentid*/)
{
	// Disabled integration
	print_no_permission();
}

// Set page titles
$pagetitle = $navbits[] = $vbphrase['dbtech_thanks_entries'];

// draw cp nav bar
THANKS::setNavClass('list');

// Shorthand the forumids we're allowed to see
$forumids = THANKS::getForumIds();

$lookup = array();
foreach (THANKS::$cache['button'] as $button)
{
	$lookup[$button['varname']] = $button;
}

// Begin the page template
$page_templater = vB_Template::create('dbtech_thanks_list');
	$page_templater->register('pagetitle', $pagetitle);

$pagenumber = $vbulletin->input->clean_gpc('r', 'pagenumber', TYPE_UINT);
$perpage = $vbulletin->input->clean_gpc('r', 'perpage', TYPE_UINT);

// Shorthands to faciliate easy copypaste
$pagenumber = ($pagenumber ? $pagenumber : 1);
$perpage = ($perpage ? $perpage : 25);

$hook_query_where = '';

if ($varname)
{
	$hook_query_where .= 'AND entry.varname = ' . $db->sql_prepare($varname);
}
if ($userid)
{
	$hook_query_where .= 'AND entry.userid = ' . $db->sql_prepare($userid);
}
if ($receiveduserid)
{
	$hook_query_where .= 'AND entry.receiveduserid = ' . $db->sql_prepare($receiveduserid);
}
if ($contenttype)
{
	$hook_query_where .= 'AND entry.contenttype = ' . $db->sql_prepare($contenttype);
}
if ($contentid)
{
	$hook_query_where .= 'AND entry.contentid = ' . $db->sql_prepare($contentid);
}

// Count number of users
$count = THANKS::$db->fetchOne('
	SELECT COUNT(*)
	FROM $dbtech_thanks_entry AS entry
	:hookQueryJoin
	WHERE 1=1
	:hookQueryWhere
', array(
	':hookQueryJoin' 	=> $hook_query_join,
	':hookQueryWhere' 	=> $hook_query_where,
));

if (!$count)
{
	// Invalid instance
	eval(standard_error(fetch_error('nothing_to_do')));
}

// Ensure every result is as it should be
sanitize_pageresults($count, $pagenumber, $perpage);

// Find out where to start
$startat = ($pagenumber - 1) * $perpage;

// Constructs the page navigation
$pagenav = construct_page_nav(
	$pagenumber,
	$perpage,
	$count,
	'thanks.php?' . $vbulletin->session->vars['sessionurl'] . 'do=list',
	"&amp;perpage=$perpage" .
		($varname ? "&amp;varname=$varname" : '') .
		($userid ? "&amp;userid=$userid" : '') .
		($receiveduserid ? "&amp;userid=$receiveduserid" : '') .
		($contenttype ? "&amp;contenttype=$contenttype" : '') .
		($contentid ? "&amp;contentid=$contentid" : '')
);

// Page navigation registration
$page_templater->register('pagenav', $pagenav);

$resultsToSort = array();
$results = THANKS::$db->fetchAll('
	SELECT entryid
	FROM $dbtech_thanks_entry AS entry
	WHERE 1=1
	:hookQueryWhere
	ORDER BY entryid DESC
	LIMIT :limitStart, :limitEnd
', array(
	':hookQueryWhere' 	=> $hook_query_where,
	':limitStart' 	=> $startat,
	':limitEnd' 	=> $perpage
));
foreach ($results as $result)
{
	// Store the entry ids
	$resultsToSort[] = $result['entryid'];
}
rsort($resultsToSort, SORT_NUMERIC);

while (count($resultsToSort) > $perpage)
{
	// Remove one element off the end of the page
	array_pop($resultsToSort);
}

if (!count($resultsToSort))
{
	// Had no results
	return true;
}

// Fetch users
$results = THANKS::$db->fetchAll('
	SELECT
		post.postid,
		post.title AS posttitle,
		thread.title AS threadtitle,
		thread.title AS title,
		thread.threadid,
		thread.forumid,
		entry.*,
		user.username,
		user.usergroupid,
		user.membergroupids,
		user.infractiongroupid,
		user.displaygroupid,
		receiveduser.username AS receivedusername,
		receiveduser.usergroupid AS receivedusergroupid,
		receiveduser.membergroupids AS receivedmembergroupids,
		receiveduser.infractiongroupid AS receivedinfractiongroupid,
		receiveduser.displaygroupid AS receiveddisplaygroupid
		:vBShop
	FROM $dbtech_thanks_entry AS entry
	LEFT JOIN $user AS user USING(userid)
	LEFT JOIN $user AS receiveduser ON(receiveduser.userid = entry.receiveduserid)
	LEFT JOIN $post AS post ON(post.postid = entry.contentid)
	LEFT JOIN $thread AS thread ON(thread.threadid = post.threadid)
	WHERE entry.entryid :entryList
		AND post.visible = 1
	:hookQueryWhere
', array(
	':hookQueryWhere' 	=> $hook_query_where,
	':vBShop' 			=> ($vbulletin->products['dbtech_vbshop'] ? ", user.dbtech_vbshop_purchase, receiveduser.dbtech_vbshop_purchase AS receivedpurchase" : ''),
	':entryList' 		=> THANKS::$db->queryList($resultsToSort)
));
$resultsToSort = array();
foreach ($results as $result)
{
	$resultsToSort[$result['entryid']] = $result;
}
krsort($resultsToSort, SORT_NUMERIC);

$entries = '';
foreach ($resultsToSort as $entry)
{
	// Ensure we have the proper day selected for the grouping
	$day = vbdate($vbulletin->options['dateformat'], $entry['dateline']);

	$received = array(
		'userid' 					=> $entry['receiveduserid'],
		'username' 					=> $entry['receivedusername'],
		'usergroupid' 				=> $entry['receivedusergroupid'],
		'membergroupids' 			=> $entry['receivedmembergroupids'],
		'infractiongroupid' 		=> $entry['receivedinfractiongroupid'],
		'displaygroupid' 			=> $entry['receiveddisplaygroupid'],
		'dbtech_vbshop_purchase' 	=> $entry['receivedpurchase'],
	);

	// Grab the markup username
	fetch_musername($entry);
	fetch_musername($received);

	// Determine what title to use
	$title = (in_array($entry['forumid'], $forumids) ? unhtmlspecialchars($entry['posttitle'] ? $entry['posttitle'] : $entry['threadtitle']) : '');
	//$title = (intval($vbulletin->versionnumber) == 3 ? unhtmlspecialchars($title) : $title);

	// Set some important variables
	$entry['date'] = vbdate($vbulletin->options['dateformat'], $entry['dateline']) . ' ' . vbdate($vbulletin->options['timeformat'], $entry['dateline']);
	$entry['type'] = $vbphrase['dbtech_thanks_button_' . $entry['varname'] . '_title'];

	if (THANKS::checkPermissions($vbulletin->userinfo, $lookup[$entry['varname']]['permissions'], 'cannotseeclicks') AND THANKS::$isPro)
	{
		// Hide user
		$entry['userid'] = 0;
	}

	$templater = vB_Template::create('dbtech_thanks_list_bit');
		$templater->register('entry', 		$entry);
		$templater->register('received', 	$received);
		$templater->register('post', 		$title);
	$entries .= $templater->render();
}

	$page_templater->register('results', $entries);
$HTML = $page_templater->render();