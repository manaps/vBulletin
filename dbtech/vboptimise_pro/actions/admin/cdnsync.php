<?php
@set_time_limit(0);
vb_optimise::start_cdn();

if (!(vboptimise_cdn::$settings['status'] == 'integrated' && count(vboptimise_cdn::$settings['styles']) > 0))
{
	print_cp_header('vB Optimise: CDN Integration');
	vboptimise_cdn::setup_error('Your CDN is either not integrated or you need to assign atleast one style.');
}

$dodo = 'cdnsync';
$do = false;
$upload = 25;

print_cp_header('vB Optimise: CDN Sync');

vbflush();

vboptimise_cdn::sync_report('vB Optimise is executing a sync operation with your CDN Provider.<br />Please do not navigate away until this process has completed.', 'h4');

switch ($_REQUEST['act'])
{
	case 'finish':
	{
		vboptimise_cdn::$settings['pendsync'] = false;
		vboptimise_cdn::$settings['online'] = true;
		vboptimise_cdn::$settings['lastsync'] = TIMENOW;
		unset(vboptimise_cdn::$settings['ignore'], vboptimise_cdn::$settings['pendingitems']['stylevar_items']); // not required, can be big too
		vboptimise_cdn::save_settings();

		vboptimise_cdn::sync_report('Sync operation has completed!');

		$do = 'return';
		$dodo = 'cdn';
	}
	break;

	case 'sync':
	{
		vboptimise_cdn::sync_report('Uploading items ' . $_REQUEST['at'] . '-' . ($_REQUEST['at'] + $upload) . ' of ' . $_REQUEST['total'] . ' to CDN...');
		$result = vboptimise_cdn::sync_items(intval($_REQUEST['at']), $upload);
		vboptimise_cdn::sync_report('Upload batch completed.');

		$next = $_REQUEST['at'] + $upload;
		$do = 'sync&at=' . $next . '&total=' . $_REQUEST['total'];

		if ($next >= intval($_REQUEST['total']))
		{
			$do = 'finish';
		}
	}
	break;

	case 'seek_trim':
	{
		vboptimise_cdn::sync_report('Trimming items that only require a sync...');
		$found = vboptimise_cdn::seek_items(1);
		vboptimise_cdn::sync_report('Trimmed Items To: ' . $found . ' items.');

		$do = 'sync&at=0&total=' . $found;
	}
	break;

	default:
	{
		// Seek items to sync
		vboptimise_cdn::sync_report('Finding items to sync...');
		$found = vboptimise_cdn::seek_items(0);
		vboptimise_cdn::sync_report('Found: ' . $found . ' items.');

		$do = 'seek_trim';
	}
	break;
}

vbflush();

if ($do)
{
	vboptimise_cdn::sync_report('Continuing operation...', 'em');
	echo '<script type="text/javascript">
<!--
setTimeout(function()
{
window.location.href = "vboptimise.php?" + SESSIONHASH + "do=' . $dodo . '&act=' . $do . '";
}, 500);
-->
</script>';
}

vbflush();

print_cp_footer();
?>