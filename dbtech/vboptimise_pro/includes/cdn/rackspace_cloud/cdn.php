<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class cdn_rackspace_cloud extends vboptimise_cdn_model
{
	private $auth = false;
	private $conn = false;
	private $cont = false;
	private $use_ca_bundle = false;

	public $error = '';

	public function build_settings()
	{
		$this->settings = array(
			'username'	=> 'Your Rackspace Cloud CDN Username',
			'apikey'	=> 'Your Rackspace Cloud CDN API Key',
		);
	}

	public function check_connection()
	{
		$this->load_api();

		try
		{
			$this->auth = new CF_Authentication($this->cdn_settings['username'], $this->cdn_settings['apikey']);
			return $this->auth->authenticate();
		}
		catch (Exception $x)
		{
			// We may have an outdated CA bundle, use the one provided and see if that works
			try
			{
				$this->use_ca_bundle = true;
				$this->auth = new CF_Authentication($this->cdn_settings['username'], $this->cdn_settings['apikey']);
				$this->auth->ssl_use_cabundle();
				return $this->auth->authenticate();
			}
			catch (Exception $e)
			{
				$this->use_ca_bundle = false;
				$this->error = '<pre>' . $e . '</pre>';
				return false;
			}
		}
	}

	private function prepare_upload()
	{
		static $prepared;

		if ($prepared === true)
		{
			return false;
		}

		$prepared = true;
		$this->check_connection();

		// Establish Connection
		$this->conn = new CF_Connection($this->auth);

		if ($this->use_ca_bundle)
		{
			$this->conn->ssl_use_cabundle();
		}

		// Either Open or Create Container
		try
		{
			$this->cont = $this->conn->get_container($this->container_name());
		}
		catch (Exception $e)
		{
			// Probably doesn't exist, create it!
			$this->cont = $this->conn->create_container($this->container_name());
		}
	}

	public function sync()
	{
		echo '<!--';

		try
		{
			foreach ($this->upload as $upload)
			{
				if (function_exists('vbflush'))
				{
					echo '-->';
					vboptimise_cdn::sync_report('Uploading: ' . $upload . '...');
					vbflush();
					echo '<!--';
				}

				$this->prepare_upload();
				$obj = $this->cont->create_object($upload);
				$obj->content_type = custom_mime_content_type(DIR . '/' . $upload);
				$obj->last_modified = TIMENOW;
				$obj->metadata = array('lastmod' => TIMENOW);
				$obj->load_from_filename(DIR . '/' . $upload);
				$obj->sync_metadata();
			}
		}
		catch (Exception $e)
		{
			echo '-->';

			return false;
		}

		echo '-->';
	}

	public function get_url()
	{
		$this->prepare_upload();
		if ($this->cont)
		{
			return $this->cont->make_public();
		}

		return '';
	}

	public function _file_on_cdn($file = '')
	{
		static $existing_items;

		if (!is_array($existing_items))
		{
			$this->prepare_upload();
			$existing_items = $this->cont->list_objects();
		}

		return in_array($file, $existing_items);
	}

	protected function _load_api()
	{
		require_once(DIR . '/dbtech/vboptimise_pro/includes/cdn/rackspace_cloud/cloudfiles.php');
	}
}