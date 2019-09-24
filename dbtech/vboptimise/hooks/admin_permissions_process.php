<?php
$vbulletin->input->clean_gpc('p', 'dbtech_vboptimiseadminperms', TYPE_ARRAY_INT);
foreach ((array)$vbulletin->GPC['dbtech_vboptimiseadminperms'] AS $field => $value)
{
	$admindm->set_bitfield('dbtech_vboptimiseadminperms', $field, $value);
}
?>