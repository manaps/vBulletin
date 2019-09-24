<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */
if (!is_object($vbulletin))
{
	die('Cannot access directly.');
}

define('vboptimise', true);

require_once(DIR . '/dbtech/vboptimise/includes/class_operator_model.php');

class vb_optimise
{
	/**
	* Version info
	*
	* @public	mixed
	*/
	public static $jQueryVersion 	= '1.7.2';

	/**
	* Opcache Operator if assigned
	*
	* @public	object
	*/
	public static $cache = null;

	/**
	* Array of valid Opcache Operator models. Use register() to extend via hooks.
	*
	* @public	array
	*/
	public static $models = array(
		'none',
		'apc',
		'apcu',
		'xcache',
		'memcache',
		'eaccelerator',
		'wincache',
		'redis',
		'filecache',
	);

	/**
	* Array of valid cacher libraries to be called through cache(). Use register() to extend via hooks.
	*
	* @public	array
	*/
	public static $cachers = array(
		'datastore',
		'templates',
		'style',
		'thanksstats',
		'usertagstats',
		/*DBTECH_PRO_START*/
		'notices',
		'showgroups',
		'forumdisplaysub',
		'forumhomewol',
		'whoread',
		'whovisited',
		/*DBTECH_PRO_END*/
	);

	/**
	* Integer to hold current sessions MySQL queries saved.
	*
	* @public	integer
	*/
	public static $query_count = 0;

	/**
	* String to hold user-defined cache prefix
	*
	* @public	string
	*/
	public static $prefix = '';

	/**
	* Boolean value to determine if page output is cached
	*
	* @private	boolean
	*/
	private static $guestcache = false;

	/**
	* Escapes a string and makes it JavaScript-safe
	*
	* @param	mixed	The string or array to make JS-safe
	*/
	public static function jsEscapeString(&$arr)
	{
		$find = array(
			"\r\n",
			"\n",
			"\t",
			'"'
		);

		$replace = array(
			'\r\n',
			'\n',
			'\t',
			'\"',
		);

		$arr = str_replace($find, $replace, $arr);
	}

	/**
	* Encodes a string as a JSON object (consistent behaviour instead of relying on PHP built-in functions)
	*
	* @param	mixed	The string or array to encode
	* @param	boolean	(Optional) Whether this is an associative array
	* @param	boolean	(Optional) Whether we should escape the string or if they have already been escaped
	*/
	public static function encodeJSON($arr, $assoc = true, $doescape = true)
	{
		if ($doescape)
		{
			self::jsEscapeString($arr);
		}
		if (!$assoc)
		{
			// Not associative, simple return
			return '{"' . implode('","', $arr) . '"}';
		}

		$content = array();
		foreach ((array)$arr as $key => $val)
		{
			if (is_array($val))
			{
				// Recursion, definition: see recursion
				$val = self::encodeJSON($val);
				$content[] = '"' . $key . '":' . $val;
			}
			else
			{
				$content[] = '"' . $key . '":"' . $val . '"';
			}
		}

		return '{' . implode(',', $content) . '}';
	}

	/**
	* Outputs a JSON string to the browser
	*
	* @param	mixed	array to output
	*/
	public static function outputJSON($json, $full_shutdown = false)
	{
		if (!headers_sent())
		{
			// Set the header
			header('Content-type: application/json');
		}

		// Create JSON
		$json = self::encodeJSON($json);

		// Turn off debug output
		$GLOBALS['vbulletin']->debug = false;

		if (defined('VB_API') AND VB_API === true)
		{
			print_output($json);
		}

		//run any registered shutdown functions
		if (intval($GLOBALS['vbulletin']->versionnumber) > 3)
		{
			$GLOBALS['vbulletin']->shutdown->shutdown();
		}
		exec_shut_down();
		$GLOBALS['vbulletin']->db->close();

		$sendHeader = false;
		switch($GLOBALS['vbulletin']->options['ajaxheader'])
		{
			case 0 :
				$sendHeader = true;

			case 1 :
				$sendHeader = false;

			case 2 :
			default:
				$sendHeader = (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false);
		}

		if ($sendHeader)
		{
			// this line is causing problems with mod_gzip/deflate, but is needed for some IIS setups
			@header('Content-Length: ' . strlen($json));
		}

		// Finally spit out JSON
		echo $json;
		die();
	}

