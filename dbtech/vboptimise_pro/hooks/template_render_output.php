<?php
if ($vbulletin->options['vbo_cache_templates_filesystem'])
{
	// use legacy postbit if necessary
	$tmpl = ($vbulletin->options['legacypostbit'] AND $this->template == 'postbit') ? 'postbit_legacy' : $this->template;

	if (file_exists(DIR . '/dbtech/vboptimise/templatecache/' . $tmpl . '-' . STYLEID . '.php'))
	{
		// We have our template
		$actioned = true;

		if (!isset(self::$template_usage[$this->template]))
		{
			self::$template_usage[$this->template] = 1;
		}
		else
		{
			self::$template_usage[$this->template]++;
		}

		// Require our template file
		require(DIR . '/dbtech/vboptimise/templatecache/' . $tmpl . '-' . STYLEID . '.php');

		// Trick the eval into not degrading performance
		$template_code = '$final_rendered = $final_rendered;';
	}
}
?>