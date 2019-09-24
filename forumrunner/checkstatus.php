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

/*
 * Forum Runner
 *
 * Copyright (c) 2013 to Internet Brands, Inc
 *
 * License: http://www.forumrunner.com/lgpl30.txt
 *
 * http://www.forumrunner.com
 */

if (!is_object($vbulletin->db)) {
    exit;
}

define(MCWD, DIR . '/forumrunner');

require_once(DIR . '/forumrunner/support/Snoopy.class.php');
require_once(DIR . '/forumrunner/support/JSON.php');
if (file_exists(DIR . '/forumrunner/sitekey.php')) {
    require_once(DIR . '/forumrunner/sitekey.php');
} else if (file_exists(DIR . '/forumrunner/vb_sitekey.php')) {
    require_once(DIR . '/forumrunner/vb_sitekey.php');
}

// You must have your valid Forum Runner forum site key.  This can be
// obtained from http://www.forumrunner.com in the Forum Manager.
if (!$mykey || $mykey == '') {
    exit;
}

 // Check to see if our prompt is disabled.  If so, exit.
if (!$vbulletin->options['forumrunner_redirect_onoff']) {
    return;
}

// We know we have a prompt enabled at this point.  Phone home for status.
$snoopy = new snoopy();
$snoopy->submit('http://www.forumrunner.com/forumrunner/request.php',
    array(
        'cmd' => 'checkstatus',
        'sitekey' => $mykey,
    )
);

$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
$out = $json->decode($snoopy->results);

if (!$out['success']) {
    // If request failed for any reason, do not change anything.
    return;
}

if ($out['data']['pub']) {
    // We are published and fine.
    return;
}

// We are unpublished.  Disable prompt.
$vbulletin->db->query_write("
    UPDATE " . TABLE_PREFIX . "setting
    SET value = 0
    WHERE varname = 'forumrunner_redirect_onoff'
");

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/
