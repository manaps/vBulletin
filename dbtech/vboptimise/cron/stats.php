<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.0.2 - Licence Number VBS9D7F856
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2010 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

if (!is_object($vbulletin->db))
{
	exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

vb_optimise::updatestats();

log_cron_action('', $nextitem, 1);

if (defined('IN_CONTROL_PANEL'))
{
	echo "<p>$log_text</p>";
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 07:35, Fri Feb 19th 2010
|| # CVS: $RCSfile$ - $Revision: 24070 $
|| ####################################################################
\*======================================================================*/