<?php
// ###################### vB Optimise: Manual Flush #######################

print_cp_header($vbphrase['vboptimise_manualflush']);

if ($vbulletin->options['vbo_online'])
{
	vb_optimise::$cache->flush(false);

	$d = dir(DIR . '/dbtech/vboptimise/templatecache');
	while (false !== ($file = $d->read()))
	{
		if ($file == '.' OR $file == '..' OR $file == 'index.html')
		{
			// Skip this
			continue;
		}

		// We can't determine individual templates, so nuke them all
		@unlink(DIR . '/dbtech/vboptimise/templatecache/' . $file);
	}
	$d->close();
}

print_table_start();
print_table_header($vbphrase['vboptimise_manualflush']);
print_description_row($vbphrase['vboptimise_flushed']);
print_table_footer();
print_cp_footer();
?>