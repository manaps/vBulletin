<?php
if ($vbulletin->options['vbo_online'] AND $vbulletin->options['vbo_showresource'])
{
	if (!class_exists('vB_Template'))
	{
		require_once(DIR . '/dbtech/vboptimise/includes/class_vboptimise_template.php');
	}

	$num = (isset($vbulletin->vbo_resource_savings) ? vb_number_format($vbulletin->vbo_resource_savings) : 0);
	$templater = vB_Template::create('forumhome_vbo');
		$templater->register('num', $num); 
	$template_hook['forumhome_wgo_stats'] .= $templater->render();
}
?>