<?php
$vbulletin->input->clean_gpc('p', 'dbtech_registrationadminperms', TYPE_ARRAY_INT);
foreach ((array)$vbulletin->GPC['dbtech_registrationadminperms'] AS $field => $value)
{
	$admindm->set_bitfield('dbtech_registrationadminperms', $field, $value);
}
?>