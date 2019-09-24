<?php
if (!defined('VB_ENTRY'))
{
	die('Access denied.');
}

/**
 * vB Optimise Cache.
 * Handler that caches and retrieves data from the vB Optimise Opcache Operator.
 * @see vB_Cache
 *
 * @package vB Optimise
 * @author Deceptor
 * @copyright Deceptor / DBTech.
 */
class vB_Cache_vBOptimise extends vB_Cache
{
	/*Properties====================================================================*/

	/**
	 * A reference to the singleton instance
	 *
	 * @var vB_Cache_vBOptimise
	 */
	protected static $instance;

	/**
	 * Cache entries for deferred purging
	 *
	 * @var array int
	 */
	protected $purged = array();

	/**
	 * Cache entries for deferred expiration.
	 *
	 * @var array int
	 */
	protected $expired = array();

	/**
	 * Cache entries that have been written during this request.
	 *
	 * @var array int
	 */
	protected static $written = array();



	/*Construction==================================================================*/

	/**
	 * Constructor protected to enforce singleton use.
	 * @see instance()
	 */
	protected function __construct(){}


	/**
	 * Returns singleton instance of self.
	 * @todo This can be inherited once late static binding is available.  For now
	 * it has to be redefined in the child classes
	 *
	 * @return vB_Cache_vBOptimise						- Reference to singleton instance of cache handler
	 */
	public static function instance()
	{
		if (!isset(self::$instance))
		{
			$class = __CLASS__;
			self::$instance = new $class();
		}

		return self::$instance;
	}


	/**
	 * Uses Late Static Binding to overload the Cache instance to use vB Optimise over DB model.
	 */
	public static function overload()
	{
		global $vbulletin;

		parent::$instance = vB_Cache_vBOptimise::instance();
		parent::$instance->attachObserver(vB_Cache_Observer_vBOptimise::instance(parent::$instance));

		$vbulletin->shutdown->add(array(parent::$instance, 'shutdown'));
	}


	/*Initialisation================================================================*/

	public function written(){}

	/**
	 * Writes the cache data to storage.
	 *
	 * @param vB_CacheObject $cache
	 */
	protected function writeCache(vB_CacheObject $cache)
	{
		$key = $cache->getKey();
		$data = $cache->getData();

		if (!empty($key) AND isset(self::$written[$key]))
		{
			return;
		}

		if (is_array($data) OR is_object($data))
		{
			$serialized = '1';
			$data = serialize($data);
		}
		else
		{
			$serialized = '0';
		}

		vb_optimise::report('vB Cache Written (' . $cache->getKey() . ')');
		vb_optimise::stat(1);
		vb_optimise::$cache->set_cache($cache->getKey(), array(
			'cacheid'	=> $cache->getKey(),
			'expires'	=> intval($cache->getExpiry()),
			'created'	=> TIMENOW,
			'locktime'	=> 0,
			'data'		=> $data,
			'serialized'	=> intval($serialized),
		));

		self::$written[$cache->getKey()] = true;
	}


	/**
	 * Reads the cache object from storage.
	 *
	 * @param string $key						- Id of the cache entry to read
	 * @return vB_CacheObject
	 */
	protected function readCache($key)
	{
		if (!is_object(vb_optimise::$cache))
		{
			return false;
		}
		
		$entry = vb_optimise::$cache->vbcache_getindex();
		$entry = $entry[$key];
		$entry['data'] = vb_optimise::$cache->get($entry['cacheid']);

		if ($entry === false || trim($entry['data']) == '')
		{
			return false;
		}

		if (intval($entry['serialized']))
		{
			$entry['data'] = unserialize($entry['data']);
		}

		vb_optimise::report('vB Cache Read (' . $key . ')');
		vb_optimise::stat(1);

		return new vB_CacheObject($key, $entry['data'], $entry['expires'], $entry['locktime']);
	}


