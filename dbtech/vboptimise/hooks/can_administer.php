<?php
foreach($do AS $field)
{
    if ($admin['dbtech_vboptimiseadminperms']  & $vbulletin->bf_misc_dbtech_vboptimiseadminperms[$field])
    {
        $return_value = true;
    }
} 
?>