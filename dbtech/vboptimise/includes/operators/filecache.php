<?php
class vb_optimise_filecache extends vb_optimise_operator
{
	protected $cacheType = 'filecache';

	public function connect()
	{
		return true;
	}

	public function canwrite()
	{
		return is_writeable(DIR . '/dbtech/vboptimise/filecache');
	}

	public function _get($id = '')
	{
		$file = DIR . '/dbtech/vboptimise/filecache/' . md5($this->id($id)) . '.php';

		if (!file_exists($file))
		{
			return false;
		}

		if (!$handle = @fopen($file, 'rb'))
		{
			return false;
		}

		$contents = '';

		while (!feof($handle))
		{
			$contents .= fread($handle, 1024);
		}

		$item = trim(str_replace('<'.'?php exit; ?'.'>', '', $contents));
		@fclose($handle);

		return unserialize($item);
	}

	public function _set($id = '', $value = '')
	{
		global $vbulletin;

		if (!is_array($value) && trim($value) == '')
		{
			$value = '{VBO_BLANK}';
		}

		$file = DIR . '/dbtech/vboptimise/filecache/' . md5($this->id($id)) . '.php';
		$value = "<"."?"."php exit; ?".">\r\n" . serialize($value);

		if ($handle = @fopen($file, 'wb'))
		{
			@fputs($handle, $value, ((strlen($value) > 0)? strlen($value) : 1));
			@fclose($handle);
			@chmod($file, octdec($vbulletin->options['vbo_suphp']));
		}

		unset($id, $value, $file);
	}

	public function do_flush($silent = false)
	{
		$directory = DIR . '/dbtech/vboptimise/filecache';

		if (is_dir($directory))
		{
			$handle = opendir($directory);

			while (($filename = readdir($handle)) !== false)
			{
				if (preg_match("#\.php$#i", $filename) && !is_dir($directory . '/' . $filename))
				{
					@unlink($directory . '/' . $filename);
				}
			}

			closedir($handle);
		}
	}
}
?>