	/**
	 * Reads an array of cache objects from storage.
	 *
	 * @param string $keys						- Ids of the cache entry to read
	 * @return array of vB_CacheObjects
	 */
	protected function readCacheArray($keys)
	{
		$found = array();
		$missing = array();
		$index = vb_optimise::$cache->vbcache_getindex();

		foreach ($keys as $id => $key)
		{
			$record = $index[$key];
			$record['data'] = vb_optimise::$cache->get($record['cacheid']);

			if ($record)
			{
				if (intval($record['serialized']))
				{
					try
					{
						$record['data'] = unserialize($record['data']);
						
						if ($record['data'])
						{
							$obj = new vB_CacheObject($record['cacheid'], $record['data'], $record['expires'], $record['locktime']);
							//only return good values
							if (!$obj->isExpired())
							{
								$found[$record['cacheid']] = $record['data'];
							}
						}
					}
					catch (exception $e)
					{
						//If we got here, something was improperly serialized
						//There's not much we can do, but we don't want to return bad data.
					}
				}
				else if ($record['data'])
				{
					$obj = new vB_CacheObject($record['cacheid'], $record['data'], $record['expires'], $record['locktime']);

					if (!$obj->isExpired())
					{
						$found[$record['cacheid']] = $record['data'];
					}
				}
			}
		}

		vb_optimise::report('vB Cache Index Read (' . implode(',', $keys) . ')');
		vb_optimise::stat(1);

		return $found;
	}


	/**
	 * Removes a cache object from storage.
	 *
	 * @param int $key							- Key of the cache entry to purge
	 * @return bool								- Whether anything was purged
	 */
	protected function purgeCache($key)
	{
		vb_optimise::report('vB Cache Purge Flag: ' . $key);
		$this->purged[] = $key;

		return true;
	}


	/**
	 * Sets a cache entry as expired in storage.
	 *
	 * @param string $key						- Key of the cache entry to expire
	 */
	protected function expireCache($key)
	{
		vb_optimise::report('vB Cache Expire Flag: ' . $key);
		$this->expired[] = $key;

		return true;
	}


	/**
	 * Locks a cache entry.
	 *
	 * @param string $key						- Key of the cache entry to lock
	 */
	public function lock($key)
	{
		vb_optimise::report('vB Cache Lock Flag: ' . $key);
		$index = vb_optimise::$cache->vbcache_getindex();
		$record = $index[$key];

		if ($record)
		{
			$record['locktime'] = TIMENOW;
		}

		vb_optimise::$cache->set_cache($key, $record);
		vb_optimise::stat(1);
	}



	/*Clean=========================================================================*/

	/**
	 * Cleans cache.
	 *
	 * @param bool $only_expired				- Only clean expired entries
	 * @param int $created_before				- Clean entries created before this time
	 */
	public function clean($only_expired = true, $created_before = false)
	{
		$index = vb_optimise::$cache->vbcache_getindex();

		if (!is_array($index))
		{
			return false;
		}

		if (!$only_expired AND !$created_before)
		{
			foreach ($index as $key => $data)
			{
				vb_optimise::$cache->set_cache($key, false);
			}

			$this->notifyClean();
			vb_optimise::stat(1);
		}
		else
		{
			foreach ($index as $key => $data)
			{
				if ($only_expired && ($data['expired'] >= 1 && $data['expired'] <= TIMENOW))
				{
					$this->purge($key);
				}
				else if ($created_before && $data['created'] < intval($created_before))
				{
					$this->purge($key);
				}
			}
			vb_optimise::stat(1);
		}
	}



	/*Shutdown======================================================================*\

	/**
	 * Perform any finalisation on shutdown.
	 */
	public function shutdown()
	{
		$kill = array();

		if (sizeof($this->purged))
		{
			foreach ($this->purged as $id => $key)
			{
				$kill[] = $key;
			}

			vb_optimise::stat(1);
		}
		$this->purged = array();

		if (sizeof($this->expired))
		{
			foreach ($this->expired as $id => $key)
			{
				$kill[] = $key;
			}

			vb_optimise::stat(1);
		}
		$this->expired = array();

		if (sizeof($kill))
		{
			$cache = vb_optimise::$cache->vbcache_getindex();

			foreach ($kill as $key)
			{
				unset($cache[$key]);
			}

			vb_optimise::$cache->set('vb.cache.index', $cache);
		}

		parent::shutdown();
	}
}