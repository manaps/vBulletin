<?php
if (can_administer('canadminthanks'))
{
	print_table_break('', $INNERTABLEWIDTH);
	print_table_header($vbphrase['dbtech_thanks_full']);
	print_yes_no_row($vbphrase['dbtech_thanks_isexcluded'], 		'user[dbtech_thanks_excluded]', 		$user['dbtech_thanks_excluded']);
}
?>