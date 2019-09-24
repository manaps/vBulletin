<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

global $bootstrap, $vbulletin;

$datastore_class = (!empty($vbulletin->config['Datastore']['class'])) ? $vbulletin->config['Datastore']['class'] : 'vB_Datastore';

if (!empty($argument) && $datastore_class != 'vB_Datastore' && $datastore_class != 'vB_Datastore_Filecache')
{
	$datastore_default = $vbulletin->datastore->defaultitems;
	$vbulletin->datastore->defaultitems = array();

	foreach ($argument as $key => $item)
	{
		$argument[$key] = str_replace(array("'", '"'), '', $item);
	}

	$vbulletin->datastore->fetch($argument);
	$vbulletin->datastore->defaultitems = $datastore_default;

	vb_optimise::report('Forced ' . count($argument) . ' custom datastore items to use vBulletin Datastore.');
	vb_optimise::stat(1);

	$argument = array();
}