<?php
class vb_optimise_apc extends vb_optimise_operator
{
	protected $cacheType = 'apc';
	
	public function connect()
	{
		if (!function_exists('apc_fetch'))
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

		return apc_fetch($this->id($id));
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

		apc_store($this->id($id), $value, $vbulletin->options['vbo_ttl']);
	}

	public function flush($silent = false)
	{
		apc_clear_cache('user');
		apc_clear_cache('opcode');
	}
}
?>