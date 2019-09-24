<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

global $vbulletin;

if ($vbulletin->userinfo['userid'] > 0)
{
	$dismissed = vb_optimise::$cache->get('notices_' . $vbulletin->userinfo['userid']);

	if ((is_bool($dismissed) && $dismissed === false) || (is_string($dismissed) && trim($dismissed) == ''))
	{
		$dismissed = fetch_dismissed_notices();

		vb_optimise::report('Caching users dismissed notices.');
		vb_optimise::$cache->set('notices_' . $vbulletin->userinfo['userid'], $dismissed);
	}
	else
	{
		vb_optimise::report('Fetched Dismissed Notices from cache.');
		vb_optimise::stat(1);
	}
	
	
	foreach ($vbulletin->noticecache AS $noticeid => $notice)
	{
		foreach ($notice AS $criteriaid => $conditions)
		{
			switch ($criteriaid)
			{
				case 'dismissible':
				{
					if (!$conditions)
					{
						// It wasn't dismissible
						break;
					}

					if (is_array($dismissed) && in_array($noticeid, $dismissed))
					{
						// notice dismissed, dont let vb process it
						unset($vbulletin->noticecache[$noticeid]);
					}
					else
					{
						// notice should show, but remove the criteria to stop query
						unset($vbulletin->noticecache[$noticeid][$criteriaid]);
						$vbulletin->noticecache[$noticeid]['override_dismiss'] = true;
					}
				}
				break;
			}
		}
	}
}
?>