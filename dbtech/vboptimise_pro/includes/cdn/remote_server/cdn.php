<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class cdn_remote_server extends vboptimise_cdn_model
{
	private $conn = false;

	public $error = '';

	public function build_settings()
	{
		$this->settings = array(
			'ftp_host'	=> 'Your FTP Host<dfn>Example: ftp.website.com</dfn>',
			'ftp_port'	=> 'Your FTP Port<dfn>This is normally 21.</dfn>',
			'ftp_username'	=> 'Your FTP Username',
			'ftp_password'	=> 'Your FTP Password',
			'ftp_path'	=> 'Your FTP Path<dfn>vB Optimise will upload content to this location, for example: /public_html/static/. This must have a trailing slash.</dfn>',
			'web_url'	=> 'Website URL<dfn>Enter the URL which links to the same location as the FTP Path, for example: http://website.com/static. This must <strong>not</strong> have a trailing slash.</dfn>',
		);
	}

	public function check_connection($disconnect = true)
	{
		$this->conn = @ftp_connect($this->cdn_settings['ftp_host'], intval($this->cdn_settings['ftp_port']));

		if (!$this->conn)
		{
			$this->error = '<pre>Unable to connect to remote host: ' . $this->cdn_settings['ftp_host'] . '</pre>';
			return false;
		}

		if (!@ftp_login($this->conn, $this->cdn_settings['ftp_username'], $this->cdn_settings['ftp_password']))
		{
			$this->error = '<pre>Could not login to remote host as user: ' . $this->cdn_settings['ftp_username'] . '</pre>';
			return false;
		}

		if ($disconnect)
		{
			@ftp_close($this->conn);
		}

		@ftp_pasv($this->conn, true);

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
		$this->check_connection(false);
	}

	public function sync()
	{
		foreach ($this->upload as $upload)
		{
			if (function_exists('vbflush'))
			{
				vboptimise_cdn::sync_report('Uploading: ' . $upload . '...');
				vbflush();
			}

			$this->upload($upload);
		}

		@ftp_close($this->conn);
	}

	private function upload($path = '', $file = false)
	{
		$this->prepare_upload();

		if (!$file)
		{
			$file = explode('/', str_replace('\\', '/', $path));
			$file = array_pop($file);
			$path = str_replace($file, '', $path);

			$this->upload($path, $file);

			return false;
		}

		$this->goto_dir($this->cdn_settings['ftp_path'] . $path);

		if ($read = @fopen($path . $file, 'r'))
		{
			if (!@ftp_fput($this->conn, $file, $read, FTP_BINARY))
			{
				echo 'Unable to upload file to remote server.';
				exit;
			}

			@fclose($read);
		}
	}

	private function goto_dir($path = '')
	{
		if (!@ftp_chdir($this->conn, $path))
		{
			$folders = explode('/', str_replace('\\', '/', $path));
			$build = array();

			foreach ($folders as $folder)
			{
				$build[] = $folder;
				$try = implode('/', $build);

				if ($try == '')
				{
					continue;
				}

				if (!@ftp_chdir($this->conn, $try))
				{
					if (!@ftp_mkdir($this->conn, $try))
					{
						echo 'Unable to create directory for upload! Directory: ' . $try . ' (Original Path Requested: ' . $path . ')';
						echo '<hr />';
						echo var_export($folders, true);
						exit;
					}
				}
			}
		}
	}

	public function get_url()
	{
		return $this->cdn_settings['web_url'];
	}

	public function _file_on_cdn($file = '')
	{
		static $items;

		if (!is_array($items))
		{
			$items = array();
		}

		$path = $file;
		$file = explode('/', str_replace('\\', '/', $file));
		$file = array_pop($file);
		$path = str_replace($file, '', $path);

		if (!isset($items[$path]))
		{
			$items[$path] = array();
			$this->prepare_upload();
	
			if (@ftp_chdir($this->conn, $this->cdn_settings['ftp_path'] . $path))
			{
				$items[$path] = @ftp_nlist($this->conn, $this->cdn_settings['ftp_path'] . $path);

				if (is_array($items[$path]))
				{
					// Some ftp servers can return the absolute path
					foreach ($items[$path] as $key => $ftpfile)
					{
						$items[$path][$key] = str_replace($path, '', $ftpfile);
					}
				}
			}
		}

		return in_array($file, $items[$path]) || in_array('/' . $file, $items[$path]);
	}
}