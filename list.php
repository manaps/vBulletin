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

/**
 * The index for vBulletin.  This file simply grabs the procedural bootstraps.
 * This file can be moved and referenced with apache mod_rewrite.  This will require
 * editing the path to the bootstrap below.  However, the original file may be
 * reverted on upgrades.
 */

// Define this script as the route handler
define('VB_PRODUCT', 'vbcms');
define('VB_ENTRY', 'list.php');
define('VB_ROUTER_SEGMENT', 'list');
define('GET_EDIT_TEMPLATES', 'picture');
define('CMS_SCRIPT', true);
define('THIS_SCRIPT', 'vbcms');
define('FRIENDLY_URL_LINK', 'vbcms');

// Bootstrapping
require_once('vb/bootstrap.php');

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/