	/**
	* Register extensions to valid Opcache Operators or Cacher Libraries via hooks.
	*
	* @param	string	Extension to register
	* @param	string	Item to register
	*/
	public static function register($to, $what = '')
	{
		if (trim($what) == '')
		{
			return false;
		}

		if (!self::$$to || in_array($what, self::$$to))
		{
			return false;
		}

		self::$$to = array_merge(self::$$to, array($what));
		self::report('Registered ' . $what . ' to ' . $to);
	}

	/**
	* Reports internal message through to vBulletins debug combo-box.
	*
	* @param	string	Message to report
	*/
	public static function report($msg = '')
	{
		if (VB_AREA != 'AdminCP') // Dev debug messages mess with the vBulletin CMS ACP Management =/
		{
			devdebug('vBOptimise: ' . $msg);
		}
	}

	/**
	* Assigns and connects a valid Opcache Operator
	*
	* @param	string	Opcache Operator to assign
	*/
	public static function assign($model = '')
	{
		global $vbulletin;

		if (!$vbulletin->options['vbo_online'])
		{
			return false;
		}

		if (self::$cache !== null)
		{
			self::report('assign(' . $model . ') failed. vB Optimise already has a operator.');
			return false;
		}

		if (!in_array($model, self::$models))
		{
			trigger_error('vB Optimise could not assign the operator model \'' . $model . '\'. If you are requesting a custom operator please extend the $models array via vb_optimise::register().', E_USER_ERROR);
		}

		if (!file_exists(DIR . '/dbtech/vboptimise_pro/includes/operators/' . $model . '.php'))
		{
			if (!file_exists(DIR . '/dbtech/vboptimise/includes/operators/' . $model . '.php'))
			{
				self::report('Unable to assign Opcache Operator (' . $model . '). vB Optimise disabled.');
				$vbulletin->options['vbo_online'] = false;
				return false;
			}
			else
			{
				require_once(DIR . '/dbtech/vboptimise/includes/operators/' . $model . '.php');
			}
		}
		else
		{
			require_once(DIR . '/dbtech/vboptimise_pro/includes/operators/' . $model . '.php');
		}

		$class = 'vb_optimise_' . $model;

		self::$cache = new $class();

		if (self::$cache->connect())
		{
			self::report('Assigned Opcache Operator (' . $model . ')');
		}
		else
		{
			self::report('Unable to assign Opcache Operator (' . $model . '). vB Optimise disabled.');
			$vbulletin->options['vbo_online'] = false;
		}
	}

	/**
	* Checks if vB Optimise Cache can be used
	*
	* @param	string	Cache perm to check (optional)
	*
	* @return	Boolean	True on success
	*/
	public static function check_cache($cache = '')
	{
		global $vbulletin;

		if (!$vbulletin->options['vbo_online'])
		{
			return false;
		}

		if ($cache != '' && !$vbulletin->options['vbo_cache_' . $cache])
		{
			return false;
		}

		if (self::$cache == null)
		{
			self::report('cache(' . $cache . ') failed. No assignment made yet.');
			return false;
		}

		return true;
	}

	/**
	* Executes a cacher library with optional arguments
	*
	* @param	string	Cache Library to execute
	* @param	mixed	Optional. Reference argument
	* @param	mixed	Optional. Reference argument
	*/
	public static function cache($what = '', &$argument = false, &$argumentb = false, &$argumentc = false)
	{
		global $vbulletin;

		if (!self::check_cache($what))
		{
			return false;
		}

		if (!in_array($what, self::$cachers))
		{
			trigger_error('vB Optimise could not assign the cacher routine \'' . $what . '\'. If you are requesting a custom routine please extend the $cachers array via vb_optimise::register().', E_USER_ERROR);
		}

		if (!file_exists(DIR . '/dbtech/vboptimise_pro/includes/cachers/cacher_' . $what . '.php'))
		{
			if (file_exists(DIR . '/dbtech/vboptimise/includes/cachers/cacher_' . $what . '.php'))
			{
				require_once(DIR . '/dbtech/vboptimise/includes/cachers/cacher_' . $what . '.php');
			}
			else
			{
				self::report('Unable to assign cacher (' . $what . '). vB Optimise disabled.');
				$vbulletin->options['vbo_online'] = false;
				return false;
			}
		}
		else
		{
			require_once(DIR . '/dbtech/vboptimise_pro/includes/cachers/cacher_' . $what . '.php');
		}
	}

