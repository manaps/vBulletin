<?php
class vb_optimise_redis extends vb_optimise_operator
{
	protected $cacheType = 'redis';
	var $redis, $redis_read;
	var $connected = false;

	public function connect()
	{
		global $vbulletin;

		if (!class_exists('Redis'))
		{
			return false;
		}

		if ($this->connected)
		{
			return $this->connected;
		}

		$this->redis_read = new Redis();
		$this->redis = new Redis();

		// first, connect to redis server, find out if we are master or slave; make master connection
		foreach ($vbulletin->config['Misc']['redisServers'] as $server)
		{
			if (!isset($server['addr']))
			{
				// Compat layer
				$server['addr'] =& $server[0];
				$server['port'] =& $server[1];
			}

			if ($this->redis_read->connect($server['addr'], $server['port'], $vbulletin->config['Misc']['redisTimeout'], NULL, $vbulletin->config['Misc']['redisRetry']))
			{
				break;
			}
		}

		try
		{
			$redis_info = $this->redis_read->info();
		}
		catch (Exception $e)
		{
			trigger_error('No valid caching servers found.', E_USER_ERROR);
		}

		// If this server is master, just create a copy
		if ($redis_info['role'] == 'master')
		{
			$this->redis = &$this->redis_read;
		// else read master info from the slave server, and make a connection to that master
		}
		else if ($redis_info["master_link_status"] == "up")
		{
			// find the master server
			$master_host = $redis_info["master_host"];
			$master_post = $redis_info["master_port"];

			if (!$this->redis->connect($master_host, $master_port, $vbulletin->config['Misc']['redisTimeout'], NULL, $vbulletin->config['Misc']['redisRetry']))
			{
				trigger_error('Master cache server is offline.', E_USER_ERROR);
			}

			if ($redis_info['master_last_io_seconds_ago'] > $vbulletin->config['Misc']['redisMaxDelay']) {
				// if this slave gets out of sync with master, switch to master redis instance to both read/write
				$this->redis_read = &$this->redis;
			}

		} else {
			trigger_error('Can not find write cache redis server.', E_USER_ERROR);
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

		return @unserialize($this->redis_read->get($this->id($id)));
	}

	public function _set($id = '', $value = '')
	{
		global $vbulletin;

		if (!$this->connect())
		{
			return false;
		}

		if (!is_array($value) AND trim($value) == '')
		{
			$value = '{VBO_BLANK}';
		}
		/**
		 * EX seconds -- Set the specified expire time, in seconds.
		 * PX milliseconds -- Set the specified expire time, in milliseconds.
		 * NX -- Only set the key if it does not already exist.
		 * XX -- Only set the key if it already exist.
		 *
		 */
		$this->redis->set($this->id($id), serialize($value), array('ex' => $vbulletin->options['vbo_ttl']));
	}

	public function do_flush($silent = false)
	{
		@$this->redis->flushAll();
	}
}
?>