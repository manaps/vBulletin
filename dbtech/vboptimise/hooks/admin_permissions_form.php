<?php
foreach (convert_bits_to_array($user['dbtech_vboptimiseadminperms'], $vbulletin->bf_misc_dbtech_vboptimiseadminperms) AS $field => $value)
{
	print_yes_no_row($vbphrase["$field"], "dbtech_vboptimiseadminperms[$field]", $value);
}
?>