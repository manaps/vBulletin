<?php
class vb_optimise_memcache extends vb_optimise_operator
{
	protected $cacheType = 'memcache';	
	var $memcache;
	var $connected = false;

	public function connect()
	{
		global $vbulletin;

		if (!class_exists('Memcache'))
		{
			return false;
		}

		if ($this->connected)
		{
			return $this->connected;
		}

		$this->memcache = new Memcache;

		if (is_array($vbulletin->config['Misc']['memcacheserver']))
		{
			foreach (array_keys($vbulletin->config['Misc']['memcacheserver']) AS $key)
			{
				$this->memcache->addServer(
					$vbulletin->config['Misc']['memcacheserver'][$key],
					$vbulletin->config['Misc']['memcacheport'][$key],
					$vbulletin->config['Misc']['memcachepersistent'][$key],
					$vbulletin->config['Misc']['memcacheweight'][$key],
					$vbulletin->config['Misc']['memcachetimeout'][$key],
					$vbulletin->config['Misc']['memcacheretry_interval'][$key]
				);
			}
		}
		else
		{
			$this->memcache->addServer($vbulletin->config['Misc']['memcacheserver'], $vbulletin->config['Misc']['memcacheport']);
		}

		$this->connected = true;

		return true;
	}

	public function _get($id = '')
	{
		if (!$this->connect())
		{
			return false;
		}

		return $this->memcache->get($this->id($id));
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

		$this->memcache->set($this->id($id), $value, MEMCACHE_COMPRESSED, $vbulletin->options['vbo_ttl']);
	}

	public function do_flush($silent = false)
	{
		@$this->memcache->flush();
	}
}
?>