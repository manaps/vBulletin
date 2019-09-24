<?php if (!defined('VB_ENTRY')) die('Access denied.');
/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.5 - Licence Number LC449E5B7C
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2019 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| #        www.vbulletin.com | www.vbulletin.com/license.html        # ||
|| #################################################################### ||
\*======================================================================*/

/**
 * CMS Layout View
 * View for rendering a page layout, it's grid html or customised template, and the
 * content and widget views that the layout contains.
 *
 * @package vBulletin
 * @author vBulletin Development Team
 * @version $Revision: 92140 $
 * @since $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
 * @copyright vBulletin Solutions Inc.
 */
class vBCms_View_Layout extends vB_View
{
	/**
	 * Widget locations
	 *
	 * @var array
	 */
	public $widgetlocations = array();



	/*Render========================================================================*/

	/**
	 * Prepare the widget block locations and other info.
	 */
	protected function prepareProperties()
	{
		// Prepare widget and content blocks
		$this->prepareBlocks();
	}


	/**
	 * Arranges widgets and content into the layout block locations.
	 */
	protected function prepareBlocks()
	{
		// Get widget columns and sort into block locations
		$blocks = array();

		$add_content = true;

		foreach ($this->widgetlocations AS $column => &$indices)
		{
			ksort($indices);
			foreach ($indices AS $index => $widgetid)
			{
				if (!isset($this->widgets[$widgetid]))
				{
					continue;
				}

				if ($add_content AND ($this->contentcolumn == $column) AND ($this->contentindex <= $index))
				{
					$blocks[$column][] = $this->content;
					$add_content = false;
				}

				$blocks[$column][] = $this->widgets[$widgetid];
			}
		}

		// Ensure the content was added
		if ($add_content)
		{
			$blocks[$this->contentcolumn][$this->contentindex] = $this->content;
		}


		ksort($blocks);
		foreach ($blocks AS $column => &$indices)
		{
			ksort($indices);
		}

		// Arrange widgets with column views
		$columns = array();
		foreach ($blocks AS $column => $widgets)
		{
			$widget_column = new vB_View('vbcms_widget_column');
			$widget_column->widgets = $widgets;
			$columns[$column] = $widget_column;
		}

		$this->column = $columns;

		unset($this->_properties['widgets']);
		unset($this->_properties['widgetlocations']);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 17:39, Sat Aug 3rd 2019 : $Revision: 92140 $
|| # $Date: 2016-12-30 20:26:15 -0800 (Fri, 30 Dec 2016) $
|| ####################################################################
\*======================================================================*/