<?php
require_once(DIR . '/dbtech/vboptimise/includes/class_vboptimise.php');
/*DBTECH_PRO_START*/
require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_overload.php');
require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_cdn.php');
/*DBTECH_PRO_END*/

$extracache = array();
if ($vbulletin->options['vbo_showresource'])
{
	$extracache[] = 'vbo_resource_savings';
}

$extrafetch = array();
foreach ($extracache as $varname)
{
	// datastore_fetch uses a different syntax
	$extrafetch[] = "'$varname'";
}

// Now merge the prepared entries
$datastore_fetch = array_merge($datastore_fetch, $extrafetch);

if (isset($this) AND is_object($this))
{
	// Forum inits within a class
	$this->datastore_entries = array_merge((array)$this->datastore_entries, $extracache);
}
else
{
	// AdminCP / ModCP inits normally
	$specialtemplates = array_merge((array)$specialtemplates, $extracache);
}

vb_optimise::$prefix = $vbulletin->options['vbo_prefix'];
vb_optimise::assign($vbulletin->options['vbo_operator']);
vb_optimise::cache('datastore', $datastore_fetch);

if (VB_AREA == 'AdminCP')
{
	vb_optimise::update();
}
?>