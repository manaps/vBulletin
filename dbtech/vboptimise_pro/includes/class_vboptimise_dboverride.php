<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */
class vb_optimise_db
{
	protected $dbobject = null;
	protected $cache = array();
	protected $first = true;

	public function __construct($cache = array())
	{
		global $vbulletin;

		$this->dbobject = $vbulletin->db;
		$this->cache = $cache;
	}

	protected function restore()
	{
		global $vbulletin, $db;

		$vbulletin->db = $db = $this->dbobject;
		if (class_exists('vB') AND vB::$db)
		{
			vB::$db = $this->dbobject;
		}
		//unset($this);
	}

	/**
	* Methods to execute a query
	* * * * * * * * * * * * * * * * * * * * * *
	*/
	public function query_read($sql = '')
	{
		return $this->query($sql);
	}

	public function query_read_slave($sql = '')
	{
		return $this->query($sql);
	}

	public function query_first_slave($sql)
	{
		$this->restore();
		return $this->query($sql);
	}

	public function query($sql = '')
	{
		return $this->cache;
	}

	/**
	* Methods to retrieve results
	* * * * * * * * * * * * * * * * * * * * * *
	*/

	public function fetch_array(&$cache)
	{
		global $vbulletin;

		if ($this->first)
		{
			$item = current($cache);
			$this->first = false;
		}
		else
		{
			$item = @next($cache);
		}

		if (!$item || is_null($item))
		{
			unset($this->cache, $cache);
			$this->restore();
			return false;
		}

		return $item;
	}


	/**
	* Escapes a string to make it safe to be inserted into an SQL query
	*
	* @param	string	The string to be escaped
	*
	* @return	string
	*/
	function escape_string($string)
	{
		return $this->dbobject->escape_string($string);
	}

	function hide_errors()
	{
		return $this->dbobject->hide_errors();
	}

	function show_errors()
	{
		return $this->dbobject->show_errors();
	}

	function free_result($res)
	{
		return $this->dbobject->free_result($res);
	}

	function num_rows($res)
	{
		return $this->dbobject->num_rows($res);
	}

	function insert_id()
	{
		return $this->dbobject->num_rows();
	}

	function query_write($sql)
	{
		return $this->dbobject->query_write($sql);
	}
}