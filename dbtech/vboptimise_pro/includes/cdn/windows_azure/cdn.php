<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class cdn_windows_azure extends vboptimise_cdn_model
{
	private $client = false;
	private $cont = false;

	public $error = '';

	public function build_settings()
	{
		$this->settings = array(
			'blobname'	=> 'Your Windows Azure Blob Name<dfn>If your blob url is <strong>myblob</strong>.blob.core.windows.net, the name is "myblob".</dfn>',
			'primarykey'	=> 'Your Windows Azure Primary Key',
			'blobhost'	=> 'Your Windows Azure Host<dfn>You can use the default Blob URL/Azure CDN Url or your own that you\'ve assigned to Windows Azure. If you are unsure simply enter "blob.core.windows.net".</dfn>',
		);
	}

	public function check_connection()
	{
		$this->load_api();

		if (!isset($this->cdn_settings['blobhost']) || trim($this->cdn_settings['blobhost']) == '')
		{
			$this->cdn_settings['blobhost'] = 'blob.core.windows.net';
		}

		try
		{
			$this->client = new Microsoft_WindowsAzure_Storage_Blob($this->cdn_settings['blobhost'], $this->cdn_settings['blobname'], $this->cdn_settings['primarykey']);
			$this->client->listContainers(); // something to trigger errors on authentification should we have invalid values
		}
		catch (Exception $e)
		{
			$this->error = '<pre>' . $e->getMessage() . '</pre>';
			return false;
		}

		return true;
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

		// Either Open or Create Container
		try
		{
			$this->cont = $this->client->getContainer($this->container_name());
		}
		catch (Exception $e)
		{
			// Probably doesn't exist, create it!
			$this->cont = $this->client->createContainer($this->container_name());
		}

		$this->client->setContainerAcl($this->container_name(), Microsoft_WindowsAzure_Storage_Blob::ACL_PUBLIC); // make the container public
	}

	public function sync()
	{
		try
		{
			$this->prepare_upload();

			foreach ($this->upload as $upload)
			{
				if (function_exists('vbflush'))
				{
					vboptimise_cdn::sync_report('Uploading: ' . $upload . '...');
					vbflush();
				}

				$ctype = custom_mime_content_type(DIR . '/' . $upload);

				$obj = $this->client->putBlob($this->container_name(), $upload, DIR . '/' . $upload, array(), array(
					'Content-Type'	=> $ctype,
				));
			}
		}
		catch (Exception $e)
		{
			die('vB Optimise experienced an issue uploading to your cdn: ' . $e->getMessage());

			return false;
		}
	}

	public function get_url()
	{
		$this->prepare_upload();

		if ($this->cont)
		{
			return $this->client->getBaseUrl() . '/' . $this->container_name();
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
		$current = getcwd();
		chdir(DIR . '/dbtech/vboptimise_pro/includes/cdn/windows_azure/');
		require_once(DIR . '/dbtech/vboptimise_pro/includes/cdn/windows_azure/Microsoft/WindowsAzure/Storage/Blob.php');
		chdir($current);
	}

	protected function container_name()
	{
		static $cont = false;

		if (!$cont)
		{
			$cont = preg_replace('#-{2,}#', '-', 'azure-' . str_replace('cdn-', '', preg_replace('#[^a-z0-9\-]#i', '-', parent::container_name())) . '-cdn');
		}

		return $cont;
	}
}