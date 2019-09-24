<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class vboptimise_cdn
{
	public static $cdn = false;
	public static $settings = array();
	protected static $map = array();

	public static function parse_config($config = '')
	{
		self::$settings = trim($config) == '' ? array() : unserialize($config);
	}

	public static function display_setup()
	{
		global $vbphrase;

		$providers = self::fetch_providers();

		switch ($_POST['setup'])
		{
			case '1':
			{
				self::display_setup_provider($providers, $_POST['provider']);
			}
			break;

			case '2':
			{
				self::save_new_provider($providers, $_POST['provider'], $_POST['settings']);
			}
			break;

			default:
			{
				print_form_header('vboptimise', 'cdn');
				construct_hidden_code('setup', '1');
				print_table_header('CDN Integration');
				print_description_row('CDN Integration has not yet been configured. vB Optimise can work with some CDN providers to automatically integrate into your vBulletin forum. To begin select your CDN provider from below.');
				print_select_row('Select your CDN Provider:', 'provider', $providers);
				print_submit_row($vbphrase['continue'], false);
			}
			break;
		}
	}

	public static function save_settings()
	{
		global $vbulletin;

		require_once(DIR . '/includes/adminfunctions_options.php');

		if (self::$settings['pendsync'])
		{
			self::$settings['online'] = false;
		}

		$vbulletin->db->query_write("
			UPDATE " . TABLE_PREFIX . "setting
			SET value = '" . $vbulletin->db->escape_string(serialize(self::$settings)) . "'
			WHERE varname = 'vbo_cdn_settings'
		");

		build_options();
	}

	public static function sync_items($at = 0, $do = 10)
	{
		if ($at < 0)
		{
			$at = 0;
		}

		$items = array();
		$undo = array();

		if (!is_array(self::$settings['ignore']))
		{
			self::$settings['ignore'] = array();
		}

		foreach (self::$settings['pendingitems'] as $type => $pendingitems)
		{
			if ($type == 'stylevar_folders')
			{
				continue;
			}

			foreach ($pendingitems as $item)
			{
				if (!in_array($item, self::$settings['ignore']))
				{
					$items[] = $item;
				}
			}
		}

		self::load_provider(self::$settings['provider']);

		for ($i = $at; $i < $at + $do; $i++)
		{
			if ($items[$i] != '' && file_exists(DIR . '/' . $items[$i]))
			{
				$handled = self::handle_css_file($items[$i]);
				self::$cdn->assign_upload($items[$i]);

				if ($handled)
				{
					if (function_exists('vbflush'))
					{
						self::sync_report('CDN-ised CSS File: ' . $items[$i]);
						vbflush();
					}

					$undo[] = $items[$i];
				}
			}
		}

		self::$cdn->sync();

		if (count($undo) > 0)
		{
			foreach ($undo as $revert)
			{
				self::handle_css_file_completed($revert);
			}
		}

		// Fetch the container URL and save it
		if (self::$settings['cdnurl'] == '' || $at == 0)
		{
			self::$settings['cdnurl'] = self::$cdn->get_url();
			self::$settings['folders_assigned'] = count(self::$settings['pendingitems']['stylevar_folders']);
			self::save_settings();
		}

		return $at + $do > count($items) ? count($items) : $at + $do;
	}

	private static function handle_css_file_contents(&$contents)
	{
		global $vbulletin;
		static $cdn_url;

		if (trim($cdn_url) == '')
		{
			$cdn_url = self::$cdn->get_url();
		}

		$replace = array_merge((array)self::$settings['pendingitems']['stylevar_css_images'], (array)self::$settings['pendingitems']['stylevar_items']);

		if (count($replace) < 0)
		{
			return false;
		}

		$current = array();

		if (intval($vbulletin->versionnumber) == 3)
		{
			$contents = preg_replace("#\.\.\/\.\.\/#", '', $contents);
		}

		// Break the expression, it can grow too large
		foreach ($replace as $item)
		{
			$current[] = $item;

			if (count($current) >= 30)
			{
				$contents = preg_replace("#(" . preg_quote($vbulletin->options['bburl']) . ")?(/)?(" . str_replace('IMPLODEKEY', '|', preg_quote(implode('IMPLODEKEY', $current))) . ")#ie", "vboptimise_cdn::apply_cdn_rewrite_css('\\0');", $contents);
				$current = array();
			}
		}

		if (count($current) > 0)
		{
			$contents = preg_replace("#(" . preg_quote($vbulletin->options['bburl']) . ")?(/)?(" . str_replace('IMPLODEKEY', '|', preg_quote(implode('IMPLODEKEY', $current))) . ")#ie", "vboptimise_cdn::apply_cdn_rewrite_css('\\0');", $contents);
		}
	}

	private static function apply_cdn_rewrite_css($url)
	{
		global $vbulletin;

		$new = str_replace(array($vbulletin->options['bburl'] . '/', '\"'), '', $url);

		if (@in_array($new, self::$settings['pendingitems']['stylevar_css_images']) || @in_array($new, self::$settings['pendingitems']['stylevar_items']))
		{
			return self::$settings['cdnurl'] . '/' . str_replace(array($vbulletin->options['bburl'] . '/', '\"'), '', $url);
		}
		else
		{
			return $url;
		}
	}

	private static function handle_css_file($file = '')
	{
		global $vbulletin;

		if ($vbulletin->options['storecssasfile'] && ((is_array(self::$settings['pendingitems']['stylevar_css_images']) && count(self::$settings['pendingitems']['stylevar_css_images']) > 0) || (is_array(self::$settings['pendingitems']['stylevar_items']) && count(self::$settings['pendingitems']['stylevar_items']) > 0)))
		{
			if (preg_match("#^clientscript/vbulletin_css/(.*)\.css$#", $file))
			{
				if ($source = @fopen($file, 'r'))
				{
					$contents = @fread($source, filesize($file));
					@fclose($source);

					if (trim($contents) != '' && ($backup = @fopen($file . '.bak', 'wb')))
					{
						@fputs($backup, $contents, ((strlen($contents) > 0)? strlen($contents) : 1));
						@fclose($backup);

						self::handle_css_file_contents($contents);

						$cdnised = @fopen($file, 'wb');
						@fputs($cdnised, $contents, ((strlen($contents) > 0)? strlen($contents) : 1));
						@fclose($cdnised);

						return true;
					}
				}
			}
		}

		return false;
	}

	private static function handle_css_file_completed($file = '')
	{
		global $vbulletin;

		if ($source = @fopen($file . '.bak', 'r'))
		{
			$contents = @fread($source, filesize($file));
			@fclose($source);

			if (!self::$cdn->keep_css_cdn) // otherwise it's origin, needs to remain this way
			{
				if (trim($contents) != '' && ($orig = @fopen($file, 'wb')))
				{
					@fputs($orig, $contents, ((strlen($contents) > 0)? strlen($contents) : 1));
	
					@fclose($orig);
	
					@unlink($file . '.bak');
				}
			}
			else
			{
				@unlink($file . '.bak');
			}
		}

		return false;
	}

	public static function sync_report($msg = '', $tag = 'pre')
	{
		echo "<$tag>$msg</$tag>";
		vbflush();
	}

	public static function seek_items($part = 0)
	{
		global $vbulletin;

		if ($part == 0)
		{
			$version = intval($vbulletin->versionnumber);
	
			// Fetch common items
			self::common_items();
	
			// Fetch items related to styles
			if ($version == 4)
			{
				self::style_items_vb4(intval($styleid));
			}
			else
			{
				self::style_items_vb3(intval($styleid));
			}

			// Fetch custom items from assigned folders/items
			self::custom_items();
	
			self::$settings['ignore'] = array();
			self::$settings['pendingitems'] = self::found_items();
		}

		$count = 0;
		$removed = 0;

		if ($part == 1)
		{
			self::load_provider(self::$settings['provider']);
		}

		foreach (self::$settings['pendingitems'] as $type => $items)
		{
			if ($type == 'stylevar_folders')
			{
				continue;
			}

			foreach ($items as $key => $item)
			{
				if ($part == 1)
				{
					if (!self::$cdn->changed_since_lastsync(DIR . '/' . $item) && self::$cdn->file_on_cdn($item))
					{
						$removed++;
						self::$settings['ignore'][] = $item; // already sync'd
					}
				}
			}

			$count += count($items);
		}

		// Save items for pending sync operation
		self::$settings['items_assigned'] = $count;
		self::save_settings();

		return $count - $removed;
	}

	private static function get_cdn_styles()
	{
		global $vbulletin;
		static $styles = array();

		if (count($styles) < 1)
		{
			$fetch_styles = $vbulletin->db->query_read_slave("select * from " . TABLE_PREFIX . "style where styleid in (" . implode(',', self::$settings['styles']) . ")");
			while ($style = $vbulletin->db->fetch_array($fetch_styles))
			{
				$styles[$style['styleid']] = $style;
			}
		}

		return $styles;
	}

	private static function style_items_vb4()
	{
		global $vbulletin;

		$folders = array();
		$styles = self::get_cdn_styles();

		foreach ($styles as $styleid => $style)
		{
			$stylevars = unserialize($style['newstylevars']);

			// Locate imgdir type stylevars & stylevars with specific references to images
			foreach ($stylevars as $var => $data)
			{
				if ($data['datatype'] == 'imagedir' && trim($data['imagedir']) != '' && is_dir(DIR . '/' . $data['imagedir']))
				{
					if (!preg_match("#^\.?/#", $data['imagedir'])) // make sure the folder is relative, otherwise we won't bother
					{
						$folders[] = $data['imagedir'];
					}
				}
				else if (preg_match("#url\((.*[^\)])\)#i", $data['image'], $matches))
				{
					$image = trim($matches[1]);

					if (!preg_match("#^\.?/#", $image) && file_exists(DIR . '/' . $image))
					{
						self::found_items('stylevar_css_images', $image);
					}
				}
			}
		
		}

		if (count($folders) > 0)
		{
			$folders = array_unique($folders); // Very unlikely multiple styles won't use common folders

			foreach ($folders as $folder)
			{
				$items = self::fetch_map(DIR . '/' . $folder);

				if (count($items['map']) > 0)
				{
					self::found_items('stylevar_folders', $folder);
					foreach ($items['map'] as $location => $filename)
					{
						self::found_items('stylevar_items', str_replace(DIR . '/', '', $location));
					}
				}
			}
		}
	}

	private static function fetch_vb3_css($css = '')
	{
		if (preg_match("#import url\(\"(.*)\"\)#", $css, $matches))
		{
			if (is_file(DIR . '/' . $matches[1]))
			{
				if ($css_file = @fopen(DIR . '/' . $matches[1], 'r'))
				{
					$css = preg_replace("#\.\./\.\./#", '', @fread($css_file, filesize(DIR . '/' . $matches[1])));
					@fclose($css_file);
				}
			}
		}

		return $css;
	}

	private static function style_items_vb3()
	{
		global $vbulletin;

		$folders = array();
		$styles = self::get_cdn_styles();

		foreach ($styles as $styleid => $style)
		{
			$stylevars = unserialize($style['stylevars']);
			foreach ($stylevars as $var => $value)
			{
				if (preg_match("#^imgdir_(.*)$#i", $var))
				{
					$folders[] = $value;
				}
			}

			$style['css'] = self::fetch_vb3_css($style['css']);

			while (preg_match("#url\((.*[^\)])\)#i", $style['css'], $matches))
			{
				self::found_items('stylevar_css_images', $matches[1]);
				$folder = trim(str_replace('/' . array_pop(explode('/', str_replace('\\', '/', $matches[1]))), '', $matches[1]));

				if ($folder != '')
				{
					$folders[] = $folder;
				}

				$style['css'] = str_replace($matches[0], '', $style['css']);
			}		
		}

		if (count($folders) > 0)
		{
			$folders = array_unique($folders); // Very unlikely multiple styles won't use common folders

			foreach ($folders as $folder)
			{
				$items = self::fetch_map(DIR . '/' . $folder);

				if (count($items['map']) > 0)
				{
					self::found_items('stylevar_folders', $folder);
					foreach ($items['map'] as $location => $filename)
					{
						self::found_items('stylevar_items', str_replace(DIR . '/', '', $location));
					}
				}
			}
		}
	}

	private static function found_items($type = 'fetch', $items = array())
	{
		static $found = array();

		if ($type == 'fetch')
		{
			return $found;
		}

		if (!is_array($items))
		{
			$items = array($items);
		}

		foreach ($items as $key => $item)
		{
			if (preg_match('/^https?/i', $item))
			{
				unset($items[$key]);
			}
		}

		if (empty($items) || count($items) < 1)
		{
			return false;
		}

		if (!is_array($found[$type]))
		{
			$found[$type] = $items;
		}
		else
		{
			$found[$type] = array_merge($items, $found[$type]);
		}

		$found[$type] = array_unique($found[$type]);
	}

	private static function custom_items()
	{
		global $vbulletin;

		if (count(self::$settings['customfolders']) > 0)
		{
			foreach (self::$settings['customfolders'] as $folder)
			{
				if (is_dir(DIR . '/' . $folder))
				{
					$items = self::fetch_map(DIR . '/' . $folder);

					if (count($items['map']) > 0)
					{
						foreach ($items['map'] as $location => $filename)
						{
							if (!preg_match("#\.(php)$#i", $filename))
							{
								self::found_items('custom', str_replace(DIR . '/', '', $location));
							}
						}
					}
				}
			}
		}

		if (count(self::$settings['customitems']) > 0)
		{
			foreach (self::$settings['customitems'] as $item)
			{
				if (is_file(DIR . '/' . $item))
				{
					self::found_items('custom', $item);
				}
			}
		}
	}

	private static function common_items()
	{
		global $vbulletin;

		// Default thread icon
		if ($vbulletin->options['showdeficon'] != '')
		{
			self::found_items('showdeficon', $vbulletin->options['showdeficon']);
		}

		// Post icons
		$posticons = $vbulletin->db->query_read_slave("select iconpath from " . TABLE_PREFIX . "icon");
		while ($icon = $vbulletin->db->fetch_array($posticons))
		{
			self::found_items('posticon', $icon['iconpath']);
		}

		// Clientscript directory
		$clientscript = self::fetch_map(DIR . '/clientscript');

		if (count($clientscript['map']) > 0)
		{
			foreach ($clientscript['map'] as $location => $filename)
			{
				if (preg_match("#style([0-9]+)(l|r)#i", $location, $matches) && preg_match("#\.css#", $filename))
				{
					$styleid = intval($matches[1]);

					if (!in_array($styleid, self::$settings['styles'])) // ignore styles not assigned or styles no longer used (vBulletin doesn't delete them, only affects vB4)
					{
						continue;
					}
				}

				if (!preg_match("#\.(php)$#i", $filename))
				{
					self::found_items('clientscript', str_replace(DIR . '/', '', $location));
				}
			}
		}
	}

	public static function apply_cdn()
	{
		global $vbulletin;

		// Apply the CDN to the default icon
		if (self::$settings['pendingitems']['showdeficon'][0] != '')
		{
			$vbulletin->options['showdeficon'] = self::$settings['cdnurl'] . '/' . $vbulletin->options['showdeficon'];
		}

		// Apply the CDN to Post Icons
		if (is_array($vbulletin->iconcache) && is_array(self::$settings['pendingitems']['posticon']))
		{
			foreach ($vbulletin->iconcache as $id => $data)
			{
				if (in_array($data['iconpath'], self::$settings['pendingitems']['posticon']))
				{
					$vbulletin->iconcache[$id]['iconpath'] = self::$settings['cdnurl'] . '/' . $data['iconpath'];
				}
			}

			vb_optimise::report('CDN-ised Post Icons');
		}
	}

	public static function apply_cdn_rewrite($url)
	{
		global $vbulletin;

		$new = str_replace(array($vbulletin->options['bburl'] . '/', '\"'), '', $url);

		if (in_array($new, self::$settings['pendingitems']['clientscript']) || in_array($new, self::$settings['customfolders']) || in_array($new, self::$settings['customitems']) || in_array($new, self::$settings['pendingitems']['custom']))
		{
			return '"' . self::$settings['cdnurl'] . '/' . str_replace(array($vbulletin->options['bburl'] . '/', '\"', '"'), '', $url);
		}
		else if (intval($vbulletin->versionnumber) == 3)
		{
			foreach (self::$settings['customfolders'] as $folder)
			{
				if (stristr($new, $folder) !== false)
				{
					return '"' . self::$settings['cdnurl'] . '/' . str_replace(array($vbulletin->options['bburl'] . '/', '\"'), '', $url);
				}
			}
		}
		else
		{
			return str_replace('\"', '"', $url);
		}
	}

	public static function apply_cdn_finalise(&$output)
	{
		global $vbulletin;

		$find = array();
		$replace = array();

		if (empty(self::$settings['customfolders']) || !is_array(self::$settings['customfolders']))
		{
			self::$settings['customfolders'] = array();
		}

		if (empty(self::$settings['customitems']) || !is_array(self::$settings['customitems']))
		{
			self::$settings['customitems'] = array();
		}

		if (empty(self::$settings['pendingitems']['custom']) || !is_array(self::$settings['pendingitems']['custom']))
		{
			self::$settings['pendingitems']['custom'] = array();
		}

		if (intval($vbulletin->versionnumber) == 3 && is_array(self::$settings['pendingitems']['stylevar_folders']))
		{
			self::$settings['customfolders'] = array_unique(array_merge(self::$settings['customfolders'], self::$settings['pendingitems']['stylevar_folders']));
		}

		if (self::cdn_online() && count(self::$settings['pendingitems']['clientscript']) > 0)
		{
			$folders = array_merge(array('clientscript'), self::$settings['customfolders']);

			$output = preg_replace("#\"(" . preg_quote($vbulletin->options['bburl']) . ")?(/)?(" . str_replace('IMPLODEKEY', '|', preg_quote(implode('IMPLODEKEY', $folders))) . ")/(.+?[^\"])\.(js|css|gif|jpeg|jpg|png|bmp|swf)#ie", "vboptimise_cdn::apply_cdn_rewrite('\\0');", $output);

			if (is_array(self::$settings['customitems']) && count(self::$settings['customitems']) > 0)
			{
				$output = preg_replace("#\"(" . implode('|', self::$settings['customitems']) . ")#ie", "vboptimise_cdn::apply_cdn_rewrite('\\0');", $output);
			}

			vb_optimise::report('CDN-ised Javascript/CSS and Custom Folders: ' . count(self::$settings['customfolders']));
		}
	}

	public static function apply_cdn_styles()
	{
		global $vbulletin, $style;

		$version = intval($vbulletin->versionnumber);

		if (!is_array(self::$settings['pendingitems']['stylevar_folders']))
		{
			self::$settings['pendingitems']['stylevar_folders'] = array();
		}

		if (self::cdn_online() && in_array($style['styleid'], self::$settings['styles']))
		{
			if ($version == 3)
			{
				self::apply_cdn_styles_vb3();
				return false;
			}

			foreach ($vbulletin->stylevars as $name => $var)
			{
				if ($var['datatype'] == 'imagedir')
				{
					// vB4 have some inconsistencies with "imgdir" and "imagedir", we'll account for both
					$folder = trim($var['imagedir']) != '' ? $var['imagedir'] : $var['string'];

					if (in_array($folder, self::$settings['pendingitems']['stylevar_folders']))
					{
						$vbulletin->stylevars[$name] = array('string' => self::$settings['cdnurl'] . '/' . $folder, 'imagedir' => self::$settings['cdnurl'] . '/' . $folder, 'datatype' => 'imagedir');
					}
				}

				if (!$vbulletin->options['storecssasfile'] && trim($var['image']) != '' && preg_match("#^url\((.*[^\)])\)#i", $var['image'], $matches))
				{
					$image = trim($matches[1]);

					if (!preg_match("#^\.?/#", $image))
					{
						$vbulletin->stylevars[$name]['image'] = str_replace($image, self::$settings['cdnurl'] . '/' . $image, $vbulletin->stylevars[$name]['image']);
					}
				}
			}

			vb_optimise::report('CDN-ised stylevar image folders.');
		}		
	}

	public static function apply_cdn_styles_vb3()
	{
		global $vbulletin, $style, $headinclude, $stylevar, $show, $foruminfo, $threadinfo;
		global $vbphrase, $pagenumber, $editor_css;

		if (is_array(self::$settings['pendingitems']['stylevar_css_images']))
		{
			foreach (self::$settings['pendingitems']['stylevar_css_images'] as $image)
			{
				if (!preg_match("#\.css#", $image)) // css will be handled later...
				{
					$style['css'] = str_replace($image, self::$settings['cdnurl'] . '/' . $image, $style['css']);
				}
			}
	
			eval('$headinclude = "' . fetch_template('headinclude') . '";');
	
			vb_optimise::report('CDN-ised stylevar image folders.');
		}
	}

	public static function apply_cdn_posticon(&$iconpath)
	{
		if ($iconpath != '' && self::cdn_online() && is_array(self::$settings['pendingitems']['posticon']) && in_array($iconpath, self::$settings['pendingitems']['posticon']))
		{
			$iconpath = self::$settings['cdnurl'] . '/' . $iconpath;
		}
	}

	public static function apply_cdn_postbit(&$post)
	{
		self::apply_cdn_posticon($post['iconpath']);
	}

	public static function cdn_online()
	{
		return vboptimise_cdn::$settings['online'] && vboptimise_cdn::$settings['status'] == 'integrated' && !vboptimise_cdn::$settings['pendsync'];
	}

	private static function save_new_provider($providers, $provider = '', $settings = array())
	{
		global $vbphrase;

		if (!in_array($provider, array_keys($providers)))
		{
			self::setup_error('You selected an invalid CDN Provider.');
		}

		self::load_provider($provider);
		self::$cdn->build_settings();
		self::$cdn->apply_settings($settings);

		if (!self::$cdn->check_connection())
		{
			self::setup_error('
				Sorry but we could verify the connection to your CDN Provider. Please go back and check the settings you entered.
				<br />To assist you, this is the response we got back from the API:<br />
				' . self::$cdn->error . '
			');
		}

		self::$settings = array(
			'status'	=> 'integrated',
			'provider'	=> $provider,
			'cdn'		=> $settings,
			'online'	=> false,
		);

		self::save_settings();
		exec_header_redirect('vboptimise.php?do=cdn');
	}

	private static function display_setup_provider($providers, $provider = '')
	{
		global $vbphrase;

		if (!in_array($provider, array_keys($providers)))
		{
			self::setup_error('You selected an invalid CDN Provider.');
		}

		self::load_provider($provider);
		self::$cdn->build_settings();

		print_form_header('vboptimise', 'cdn');
		construct_hidden_code('setup', '2');
		construct_hidden_code('provider', $provider);
		print_table_header('CDN Integration');
		print_description_row('Now that you have selected your CDN Provider, vB Optimise will require settings to allow connection and interaction with the providers API.');

		foreach (self::$cdn->settings as $setting => $title)
		{
			print_input_row($title, 'settings[' . $setting . ']', '');
		}

		print_submit_row($vbphrase['continue'], false);
	}

	private static function load_provider($provider = '')
	{
		require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_cdn_model.php');
		require_once(DIR . '/dbtech/vboptimise_pro/includes/cdn/' . $provider . '/cdn.php');

		$class = 'cdn_' . $provider;

		self::$cdn = new $class;
		self::$cdn->apply_settings(self::$settings['cdn']);
	}

	public static function setup_error($error = '')
	{
		global $vbphrase;

		print_cp_header('vB Optimise: CDN Integration');

		print_table_start();
		print_table_header('CDN Integration Error');
		print_description_row($error);
		print_description_row("<a href='javascript:history.go(-1);'>{$vbphrase['go_back']}</a>");
		print_table_footer();
		print_cp_footer();
	}

	private static function fetch_providers()
	{
		$return = array();
		$providers = self::fetch_map(DIR . '/dbtech/vboptimise_pro/includes/cdn', false, true);

		if (is_array($providers['map']))
		{
			foreach ($providers['map'] as $path => $provider)
			{
				$return[$provider] = ucwords(str_replace('_', ' ', $provider));
			}
		}

		return $return;
	}

	public static function fetch_map($dir = '', $deep = true, $only_dir = false)
	{
		self::$map = array();
		self::scan($dir, $deep, $only_dir);

		return array(
			'root'	=> $dir . '/',
			'map'	=> self::$map,
		);
	}

	protected static function valid($item = '')
	{
		return $item != '.' && $item != '..' && !preg_match("#\.db$#i", $item) && !preg_match("#\.svn$#i", $item);
	}

	protected static function scan($dir = '', $deep = true, $only_dir = false)
	{
		if (is_dir($dir) && is_readable($dir))
		{
			$handle = opendir($dir);

			while (($item = readdir($handle)) !== false)
			{
				if (self::valid($item) && is_readable($dir . '/' . $item))
				{
					if (is_dir($dir . '/' . $item) && $only_dir)
					{
						self::$map[$dir . '/' . $item] = $item;
					}

					if (is_dir($dir . '/' . $item) && $deep)
					{
						self::scan($dir . '/' . $item, $deep, $only_dir);
					}
					else if (!$only_dir)
					{
						self::$map[$dir . '/' . $item] = $item;
					}
				}
			}

			closedir($handle);
		}
	}
}

if (!function_exists('mime_content_type'))
{
	function mime_content_type($file = '')
	{
		static $mime_types = array(
			'txt'	=> 'text/plain',
			'htm'	=> 'text/html',
			'html'	=> 'text/html',
			'php'	=> 'text/html',
			'css'	=> 'text/css',
			'js'	=> 'application/javascript',
			'json'	=> 'application/json',
			'xml'	=> 'application/xml',
			'swf'	=> 'application/x-shockwave-flash',
			'flv'	=> 'video/x-flv',
			'png'	=> 'image/png',
			'jpe'	=> 'image/jpeg',
			'jpeg'	=> 'image/jpeg',
			'jpg'	=> 'image/jpeg',
			'gif'	=> 'image/gif',
			'bmp'	=> 'image/bmp',
			'ico'	=> 'image/vnd.microsoft.icon',
			'tiff'	=> 'image/tiff',
			'tif'	=> 'image/tiff',
			'svg'	=> 'image/svg+xml',
			'svgz'	=> 'image/svg+xml',
			'zip'	=> 'application/zip',
			'rar'	=> 'application/x-rar-compressed',
			'mp3'	=> 'audio/mpeg',
		);
	
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	
		if (array_key_exists($ext, $mime_types))
		{
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open'))
		{
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mimetype;
		}
		else
		{
			return 'application/octet-stream';
		}
	}
}

// For amazon
function custom_mime_content_type($file = '')
{
	static $mime_types = array(
		'txt'	=> 'text/plain',
		'htm'	=> 'text/html',
		'html'	=> 'text/html',
		'php'	=> 'text/html',
		'css'	=> 'text/css',
		'js'	=> 'application/javascript',
		'json'	=> 'application/json',
		'xml'	=> 'application/xml',
		'swf'	=> 'application/x-shockwave-flash',
		'flv'	=> 'video/x-flv',
		'png'	=> 'image/png',
		'jpe'	=> 'image/jpeg',
		'jpeg'	=> 'image/jpeg',
		'jpg'	=> 'image/jpeg',
		'gif'	=> 'image/gif',
		'bmp'	=> 'image/bmp',
		'ico'	=> 'image/vnd.microsoft.icon',
		'tiff'	=> 'image/tiff',
		'tif'	=> 'image/tiff',
		'svg'	=> 'image/svg+xml',
		'svgz'	=> 'image/svg+xml',
		'zip'	=> 'application/zip',
		'rar'	=> 'application/x-rar-compressed',
		'mp3'	=> 'audio/mpeg',
	);

	$ext = strtolower(array_pop(explode('.', $file)));

	if (array_key_exists($ext, $mime_types))
	{
		return $mime_types[$ext];
	}
	elseif (function_exists('finfo_open'))
	{
		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $file);
		finfo_close($finfo);
		return $mimetype;
	}
	else
	{
		return 'application/octet-stream';
	}
}