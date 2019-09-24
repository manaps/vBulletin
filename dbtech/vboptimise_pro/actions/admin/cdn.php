<?php
// ###################### vB Optimise: CDN Integration #######################
vb_optimise::start_cdn();

if ($_POST['operation'] == '' && $_POST['setup'] != '2')
{
	print_cp_header('vB Optimise: CDN Integration');
}

if (vboptimise_cdn::$settings['status'] == '') // not configured
{
	vboptimise_cdn::display_setup();
}
else if (vboptimise_cdn::$settings['status'] == 'integrated')
{
	if ($_REQUEST['act'] == 'toggle')
	{
		if (vboptimise_cdn::$settings['online'])
		{
			vboptimise_cdn::$settings['online'] = false;
		}
		else if (!vboptimise_cdn::$settings['pendsync'])
		{
			vboptimise_cdn::$settings['online'] = true;
		}

		vboptimise_cdn::save_settings();
	}

	if ($_REQUEST['operation'] == '')
	{
		print_table_start();
		print_table_header('CDN Status', 4);
		print_cells_row(
			array(
				'CDN Provider', ucwords(str_replace('_', ' ', vboptimise_cdn::$settings['provider'])),
				'Styles Assigned', count(vboptimise_cdn::$settings['styles']),
			),
		0, 0, -5, 'top', 1, 1);
		print_cells_row(
			array(
				'CDN Status', vboptimise_cdn::$settings['online'] ? 'Online' : 'Offline',
				'Stylevar Folders Assigned', vb_number_format(intval(vboptimise_cdn::$settings['folders_assigned']) + count(vboptimise_cdn::$settings['customfolders'])),
			),
		0, 0, -5, 'top', 1, 1);
		print_cells_row(
			array(
				'Last Sync', intval(vboptimise_cdn::$settings['lastsync']) > 0 ? vbdate('H:i, D jS Y', vboptimise_cdn::$settings['lastsync']) : 'Never',
				'Items Assigned', vb_number_format(intval(vboptimise_cdn::$settings['items_assigned'])),
			),
		0, 0, -5, 'top', 1, 1);
		print_table_footer();

		if (vboptimise_cdn::$settings['pendsync'] && $_REQUEST['act'] != 'sync')
		{
			print_table_start();
			print_table_header('Reminder');
			print_description_row('You have adjusted your CDN Settings but they will not be applied until you run the Sync operation below.');
			print_table_footer();
		}

		print_table_start();
		print_table_header('CDN Operations');
		print_description_row('<table cellpadding="4" cellspacing="0" border="0" width="100%"><tr align="center">

		<td class="smallfont" width="33%">
			<a href="vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn&amp;act=styles" style="text-decoration: none;">
				<div style="padding-bottom: 4px; height: 28px; width: 32px; background: url(' . $vbulletin->options['bburl'] . '/dbtech/vboptimise_pro/images/cdn_assign.png) no-repeat;"></div>
				<div style="text-decoration: underline;">Manage Assigned Styles</div>
			</a>
		</td>

		<td class="smallfont" width="34%">
			<a href="vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn&amp;act=folders" style="text-decoration: none;">
				<div style="padding-bottom: 4px; height: 28px; width: 32px; background: url(' . $vbulletin->options['bburl'] . '/dbtech/vboptimise_pro/images/cdn_folders.png) no-repeat;"></div>
				<div style="text-decoration: underline;">Managed Assigned Folders</div>
			</a>
		</td>

		<td class="smallfont" width="33%">
			<a href="vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn&amp;act=items" style="text-decoration: none;">
				<div style="padding-bottom: 4px; height: 28px; width: 32px; background: url(' . $vbulletin->options['bburl'] . '/dbtech/vboptimise_pro/images/cdn_items.png) no-repeat;"></div>
				<div style="text-decoration: underline;">Managed Assigned Items</div>
			</a>
		</td>

		</tr></table>');

		print_description_row('<table cellpadding="4" cellspacing="0" border="0" width="100%"><tr align="center">

		<td class="smallfont" width="33%">
			<a href="vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdnsync" style="text-decoration: none;">
				<div style="padding-bottom: 4px; height: 28px; width: 32px; background: url(' . $vbulletin->options['bburl'] . '/dbtech/vboptimise_pro/images/cdn_sync.png) no-repeat;"></div>
				<div style="text-decoration: underline;">Sync with CDN</div>
			</a>
		</td>

		<td class="smallfont" width="34%">
			<a href="vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn&amp;act=toggle" style="text-decoration: none;">
				<div style="padding-bottom: 4px; height: 28px; width: 32px; background: url(' . $vbulletin->options['bburl'] . '/dbtech/vboptimise_pro/images/cdn_switch.png) no-repeat;"></div>
				<div style="text-decoration: underline;">Toggle Online / Offline</div>
			</a>
		</td>

		<td class="smallfont" width="33%">
			<a href="vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn&amp;act=remove" style="text-decoration: none;">
				<div style="padding-bottom: 4px; height: 28px; width: 32px; background: url(' . $vbulletin->options['bburl'] . '/dbtech/vboptimise_pro/images/cdn_remove.png) no-repeat;"></div>
				<div style="text-decoration: underline;">Uninstall CDN Integration</div>
			</a>
		</td>

		</tr></table>');

		print_table_footer();
	}

	switch ($_REQUEST['act'])
	{
		case 'remove':
		{
			print_form_header('vboptimise', 'cdn');
			construct_hidden_code('operation', 'remove');
			print_table_header('Uninstall CDN Integration');
			print_description_row('Are you sure you want to uninstall CDN Integration? By doing so vB Optimise will wipe all local CDN settings and your forum will revert to serving static files from your main web server. Note that vB Optimise will not delete the files located on your CDN.');

			print_submit_row($vbphrase['confirm_action'], false);
		}
		break;

		case 'styles':
		{
			print_form_header('vboptimise', 'cdn');
			construct_hidden_code('operation', 'styles');
			print_table_header('CDN Style Assignment');
			print_description_row('Choose which styles vB Optimise will automatically adjust to apply CDN Integration with images/css. After adjusting styles you will need to run the "Sync with CDN" to commit these changes.');

			$styles = $vbulletin->db->query_read_slave("select title, styleid from " . TABLE_PREFIX . "style where styleid >= 1");
			while ($style = $vbulletin->db->fetch_array($styles))
			{
				print_checkbox_row($style['title'], 'styles[' . $style['styleid'] . ']', @in_array($style['styleid'], vboptimise_cdn::$settings['styles']));
			}

			print_submit_row($vbphrase['save'], false);
		}
		break;

		case 'folders':
		{
			print_form_header('vboptimise', 'cdn');
			construct_hidden_code('operation', 'folders');
			print_table_header('CDN Folder Assignment');
			print_description_row('vB Optimise will automatically assign folders like /clientscript and /images and more based on static content used by Styles and other default vBulletin images, but you may have images or css/javascript in other folders for your own purposes or from modifications. You can assign custom folders here so vB Optimise will automatically apply them to the CDN.');

			print_textarea_row('Custom CDN Folders<dfn>Assign 1 folder per line relative to your vBulletin root directory. For example, to allocate vBShout javascript to the CDN enter:<pre>dbtech/vbshout/clientscript</pre></dfn>', 'folders', @implode("\n", vboptimise_cdn::$settings['customfolders']), 10, 60);

			print_submit_row($vbphrase['save'], false);
		}
		break;

		case 'items':
		{
			print_form_header('vboptimise', 'cdn');
			construct_hidden_code('operation', 'items');
			print_table_header('CDN Item Assignment');
			print_description_row('vB Optimise will automatically assign folders like /clientscript and /images and more based on static content used by Styles and other default vBulletin images, but you may have images or css/javascript in locations for your own purposes or from modifications. You can assign custom items here so vB Optimise will automatically apply them to the CDN.');

			print_textarea_row('Custom CDN Items<dfn>Assign 1 item per line relative to your vBulletin root directory. For example:<pre>images/misc/vbulletin4_logo.png</pre></dfn>', 'items', @implode("\n", vboptimise_cdn::$settings['customitems']), 10, 60);

			print_submit_row($vbphrase['save'], false);
		}
		break;
	}

	$_POST = $_REQUEST;

	switch ($_POST['operation'])
	{
		case 'remove':
		{
			$vbulletin->db->query_write("truncate table " . TABLE_PREFIX . "vboptimise_cdn");

			vboptimise_cdn::$settings = array();
			vboptimise_cdn::save_settings();
			exec_header_redirect('vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn');
		}
		break;

		case 'folders':
		{
			$verified = array();
			$folders = explode("\n", $_POST['folders']);

			if (is_array($folders))
			{
				foreach ($folders as $folder)
				{
					$folder = preg_replace("#[/]+#", '/', str_replace('\\', '/', trim($folder)));

					if ($folder == '.' || $folder == '..' || $folder == '')
					{
						continue;
					}

					if (is_dir(DIR . '/' . $folder))
					{
						if (substr($folder, -1) == '/')
						{
							$folder = substr($folder, 0, -1);
						}

						if (substr($folder, 0, 1) == '/')
						{
							$folder = substr($folder, 1, strlen($folder));
						}

						$verified[] = $folder;
					}
				}
			}

			vboptimise_cdn::$settings['customfolders'] = array_unique($verified);
			vboptimise_cdn::$settings['pendsync'] = count(vboptimise_cdn::$settings['styles']) > 0;
			vboptimise_cdn::save_settings();
			exec_header_redirect('vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn');
		}
		break;

		case 'items':
		{
			$verified = array();
			$items = explode("\n", $_POST['items']);

			if (is_array($items))
			{
				foreach ($items as $item)
				{
					$item = preg_replace("#[/]+#", '/', str_replace('\\', '/', trim($item)));

					if ($item == '.' || $item == '..' || $item == '')
					{
						continue;
					}

					if (is_file(DIR . '/' . $item))
					{
						if (substr($item, -1) == '/')
						{
							$item = substr($item, 0, -1);
						}

						$verified[] = $item;
					}
				}
			}

			vboptimise_cdn::$settings['customitems'] = array_unique($verified);
			vboptimise_cdn::$settings['pendsync'] = count(vboptimise_cdn::$settings['styles']) > 0;
			vboptimise_cdn::save_settings();
			exec_header_redirect('vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn');
		}
		break;

		case 'styles':
		{
			$styles = $vbulletin->db->query_read_slave("select title, styleid from " . TABLE_PREFIX . "style where styleid >= 1");
			while ($style = $vbulletin->db->fetch_array($styles))
			{
				$valid[$style['styleid']] = $style;
			}

			$assign = @array_keys($_POST['styles']);

			if (count($assign) < 1)
			{
				$assign = array();
			}

			foreach ($assign as $key => $value)
			{
				if (!isset($valid[$value]))
				{
					unset($assign[$key]);
				}
			}

			vboptimise_cdn::$settings['styles'] = $assign;
			vboptimise_cdn::$settings['pendsync'] = count($assign) > 0;
			vboptimise_cdn::save_settings();
			exec_header_redirect('vboptimise.php?' . $vbulletin->session->vars['sessionurl'] . 'do=cdn');
		}
		break;
	}
}

print_cp_footer();
?>