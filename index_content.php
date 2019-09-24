<?php
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

/* Tell forum.php to redirect 
to the default url as defined 
in the navigation manager */
define('VB_REDIRECT', true);

/**
 * If you want to move this file to the root of your website, change the 
 * line below to your vBulletin directory and uncomment it (delete the //).
 *
 * For example, if vBulletin is installed in '/forum' the line should
 * state: define('VB_RELATIVE_PATH', 'forum');
 *
 * Note: You may need to change the cookie path of your vBulletin
 * installation to enable your users to log in at the root of your website.
 * If you move this file to the root of your website then you should ensure
 * the cookie path is set to '/'.
 *
 * See 'Admin Control Panel
 *	->Cookies and HTTP Header Options
 *	  ->Path to Save Cookies
 */

//define('VB_RELATIVE_PATH', 'forum');

// Do not edit anything below //
if (defined('VB_RELATIVE_PATH'))
{
	chdir('./' . VB_RELATIVE_PATH);
}

require('forum.php');


/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
