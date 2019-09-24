<?php
/****
 * vB Optimise
 * Copyright 2010; Deceptor
 * All Rights Reserved
 * Code may not be copied, in whole or part without written permission
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class vB_Template
{
	public static function create($template = '')
	{
		return new vB_Template_Object($template);
	}
}

class vB_Template_Object extends vB_Template
{
	protected $template = '';
	protected $registered = array();

	public function __construct($template = '')
	{
		$this->template = $template;
	}

	public function register($var = '', $value)
	{
		$this->registered[$var] = $value;
	}

	public function render()
	{
		eval('$template = "' . fetch_template($this->template) . '";');

		if (sizeof($this->registered))
		{
			foreach ($this->registered as $var => $value)
			{
				$template = str_replace('{vb:raw ' . $var . '}', $value, $template);
			}
		}

		return $template;
	}
}