	/**
	* Triggers automatic cache flushes within Admin CP based on executions.
	*/
	public static function update()
	{
		global $vbulletin;

		if (!$vbulletin->options['vbo_online'])
		{
			return false;
		}

		self::report('Looking for conditions to flush cache automatically...');

		// ACP Actions to flush cache on
		$actions = array(
			'updatetemplate',
			'inserttemplate',
			'productimport',
			'rebuild',
			'insertstyle',
			'update',
			'clear_cache',
			'purgecache',
			'displayorder',
			'savestylevar',
			'insert',
			'import',
			'replace',
			'kill',
			'productsave',
			'productdependancy',
			'productcode',
			'productdisable',
			'productenable',
			'productkill',
			'dooptions',
		);

		// ACP actions to kill whole cache as a failsafe
		$full = array(
			'clear_cache',
			'purgecache',
		);

		if (in_array($_REQUEST['do'], $actions))
		{
			if (in_array($_REQUEST['do'], $full))
			{
				self::$cache->full_flush();
			}
			else
			{
				self::$cache->flush();
			}

			self::report('Automatically flushed cache.');
		}
	}

	/**
	* Pushes statistical data to the cache temporarily
	*
	* @param	int	Number of queries saved.
	*/
	public static function stat($num = 0)
	{
		global $vbulletin;

		self::$query_count += $num;

		if (!$vbulletin->options['vbo_online'] || !$vbulletin->options['vbo_stat'])
		{
			return false;
		}

		if (self::$cache == null)
		{
			return false;
		}

		$stats = self::$cache->get('vb.optimiser.stats');
		$stats = intval($stats) + $num;

		if ($num > 0)
		{
			self::$cache->set('vb.optimiser.stats', $stats);
		}
		else
		{
			self::$cache->set('vb.optimiser.stats', 0);
		}
	}

	/**
	* Updates statistical database from cache
	*/
	public static function updatestats()
	{
		global $vbulletin;

		if (!$vbulletin->options['vbo_online'] || !$vbulletin->options['vbo_stat'])
		{
			return false;
		}

		require_once(DIR . '/includes/functions_misc.php');

		$date = $vbulletin->db->escape_string(date('M jS', time()));
		$current = $vbulletin->db->query_first_slave("select queries from " . TABLE_PREFIX . "vboptimise where dateline='" . $date . "'");
		$current = intval($current['queries']);
		$add = intval(vb_optimise::$cache->get('vb.optimiser.stats'));
		self::stat(0);

		if ($add > 0)
		{
			$vbulletin->db->query("replace into " . TABLE_PREFIX . "vboptimise (dateline, queries) values ('$date', " . ($current + $add) . ")");
		}

		$total = $vbulletin->db->query_first_slave("select sum(queries) as total from " . TABLE_PREFIX . "vboptimise");
		$total = intval($total['total']);

		build_datastore('vbo_resource_savings', $total);

		self::report('Added ' . $add . ' count to the statistics.');
	}

