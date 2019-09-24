<?php
if ($vbulletin->options['vbo_stat_pass'] != '' && $_REQUEST['pass'] != $vbulletin->options['vbo_stat_pass'])
{
	print_no_permission();
}

require_once(DIR . '/includes/class_xml.php');

// #######################################################################
vb_optimise::updatestats();

$fetch_data = $vbulletin->db->query_read_slave("select dateline, queries from " . TABLE_PREFIX . "vboptimise order by statid desc limit 30");
$response = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
$response->add_group('vboptimise');
$add = array();

while ($data = $vbulletin->db->fetch_array($fetch_data))
{
	$add[] = $data;
}

$add = array_reverse($add);

foreach ($add as $data)
{
	$response->add_tag('data', '', array('Month' => $data['dateline'], 'Amount' => $data['queries']));
}

$response->close_group();
$response->print_xml();
?>