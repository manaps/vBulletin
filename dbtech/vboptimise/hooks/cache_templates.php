<?php
/*DBTECH_PRO_START*/
if ($vbulletin->options['vbo_wysiwyg_fix'] AND is_array($cache) AND in_array('editor_clientscript', $cache))
{
	$cache[] = 'wysiwyg_cdn_css';
	define('vb_cdn_wsyiwyg', true);
}
/*DBTECH_PRO_END*/

if (THIS_SCRIPT == 'index')
{
	$cache[] = 'forumhome_vbo';
}

// Override this setting if we can't write to the template cache
$vbulletin->options['vbo_cache_templates_filesystem'] = !is_writable(DIR . '/dbtech/vboptimise/templatecache') ? 0 : $vbulletin->options['vbo_cache_templates_filesystem'];

if (!$vbulletin->options['vbo_cache_templates_filesystem'])
{
	if (intval($vbulletin->versionnumber) == 3)
	{
		// Ensure this works
		$globaltemplates = array_merge((array)$cache, (array)$globaltemplates);

		// vB3 cache
		vb_optimise::cache('templates', $globaltemplates, $style['templatelist']);
	}
	else
	{
		// vB4 cache
		vb_optimise::cache('templates', $cache, $template_ids);
	}
}
?>