	/*DBTECH_PRO_START*/
	/**
	* Determines if the output should be cached or if cache should be served
	*/
	public static function start_guestcache($startup = true)
	{
		global $vbulletin;

		if (!$startup)
		{
			return false;
		}

		$excluded = explode("\n", trim($vbulletin->options['vbo_guest_scripts_exclusion']));
		self::trimarray($excluded);

		if (defined('THIS_SCRIPT') AND (in_array(THIS_SCRIPT, $excluded) OR THIS_SCRIPT == 'register'))
		{
			$vbulletin->options['vbo_cache_guests'] = 0;
			self::report('Guest caching disabled on script exclusion');
		}

		if (intval($vbulletin->versionnumber) > 3 AND (defined('STYLE_TYPE') && STYLE_TYPE == 'mobile'))
		{
			return false;
		}

		if (!self::check_cache('guests'))
		{
			return false;
		}

		$check_post = $_POST;
		unset($check_post['ajax']);

		if ($vbulletin->userinfo['userid'] < 1 && sizeof($check_post) < 1)
		{
			self::$guestcache = true;
		}

		if (self::$guestcache && $page = self::$cache->get('pgc.' . self::key_guestcache()))
		{
			if ($page !== false)
			{
				if (TIMENOW < $page['ttl'])
				{
					$saved = ($page['queries'] - $vbulletin->db->querycount);
					$php = vb_number_format(microtime(true) - self::timestart(), 5);

					if ($saved > 0)
					{
						self::stat($saved);
					}

					self::report('Displaying guest cached content (saved: ' . ($saved) . ', remaining ttl: ' . ($page['ttl'] - TIMENOW) . ' seconds).');

					if ($vbulletin->options['vbo_guest_comment'])
					{
						$page['output'] = str_replace('</body>', '<!-- vB Optimise Guest Cached Page / Generated in ' . $php . ' seconds with ' . $vbulletin->db->querycount . ' queries and ' . $saved . ' queries saved vs uncached --></body>', $page['output']);
					}

					// Kill all plugins, they ran when we cached the page - don't repeat
					$vbulletin->pluginlist = array();
					if (method_exists(vBulletinHook, 'init'))
					{
						vBulletinHook::init()->set_pluginlist($vbulletin->pluginlist);
					}
					else
					{
						vBulletinHook::set_pluginlist($vbulletin->pluginlist);
					}

					self::display_stats($null, $saved, $page['queries'], vb_number_format($page['php'] - $php, 5), $page['php']);

					do
					{
						if (!class_exists('DBSEO'))
						{
							// Something else went awry
							break;
						}

						if (!DBSEO::$config['dbtech_dbseo_active'])
						{
							// Mod is disabled
							break;
						}

						if (defined('VBSEO_UNREG_EXPIRED'))
						{
							// vBSEO compat
							break;
						}

						// Prepare the content
						$page['output'] = DBSEO::processContent($page['output']);
					}
					while (false);

					if (intval($vbulletin->versionnumber) == 4)
					{
						$charset = $vbulletin->userinfo['lang_charset'] ? $vbulletin->userinfo['lang_charset'] : vB_Template_Runtime::fetchStyleVar('charset');
					}
					else
					{
						$charset = $vbulletin->userinfo['lang_charset'] ? $vbulletin->userinfo['lang_charset'] : $GLOBALS['stylevar']['charset'];
					}
					@header('Content-Type: text/html; charset=' . $charset);

					print_output($page['output']);
				}
			}
		}

		unset($check_post);

		if (self::$guestcache)
		{
			self::report('Guest caching started.');

			if ($vbulletin->debug) // stops duplication
			{
				$vbulletin->debug = false;
			}
		}
	}

	/**
	* Saves guest output
	*/
	public static function finish_guestcache(&$output)
	{
		global $vbulletin;

		if (self::$guestcache)
		{
			if (in_array('class_humanverify.php', self::runtime_scripts()))
			{
				self::report('Human verification detected, guest caching aborted.');
				return false;
			}

			self::$cache->set('pgc.' . self::key_guestcache(), array(
				'output'	=> $output,
				'queries'	=> ($vbulletin->db->querycount + self::$query_count),
				'ttl'		=> TIMENOW + ($vbulletin->options['vbo_cache_guests'] * 60),
				'time'		=> TIMENOW,
				'php'		=> vb_number_format(microtime(true) - self::timestart(), 5),
			));
			self::report('Assigned page to guest cache');
		}

		if (defined('VBO_CMS_TEMPLATES') && class_exists('vB_Template') && isset(vB_Template::$template_usage))
		{
			$vbo_cached = explode('|', VBO_CMS_TEMPLATES);

			if (is_array($vbo_cached))
			{
				foreach (vB_Template::$template_usage as $name => $usage)

				{
					if (in_array($name, $vbo_cached))
					{
						self::stat(1);
					}
				}
			}
		}

		self::display_stats($output, self::$query_count, ($vbulletin->db->querycount + self::$query_count), 0, 0, true);
	}
	/*DBTECH_PRO_END*/

	protected static function timestart()
	{
		if (count(explode(' ', TIMESTART)) > 1)
		{
			list($usec, $sec) = explode(' ', TIMESTART);
			return ((float)$usec + (float)$sec);
		}

		return TIMESTART;
	}

