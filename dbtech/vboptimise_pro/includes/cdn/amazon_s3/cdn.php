<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

if (!class_exists('S3'))
{
	require_once(DIR . '/dbtech/vboptimise_pro/includes/cdn/amazon_s3/S3.php');
}

class cdn_amazon_s3 extends vboptimise_cdn_model
{
	public $error = '';
	public $response = '';
	public $http = false;

	public function build_settings()
	{
		$this->settings = array(
			'access_key'	=> 'Amazon Access Key',
			'secret_key'	=> 'Amazon Secret Key',
			'bucket'	=> 'Amazon Bucket Name<dfn>You must create a Bucket within Amazon S3 and enter the name here, for example: mysitecdn</dfn>',
		);
	}

	public function check_connection()
	{
		// Instantiate the class
		$s3 = new S3($this->cdn_settings['access_key'], $this->cdn_settings['secret_key']);
		$s3->setExceptions();

		try
		{
			// List the buckets
			$buckets = $s3->listBuckets();
		}
		catch (S3Exception $e)
		{
			$this->error = '
				<pre>
					An unknown error occured when parsing the response from Amazon S3, the following response was returned from Amazon:<br />
					<hr />' . htmlspecialchars_uni($e->getMessage()) . '
				</pre>
			';
			return false;
		}

		// Check the bucket exists
		if (is_array($buckets))
		{
			foreach ($buckets as $i => $bucket)
			{
				if (trim($bucket) == '')
				{
					// Shouldn't happen, but meh.
					unset($buckets[$i]);
					continue;
				}

				if ($bucket == $this->cdn_settings['bucket'])
				{
					return true;
				}
			}
		}

		$this->error .= '<pre>The connection to Amazon S3 was successful and you were authenticated, but you do not appear to own the bucket "' . $this->cdn_settings['bucket'] . '".</pre>';
		if (count($all_buckets) > 0)
		{
			$this->error .= '<pre>vB Optimise was able to identify you have the following buckets available: ' . implode(', ', $buckets) . '</pre>';
		}
		else
		{
			$this->error .= '<pre>vB Optimise was unable to find any buckets for that account, please make sure you have created atleast one and it has permissions for the provided access/secret key.';
		}

		return false;
	}

	public function sync()
	{
		// Instantiate the class
		$s3 = new S3($this->cdn_settings['access_key'], $this->cdn_settings['secret_key'], true, $this->_getEndpoint());
		$s3->setExceptions();

		foreach ($this->upload as $upload)
		{
			if (function_exists('vbflush'))
			{
				vboptimise_cdn::sync_report('Uploading: ' . $upload . '...');
				vbflush();
			}

			try
			{
				// List the buckets
				$success = $s3->putObjectFile($upload, $this->cdn_settings['bucket'], $upload, S3::ACL_PUBLIC_READ);
			}
			catch (S3Exception $e)
			{
				vboptimise_cdn::sync_report('
					An unknown error occured when parsing the response from Amazon S3, the following response was returned from Amazon:<br />
					<hr />' . htmlspecialchars_uni($e->getMessage())
				);
				vbflush();
				continue;
			}

			if (!$success)
			{
				vboptimise_cdn::sync_report('The upload failed, but did not generate an error message.');
				vbflush();
			}
		}
	}

	public function _file_on_cdn($file = '')
	{
		static $files;

		if (isset($files))
		{
			return in_array($file, $files);
		}

		// Instantiate the class
		$s3 = new S3($this->cdn_settings['access_key'], $this->cdn_settings['secret_key'], true, $this->_getEndpoint());
		$s3->setExceptions();

		try
		{
			// List the buckets
			$files = $s3->getBucket($this->cdn_settings['bucket']);
		}
		catch (S3Exception $e)
		{
			$this->error = '
				<pre>
					An unknown error occured when parsing the response from Amazon S3, the following response was returned from Amazon:<br />
					<hr />' . htmlspecialchars_uni($e->getMessage()) . '
				</pre>
			';
			return false;
		}

		if (!is_array($files))
		{
			return false;
		}
		else
		{
			return in_array($file, $files);
		}
	}

	public function get_url()
	{
		return 'http://' . $this->cdn_settings['bucket'] . '.s3.amazonaws.com';
	}

	protected function _getEndpoint()
	{
		return '';
	}
}