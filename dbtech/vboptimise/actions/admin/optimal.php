<?php
// ###################### vB Optimise: Optimal Settings Check #######################
print_cp_header($vbphrase['vboptimise_settingscheck']);

$check_settings = array(
	'storecssasfile'	=> array(
		'good'	=> array(1),
		'edit'	=> 'options.php?' . $vbulletin->session->vars['sessionurl'] . 'do=options&amp;dogroup=stylelang',
	),
	'attachfile'		=> array(
		'good'	=> array(1, 2),
		'edit'	=> 'attachment.php?' . $vbulletin->session->vars['sessionurl'] . 'do=storage',
	),
	'usefileavatar'		=> array(
		'good'	=> array(1),
		'edit'	=> 'avatar.php?' . $vbulletin->session->vars['sessionurl'] . 'do=storage',
	),
	'nocacheheaders'	=> array(
		'good'	=> array(0),
		'edit'	=> 'options.php?' . $vbulletin->session->vars['sessionurl'] . 'do=options&amp;dogroup=http',
	),
	'threadviewslive'	=> array(
		'good'	=> array(0),
		'edit'	=> 'options.php?' . $vbulletin->session->vars['sessionurl'] . 'do=options&amp;dogroup=server',
	),
	'attachmentviewslive'	=> array(
		'good'	=> array(0),
		'edit'	=> 'options.php?' . $vbulletin->session->vars['sessionurl'] . 'do=options&amp;dogroup=server',
	),
);

print_table_start();

print_column_style_code(array('width:55%', 'width:45%'));

print_table_header($vbphrase['vboptimise_settingscheck']);

foreach ($check_settings as $setting => $optimal)
{
	if (!isset($vbulletin->options[$setting]))
	{
		continue;
	}

	print_description_row('<span style="float: right;" class="smallfont"><a href="' . $optimal['edit'] . '">[' . $vbphrase['vboptimise_edit_setting'] . ']</a></span>' . $vbphrase['vboptimise_' . $setting], 0, 2, 'optiontitle');

	$result = 'good';

	if (!in_array($vbulletin->options[$setting], $optimal['good']))
	{
		$result = 'bad';
	}

	$result = '<img src="' . $vbulletin->options['bburl'] . '/dbtech/vboptimise/images/vboptimise_' . $result . '.png" style="float: ' . $stylevar['right'] . '; padding-right: 10px; vertical-align: middle;" alt="" />' . (
	$result == 'good' ? $vbphrase['vboptimise_setting_good'] : $vbphrase['vboptimise_setting_bad']);

	print_label_row('<div class="smallfont">' . $vbphrase['vboptimise_' . $setting . '_desc'] . '</div>', $result, '', 'top');
}

print_table_footer();
print_cp_footer();
?>