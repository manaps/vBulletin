<?php
if (substr($type, 0, strlen('thanks')) == 'thanks')
{
	// Fetch AOP time
	self::fetch_aop($type, 0);
	
	if (!isset($args['types']) OR !is_int($args['types']))
	{
		// Ensure this is an int
		$args['types'] = 0;
	}
	
	if (self::$vbulletin->options['dbtech_thanks_shoutbox'])
	{
		// Grabbing Thanks
		$args['types'] += 16;
	}
	
	// Override type
	$type = 'shouts';
}
else
{
	if (!is_array($args['excludetypes']))
	{
		// Ensure this is an array
		$args['excludetypes'] = array();
	}
	
	// Exclude our tag types
	$args['excludetypes'][] = 16;
}
?>