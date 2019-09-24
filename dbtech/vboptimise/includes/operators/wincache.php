<?php
class vb_optimise_wincache extends vb_optimise_operator
{
	protected $cacheType = 'wincache';

	public function connect()
	{
		if (!function_exists('wincache_ucache_get'))
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

		return wincache_ucache_get($this->id($id));
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

		wincache_ucache_set($this->id($id), $value, $vbulletin->options['vbo_ttl']);
	}

	public function do_flush($silent = false)
	{
		global $vbulletin;

		wincache_ucache_clear();
		$this->session_vars = array();
	}
}
?>