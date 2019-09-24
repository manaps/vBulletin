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

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
* Fetches the tagbits for display in an entry
*
* @param	array	Blog info
*
* @return	string	Tag bits
*/
function fetch_entry_tagbits($bloginfo, &$userinfo)
{
	global $vbulletin, $vbphrase, $show, $template_hook;

	$tagcount = 0;
	$tag_list = array();

	if ($bloginfo['taglist'])
	{
		$tag_array = explode(',', $bloginfo['taglist']);

		foreach ($tag_array AS $tag)
		{
			$tag = trim($tag);
			if ($tag === '')
			{
				continue;
			}

			$tagcount++;
			$row['tag'] = fetch_word_wrapped_string($tag);
			$row['url'] = urlencode(unhtmlspecialchars($tag));
			$row['comma'] = $vbphrase['comma_space'];
			$row['pageinfo'] = array('tag' => $row['url']);

			($hook = vBulletinHook::fetch_hook('blog_tag_fetchbit')) ? eval($hook) : false;

			$tag_list[$tagcount] = $row;
		}
	}

	// Last element
	if ($tagcount) 
	{
		$tag_list[$tagcount]['comma'] = '';
	}

	$vbblog_url = $vboptions['vbblog_url'] ? $vboptions['vbblog_url'] . '/' : '';

	($hook = vBulletinHook::fetch_hook('blog_tag_fetchbit_complete')) ? eval($hook) : false;

	$templater = vB_Template::create('blog_taglist');
		$templater->register('vbblog_url', $vbblog_url);
		$templater->register('userinfo', $userinfo);
		$templater->register('tag_list', $tag_list);
	$tag_list = trim($templater->render());

	return $tag_list;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
?>
