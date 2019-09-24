<?php
class vb_optimise_apcu extends vb_optimise_operator
{
	protected $cacheType = 'apcu';

	public function connect()
	{
		if (!function_exists('apcu_fetch'))
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

		return apcu_fetch($this->id($id));
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

		apcu_store($this->id($id), $value, $vbulletin->options['vbo_ttl']);
	}

	public function flush($silent = false)
	{
		apcu_clear_cache();
	}
}
?>