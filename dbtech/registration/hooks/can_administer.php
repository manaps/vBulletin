<?php
foreach($do AS $field)
{
    if ($admin['dbtech_registrationadminperms']  & $vbulletin->bf_misc_dbtech_registrationadminperms["$field"])
    {
        $return_value = true;
    }
} 
?>