	protected static function saved($saved, $original)
	{
		return (($saved > 0 AND ($original / 100) != 0) ? vb_number_format($saved / ($original / 100), 2) : 0);
	}

	protected static function trimarray(&$array)
	{
		if (is_array($array))
		{
			foreach ($array as $key => $value)
			{
				if (is_array($value))
				{
					self::trimarray($array[$key]);
				}
				else if (is_string($value))
				{
					$array[$key] = trim($value);
				}
			}
		}
	}

	protected static function runtime_scripts()
	{
		static $scripts = false;

		if (!$scripts)
		{
			$scripts = @get_included_files();

			if (!is_array($scripts))
			{
				$scripts = array();
			}

			foreach ($scripts as $key => $value)
			{
				$arr = explode('/', str_replace('\\', '/', $value));
				$scripts[$key] = array_pop($arr);
			}
		}

		return $scripts;
	}

	/**
	* Output statistics
	*/
	public static function display_stats(&$pageoutput, $mysql_saved = 0, $mysql_total = 0, $php_saved = 0, $php_total = 0, $now = false)
	{
		global $vbulletin, $vbphrase;
		static $vbostats = false;

		if (!$vbulletin->options['vbo_online'])
		{
			return false;
		}

		if (!$vbostats AND !$now)
		{
			$vbostats = true;

			$vbulletin->pluginlist['global_complete'] .= "\r\n\r\nvb_optimise::display_stats(\$output, $mysql_saved, $mysql_total, $php_saved, $php_total);";

			if (method_exists(vBulletinHook, 'init'))
			{
				vBulletinHook::init()->set_pluginlist($vbulletin->pluginlist);
			}
			else
			{
				vBulletinHook::set_pluginlist($vbulletin->pluginlist);
			}

			return false;
		}

		if ($vbulletin->options['vbo_footer_info'])
		{
			$pageoutput = str_replace('<!--VBO_SAVED-->', str_replace(' / PHP 0%', '', construct_phrase($vbphrase['vboptimise_resources_saved_mysql_x_php_y'], self::saved($mysql_saved, $mysql_total), self::saved($php_saved, $php_total))), $pageoutput);
		}
		else
		{
			$pageoutput = str_replace('(<!--VBO_SAVED-->)', '', $pageoutput);
		}
	}

	/*DBTECH_PRO_START*/
	/**
	* Generates a unique key to identify the page
	*/
	private static function key_guestcache()
	{
		global $vbulletin, $style;
		static $key;

		if (!$key)
		{
			$key = md5(implode('', @array_merge(array(
				$_SERVER['HTTP_HOST'],
				THIS_SCRIPT,
				$_COOKIE[COOKIE_PREFIX . 'userstyleid'],
				$_COOKIE[COOKIE_PREFIX . 'languageid'],
				$vbulletin->options['styleid'],
				((is_browser('ie') AND !is_browser('ie', 7) AND !is_browser('ie', 8) AND !is_browser('ie', 9) AND !is_browser('ie', 10)) ? 'ie6' : 'other'),
				(($vbulletin->mobile_browser OR $vbulletin->mobile_browser_advanced) ? 'mobile' : 'desktop'),
				(isset($_SERVER['GEOIP_COUNTRY_CODE']) ? $_SERVER['GEOIP_COUNTRY_CODE'] : 'any'),
			), $_REQUEST)));
		}

		return $key;
	}

	/**
	* Start CDN Integration
	*/
	public static function start_cdn($forum = false)
	{
		global $vbulletin;
		static $started = false;

		if ($started == true)
		{
			return false;
		}

		$started = true;

		if (!$vbulletin->options['vbo_online'])
		{
			if (!$forum)
			{
				require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_cdn.php');
				vboptimise_cdn::parse_config($vbulletin->options['vbo_cdn_settings']);
			}

			return false;
		}

		require_once(DIR . '/dbtech/vboptimise_pro/includes/class_vboptimise_cdn.php');

		vboptimise_cdn::parse_config($vbulletin->options['vbo_cdn_settings']);

		if ($forum && vboptimise_cdn::cdn_online())
		{
			vboptimise_cdn::apply_cdn();
		}
	}
	/*DBTECH_PRO_END*/
}