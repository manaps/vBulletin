<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class cdn_origin_pull_or_mirror extends vboptimise_cdn_model
{
	public $keep_css_cdn = true;

	public function build_settings()
	{
		$this->settings = array(
			'mirror_url'	=> 'Your Origin Pull/Mirror URL<dfn>This is the URL of your Origin Pull or Mirror server, it must <strong>not</strong> end with a trailing slash!</dfn>',
		);
	}

	public function check_connection($disconnect = true)
	{
		return true;
	}

	public function sync()
	{
		// nothing to do
	}

	public function get_url()
	{
		return $this->cdn_settings['mirror_url'];
	}

	public function _file_on_cdn($file = '')
	{
		return true;
	}
}