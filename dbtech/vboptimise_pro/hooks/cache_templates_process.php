<?php
if ($vbulletin->options['vbo_cache_templates_filesystem'] AND defined('STYLEID'))
{
	$_templates = array_unique($templates);
	if (empty($templateassoc))
	{
		$templateassoc = unserialize($templateidlist);
	}

	if ($vbulletin->options['legacypostbit'] AND in_array('postbit', $_templates))
	{
		$templateassoc['postbit'] = $templateassoc['postbit_legacy'];
	}

	$_templateids = array();
	foreach ($_templates AS $template)
	{
		if ($vbulletin->options['legacypostbit'] AND $template == 'postbit')
		{
			$template = 'postbit_legacy';
		}

		if (file_exists(DIR . '/dbtech/vboptimise/templatecache/' . $template . '-' . STYLEID . '.php'))
		{
			$vbulletin->templatecache[$template] = file_get_contents(DIR . '/dbtech/vboptimise/templatecache/' . $template . '-' . STYLEID . '.php');

			if ($vbulletin->options['legacypostbit'] AND $template == 'postbit_legacy')
			{
				$vbulletin->templatecache['postbit'] =& $vbulletin->templatecache[$template];
			}			
		}
		else
		{
			// We need to pull this from the cache
			$_templateids[] = intval($templateassoc[$template]);
		}
	}

	if (!empty($_templateids))
	{
		// run query
		$temps = $vbulletin->db->query_read_slave("
			SELECT title, template
			FROM " . TABLE_PREFIX . "template
			WHERE templateid IN (" . implode(',', $_templateids) . ")
		");

		// cache templates
		while ($temp = $vbulletin->db->fetch_array($temps))
		{
			if (!empty($vbulletin->templatecache[$temp['title']]))
			{
				// Already cached this template
				continue;
			}

			if (strpos($temp['template'], '$final_rendered') !== false)
			{
				$template_code = '<?php ' . $temp['template'] . ' ?>';
			}
			else
			{
				$template_code = '<?php $final_rendered = "' . $temp['template'] . '"; ?>';
			}

			// Write to the file
			file_put_contents(DIR . '/dbtech/vboptimise/templatecache/' . $temp['title'] . '-' . STYLEID . '.php', $template_code);

			// Store template cache
			$vbulletin->templatecache[$temp['title']] = $template_code;
		}
		$vbulletin->db->free_result($temps);
	}

	// Assume we have all templates at this point
	$actioned = true;
}
?>