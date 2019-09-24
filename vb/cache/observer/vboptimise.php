<?php
if (!defined('VB_ENTRY'))
{
	die('Access denied.');
}

/**
 * vB Optimise Cache Observer
 * Tracks events using vB Optimise.
 *
 * @package vB Optimise
 * @author Deceptor
 * @copyright Deceptor / DBTech.
 */
class vB_Cache_Observer_vBOptimise extends vB_Cache_Observer
{
	/*Properties====================================================================*/

	/**
	 * Events that have been registered.
	 * The array is assoc in the form $key => array($events)
	 *
	 * @var array
	 */
	protected $registered_events = array();


	/**
	 * Cache entries that have been purged.
	 * The array is in the form $key => true
	 *
	 * @var array
	 */
	protected $purged_entries = array();

	/**
	 * Events that have been triggered
	 * The array is in the form $event => true
	 *
	 * @var bool
	 */
	protected $triggered_events = array();


	/**
	 * Events that have been purged
	 * The array is in the form $event => true
	 *
	 * @var array
	 */
	protected $purged_events = array();



	/*Construction==================================================================*/

	/**
	 * Returns singleton instance of self.
	 * @todo This can be inherited once late static binding is available.  For now
	 * it has to be redefined in the child classes
	 *
	 * @param vB_Cache $cache					- Reference to the cache we are observing
	 * @return vB_Cache_Observer				- Reference to singleton instance of cache observer
	 */
	public static function instance(vB_Cache $cache)
	{
		if (!isset(self::$instance))
		{
			$class = __CLASS__;
			self::$instance = new $class($cache);
		}

		return self::$instance;
	}



	/*Cache Events==================================================================*/

	/**
	 * Notifies observer that a cache entry has been created with event id's.
	 *
	 * @param string $key						- The key of the cache entry
	 * @param array string $events				- Array of associated events
	 */
	public function written($key, $events)
	{
		vb_optimise::report('vB CacheEvent with Events.');
		$this->registered_events[(string)$key] = $events;
	}


	/**
	 * Notifies observer that a cache entry was purged.
	 *
	 * @param string $key						- The key of the cache entry that was purged
	 */
	public function purged($key)
	{
		vb_optimise::report('vB Cache Entry Purged: ' . $key);
		$this->purged_entries[(string)$key] = true;
	}


	/**
	 * Notifies observer of a crud event.
	 *
	 * @param array | string $event						- The id of the crud event
	 */
	public function event($events)
	{
		if (empty($events))
		{
			return;
		}
		
		$events = (array)$events;
		
		foreach ($events AS $event)
		{
			$this->triggered_events[strval($event)] = true;
		}
	}
	
	/**
	 * Builds list of events to be purged.
	 *
	 * @param array | string $event						- The id of the event
	 */
	public function eventPurge($events)
	{
		if (empty($events))
		{
			return;
		}
		
		$events = (array)$events;

		foreach ($events AS $event)
		{
			if (!is_array($event))
			{
				vb_optimise::report('vB Cache Event Purged: ' . strval($event));
				$this->purged_events[strval($event)] = true;
			}
			else
			{
				foreach ($event AS $subevent)
				{
					vb_optimise::report('vB Cache SubEvent Purged: ' . strval($subevent));
					$this->purged_events[strval($subevent)] = true;
				}
			}
		}
	}


	/**
	 * Notifies observer that a cache entry expired.
	 * The vB Optimise observer has nothing to do.
	 *
	 * @param string $key						- The key of the cache entry that expired
	 */
	public function expired($key){}


	/**
	 * Notifies observer that the cache was cleaned.
	 * The observer will remove all event associations.
	 */
	public function clean()
	{
		vb_optimise::report('vB CacheEvent cleaned.');
		vb_optimise::stat(1);
		vb_optimise::$cache->set('vb.cache.event', false);
	}



	/*Shutdown======================================================================*/

	/**
	 * Ensures that all event maintenance is executed before shutdown.
	 */
	public function shutdown()
	{
		// Save registered events
		$this->registerEvents();

		// Purge events that are no longer associated
		$this->purgeEvents();

		// Expire cache entries triggered by events
		if ($this->triggerEvents())
		{
			$this->cache->shutdown();
		}
	}


	/**
	 * Registers all new cache entry -> event associations.
	 * Pending events are in $this->registered_events in the form
	 * $key => array($events)
	 */
	protected function registerEvents()
	{
		if (!sizeof($this->registered_events))
		{
			vb_optimise::report('vB CacheEvent Register failed (0 events).');
			return;
		}

		$values = array();

		// Register events
		foreach ($this->registered_events AS $key => $events)
		{
			$key = $key;
			$events = array_unique($events);
			$cacheevents = array();

			foreach ($events AS $event)
			{
				if (is_array($event))
				{
					$cacheevents = array_merge($cacheevents, $event);
				}
				else
				{
					$cacheevents = array_merge($cacheevents, array($event));
				}
			}

			vb_optimise::$cache->set_event($key, array_unique($cacheevents));
		}

		vb_optimise::report('vB CacheEvent registered.');
		vb_optimise::stat(1);

		$this->registered_events = array();
	}


	/**
	 * Expires cache entries associated with triggered events.
	 *
	 * @return bool								- Whether any events were triggered
	 */
	protected function triggerEvents()
	{
		if (!sizeof($this->triggered_events))
		{
			return;
		}

		$fetch_events = vb_optimise::$cache->vbcache_getindex('vb.cache.event');

		if (is_array($fetch_events))
		{
			foreach ($fetch_events as $key => $triggerevents)
			{
				foreach (array_keys($this->triggered_events) as $triggeredevent)
				{
					if (in_array($triggeredevent, $triggerevents))
					{
						$this->cache->expire($key);
					}
				}
			}
		}

		vb_optimise::report('vB CacheEvent triggered events.');
		vb_optimise::stat(1);

		$this->triggered_events = array();

		return true;
	}


	/**
	 * Purges events that are no longer associated.
	 */
	protected function purgeEvents()
	{
		if (!sizeof($this->purged_entries))
		{
			return;
		}

		$entries = array();

		foreach (array_keys($this->purged_entries) AS $key)
		{
			vb_optimise::$cache->set_event($key, false);
		}

		vb_optimise::report('vB CacheEvent purged events.');
		vb_optimise::stat(1);

		$this->purged_entries = array();
	}
}