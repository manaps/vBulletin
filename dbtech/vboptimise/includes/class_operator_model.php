<?php
class vb_optimise_operator
{
	protected $cacheType = '';

	// These cache items should never be flushed
	protected $protected = array(
		'vb.optimiser.stats',		// vB Optimiser's temporary statistics
	);

	// Items already set in this session
	protected $session_cache = array();

	public function fetchType()
	{
		return $this->cacheType ? 'vbo_cachefail_' . $this->cacheType : 'vbo_cachefail';
	}

	public function id($id = '')
	{
		return vb_optimise::$prefix . $id;
	}

	public function full_flush()
	{
		$this->protected = array('vb.optimiser.stats');
		$this->flush();
		$this->protected = array(
			'vb.cache.index',
			'vb.cache.event',
			'vb.optimiser.stats',
		);
	}

	public function get($id = '')
	{
		global $vbulletin;

		if (isset($this->session_cache[$this->id($id)]))
		{
			return $this->handle_blank($this->session_cache[$this->id($id)]['value']);
		}

		$value = $this->_get($id);

		if (!in_array($id, $this->protected) && $id != 'vb.flush.time' && is_object($vbulletin) && $vbulletin->options['vbo_altflush'])
		{
			$flushed = $this->get('vb.flush.time');

			if (intval($flushed) < 1)
			{
				vb_optimise::report('Global flush key not yet assigned, assigning now.');
				$this->set('vb.flush.time', TIMENOW);

				return $this->get($id);
			}

			if (intval($value['time']) < intval($flushed))
			{
				vb_optimise::report($id . ' is stale.');
				return false;
			}
		}

		return $this->handle_blank($value['value']);
	}

	public function set($id = '', $value = '')
	{
		$value = array(
			'time'	=> TIMENOW,
			'value'	=> $value,
		);

		$this->_set($id, $value);
		$this->session_cache[$this->id($id)] = $value;
	}

	public function flush($silent = true)
	{
		global $vbulletin;

		$protect = array();

		foreach ($this->protected as $keep_protected)
		{
			$protect[$keep_protected] = $this->get($keep_protected);
		}

		if (is_object($vbulletin) && $vbulletin->options['vbo_altflush'])
		{
			$this->set('vb.flush.time', TIMENOW);
		}
		else
		{
			$this->do_flush($silent);
		}

		foreach ($protect as $keep_protected => $data)
		{
			$this->set($keep_protected, $data);
		}

		unset($protect);
	}

	public function handle_blank($value = '')
	{
		if (!is_array($value) && trim($value) == '{VBO_BLANK}')
		{
			return false;
		}
		else if (!is_array($value) && trim($value) == '')
		{
			return false;
		}

		return $value;
	}

	public function set_cache($id = '', $value = '')
	{
		$cache = $this->vbcache_getindex();

		if (is_array($value) && $value['cacheid'])
		{
			$this->set($value['cacheid'], $value['data']);

			unset($value['data']);
		}

		if ($value != false)
		{
			$cache[$id] = $value;
		}
		else
		{
			unset($cache[$id]);
		}

		$this->set('vb.cache.index', $cache);
		unset($cache);
	}

	public function set_event($id = '', $value = '')
	{
		$events = $this->vbcache_getindex('vb.cache.event');

		if ($value != false)
		{
			$events[$id] = $value;
		}
		else
		{
			unset($events[$id]);
		}

		$this->set('vb.cache.event', $events);
		unset($event);
	}

	public function vbcache_getindex($index = 'vb.cache.index')
	{
		$item = $this->get($index);

		return $item;
	}
}
?>