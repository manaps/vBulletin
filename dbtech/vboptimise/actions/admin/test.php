<?php
// ###################### vB Optimise: Test Setup #######################
if (!$vbulletin->options['vbo_online'])
{
	print_cp_header($vbphrase['vboptimise_testsetup']);
	print_cp_message($vbphrase['vboptimise_testsetup_offline']);
}

if ($_REQUEST['act'] == '')
{
	print_cp_header($vbphrase['vboptimise_testsetup']);

	echo '<script type="text/javascript" src="' . $vbulletin->options['bburl'] . '/dbtech/vboptimise/clientscript/vboptimise.js?v=1"></script>';
	print_table_start();
	print_table_header($vbphrase['vboptimise_testsetup']);

	print_description_row('<span class="smallfont"><strong>' . $vbphrase['vboptimise_runtest'] . ':</strong> ' . $vbphrase['vboptimise_connectionto'] . ' ' . $vbulletin->options['vbo_operator'] . '</span>', 0, 2, 'optiontitle');
	print_description_row('<div id="vbo_connection" style="padding: 8px;"><img src="' . $vbulletin->options['bburl'] . '/dbtech/vboptimise/images/progress.gif" alt="" /></div>');

	print_description_row('<span class="smallfont"><strong>' . $vbphrase['vboptimise_runtest'] . ':</strong> ' . $vbphrase['vboptimise_storecache'] . '</span>', 0, 2, 'optiontitle');
	print_description_row('<div id="vbo_store" style="padding: 8px;"><img src="' . $vbulletin->options['bburl'] . '/dbtech/vboptimise/images/progress.gif" alt="" /></div>');

	print_description_row('<span class="smallfont"><strong>' . $vbphrase['vboptimise_runtest'] . ':</strong> ' . $vbphrase['vboptimise_fetchcache'] . '</span>', 0, 2, 'optiontitle');
	print_description_row('<div id="vbo_fetch" style="padding: 8px;"><img src="' . $vbulletin->options['bburl'] . '/dbtech/vboptimise/images/progress.gif" alt="" /></div>');

	print_description_row('<span class="smallfont"><strong>' . $vbphrase['vboptimise_runtest'] . ':</strong> ' . $vbphrase['vboptimise_flushcache'] . '</span>', 0, 2, 'optiontitle');
	print_description_row('<div id="vbo_flush" style="padding: 8px;"><img src="' . $vbulletin->options['bburl'] . '/dbtech/vboptimise/images/progress.gif" alt="" /></div>');

	echo '<script type="text/javascript">
<!--
if (typeof ADMINHASH == "undefined")
{
ADMINHASH = "' . ADMINHASH . '";
};

vBOptimise.tests.push("connection");
vBOptimise.tests.push("store");
vBOptimise.tests.push("fetch");
vBOptimise.tests.push("flush");
setTimeout("vBOptimise.run_test();", 1500);
-->
</script>';

	print_table_footer();
	print_cp_footer();
}
else
{
	require_once(DIR . '/includes/class_xml.php');

	switch ($_REQUEST['act'])
	{
		case 'connection':
		{
			$result = vb_optimise::$cache->connect();
		}
		break;

		case 'store':
		{
			vb_optimise::$cache->set('vbo_systest_temp', 'vB Optimise System Test');

			if (vb_optimise::$cache->get('vbo_systest_temp') != 'vB Optimise System Test')
			{
				$result = false;
				$message = $vbphrase[vb_optimise::$cache->fetchType()];
			}
			else
			{
				$result = true;
			}
		}
		break;

		case 'fetch':
		{
			// A repeat of the above, but without being set prior. This tests the opcachers ability to maintain cache between sessions. This is a common issue on fastcgi setups

			if (vb_optimise::$cache->get('vbo_systest_temp') != 'vB Optimise System Test')
			{
				$result = false;
				$message = $vbphrase[vb_optimise::$cache->fetchType()];
			}
			else
			{
				$result = true;
			}
		}
		break;

		case 'flush':
		{
			vb_optimise::$cache->flush();

			if (vb_optimise::$cache->get('vbo_systest_temp') == 'vB Optimise System Test')
			{
				$result = false;
				$message = $vbphrase['vboptimise_flushfail'];
			}
			else
			{
				$result = true;
			}
		}
		break;
	}

	$response = new vB_AJAX_XML_Builder($vbulletin, 'text/xml');
	$response->add_group('test');
	$response->add_tag('result', $result ? 'OK' : 'bad');

	if (trim($message))
	{
		$response->add_tag('message', $message);
	}

	$response->close_group();
	$response->print_xml();
}
?>