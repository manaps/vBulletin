<?php
global $vbulletin;
if ($vbulletin->options['vbo_cache_templates_filesystem'])
{
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
?>