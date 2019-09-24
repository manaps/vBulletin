<?php
if (
	$vbulletin->options['vbo_online']
	AND $vbulletin->options['vbo_cache_plugins_filesystem']
	AND is_array($vbulletin->pluginlist)
	AND is_dir($vbulletin->options['vbo_cache_plugins_filesystem_path'])
	AND is_writeable($vbulletin->options['vbo_cache_plugins_filesystem_path'])
)
{
	foreach ($vbulletin->pluginlist as $hook => $code)
	{
		if ($hook == 'init_startup')
		{
			// No point
			continue;
		}

		// Shorthand
		$file = $vbulletin->options['vbo_cache_plugins_filesystem_path'] . '/' . $hook . '.php';

		if (file_exists($file) AND !is_writable($file))
		{
			// File most likely won't be written to
			continue;
		}

		// Shorthand
		$code = "<?php\nif (!defined('VB_AREA')) die('Access denied.');\n{$code}?>";

		if (file_exists($file) AND file_get_contents($file) === $code)
		{
			// File was identical
			continue;
		}

		$res = file_put_contents($file, $code);
		if ($res === false)
		{
			// Couldn't write the contents after all
			continue;
		}

		// Finally set hook
		$vbulletin->pluginlist[$hook] = "require($file);";
	}
}
?>