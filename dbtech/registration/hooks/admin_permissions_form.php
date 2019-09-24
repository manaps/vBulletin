<?php
foreach (convert_bits_to_array($user['dbtech_registrationadminperms'], $vbulletin->bf_misc_dbtech_registrationadminperms) AS $field => $value)
{
	print_yes_no_row($vbphrase["$field"], "dbtech_registrationadminperms[$field]", $value);
}
?>