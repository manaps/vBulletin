<?php
if ($oldsetting['varname'] == 'vbo_online')
{
	$vbulletin->options['vbo_online'] = $settings[$oldsetting['varname']];
}

if ($oldsetting['varname'] == 'vbo_prefix' AND trim($settings[$oldsetting['varname']]) != '')
{
	if (!preg_match("#^[a-z_]+$#i", $settings[$oldsetting['varname']]))
	{
		print_cp_message('vB Optimise: You have specified an invalid data prefix, it may only contain a-z and underscore (_) characters.');
	}
}

if ($oldsetting['varname'] == 'vbo_operator' AND $settings[$oldsetting['varname']] != 'none' AND $vbulletin->options['vbo_online'])
{
	vb_optimise::$cache = null;
	vb_optimise::assign($settings[$oldsetting['varname']]);

	if (!vb_optimise::$cache OR !vb_optimise::$cache->connect())
	{
		print_cp_message('vB Optimise: You have selected a cache method that vB Optimise has detected you cannot use, this may be because the extension required is either not installed or configured correctly on your server. Please contact your system administrator or hosting provider for more information.');
	}

	if ($settings[$oldsetting['varname']] == 'filecache' AND !vb_optimise::$cache->canwrite())
	{
		print_cp_message('vB Optimise: The filecache directory is not writeable. Please give the following directory correct permissions for filecache to work: <em>/dbtech/vboptimise/filecache</em>');
	}
}
?>