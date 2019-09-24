<?php
class vb_optimise_xcache extends vb_optimise_operator
{
	protected $cacheType = 'xcache';
	
	public function connect()
	{
		if (!function_exists('xcache_get'))
		{
			return false;
		}

		return true;
	}

	public function _get($id = '')
	{
		if (!$this->connect())
		{
			return false;
		}

		return xcache_get($this->id($id));
	}

	public function _set($id = '', $value = '')
	{
		global $vbulletin;

		if (!$this->connect())
		{
			return false;
		}

		if (!is_array($value) && trim($value) == '')
		{
			$value = '{VBO_BLANK}';
		}

		xcache_set($this->id($id), $value, $vbulletin->options['vbo_ttl']);
	}

	public function do_flush($silent = false)
	{
		global $vbulletin;

		if ($vbulletin->options['vbo_xcache_auth'])
		{
			require(DIR . '/dbtech/vboptimise/config.php');

			$_SERVER['PHP_AUTH_USER'] = $xcache_username;
			$_SERVER['PHP_AUTH_PW'] = $xcache_password;
		}

		for ($x = 0, $total = @xcache_count(XC_TYPE_VAR); $x < $total; $x++)
		{
			@xcache_clear_cache(XC_TYPE_VAR, $x);
		}

		for ($x = 0, $total = @xcache_count(XC_TYPE_PHP); $x < $total; $x++)
		{
			@xcache_clear_cache(XC_TYPE_PHP, $x);
		}

		unset($xcache_username, $xcache_password, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
	}
}
?>