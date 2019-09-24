<?php
class vb_optimise_none extends vb_optimise_operator
{
	public function connect()
	{
		return false;
	}

	public function get($id = '')
	{
		if (!$this->connect())
		{
			return false;
		}
	}

	public function set($id = '', $value = '')
	{
		if (!$this->connect())
		{
			return false;
		}
	}

	public function do_flush($silent = false)
	{
	}
}
?>