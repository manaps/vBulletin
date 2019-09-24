<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class vboptimise_cdn_model
{
	public $settings = array();

	protected $cdn_settings = array();
	protected $upload = array();
	protected $full = true;

	private $loaded_api = false;

	protected function container_name()
	{
		global $vbulletin;

		return $vbulletin->options['vbo_prefix'] . 'cdn_';
	}

	public function build_settings(){}

	public function load_api()
	{
		if (!$this->loaded_api)
		{
			$this->_load_api();
			$this->loaded_api = true;
		}
	}

	public function apply_settings($settings = array())
	{
		$this->cdn_settings = $settings;
	}

	public function check_connection()
	{
		return false;
	}

	public function assign_upload($file = '')
	{
		global $vbulletin;

		$this->upload[] = $file;
		$this->upload = array_unique($this->upload); // May occur with all the places icons/images are assigned

		$vbulletin->db->query_write("replace into " . TABLE_PREFIX . "vboptimise_cdn (cdn_file) values ('" . $vbulletin->db->escape_string($file) . "')");
	}

	public function sync(){}

	public function get_url()
	{
		return '';
	}

	public function changed_since_lastsync($file = '')
	{
		return intval(@filemtime($file)) >= intval(vboptimise_cdn::$settings['lastsync']);
	}

	public function file_on_cdn($file = '')
	{
		static $fetched_db;

		if (!isset($fetched_db))
		{
			global $vbulletin;

			$fetched_db = array();
			$fetch_cdn = $vbulletin->db->query_read_slave('select * from ' . TABLE_PREFIX . 'vboptimise_cdn');
			while ($cdn = $vbulletin->db->fetch_array($fetch_cdn))
			{
				$fetched_db[] = $cdn['cdn_file'];
			}
		}

		if (in_array($file, $fetched_db))
		{
			return true;
		}

		$api = $this->_file_on_cdn($file);

		if ($api)
		{
			global $vbulletin;

			$vbulletin->db->query_write("replace into " . TABLE_PREFIX . "vboptimise_cdn (cdn_file) values ('" . $vbulletin->db->escape_string($file) . "')");
		}

		return $api;
	}
}