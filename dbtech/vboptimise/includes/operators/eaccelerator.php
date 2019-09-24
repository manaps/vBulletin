<?php
class vb_optimise_eaccelerator extends vb_optimise_operator
{
	protected $cacheType = 'eaccelerator';
	
	public function connect()
	{
		if (!function_exists('eaccelerator_get'))
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

		return eaccelerator_get($this->id($id));
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

		eaccelerator_rm($id);
		eaccelerator_put($this->id($id), $value, $vbulletin->options['vbo_ttl']);
	}

	public function do_flush($silent = true)
	{
		$success = '__vBOptimise_EA_Test_' . rand(3,999) . '__';

		$this->set('vBOptimiseTest', $success);

		@eaccelerator_clear();

		if (!$silent && $this->get('vBOptimiseTest') == $success && function_exists('print_cp_message'))
		{
			print_cp_message('vB Optimise: Your eAccelerator requires you to specify permission to this directory to allow vB Optimise to clear your cache. For more information please visit <a href="http://bart.eaccelerator.net/doc/phpdoc/eAccelerator/_info_php.html#functioneaccelerator_clear" target="_blank">here</a>.');
		}
	}
}
?>