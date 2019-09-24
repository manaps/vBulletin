<?php
if (!defined('vboptimise'))
{
	die('Cannot access directly.');
}

if (!isset($vbulletin))
{
	global $vbulletin, $templateassoc, $globaltemplates;
}

if (empty($templateassoc))
{
	$templateassoc = unserialize($argumentb);
}

if ($vbulletin->options['legacypostbit'] AND in_array('postbit', $argument))
{
	$templateassoc['postbit'] = $templateassoc['postbit_legacy'];
}

if ($vbulletin->options['vbo_showresource'] && in_array('FORUMHOME', $argument))
{
	$argument[] = 'forumhome_vbo';
}

if (defined('CMS_SCRIPT'))
{
	$add = array(
		'editor_clientscript',
		'editor_jsoptions_font',
		'editor_jsoptions_size',
		'editor_smilie',
		'editor_smilie_category',
		'editor_smilie_row',
		'editor_smiliebox',
		'editor_toolbar_colors',
		'editor_toolbar_fontname',
		'editor_toolbar_fontsize',
		'editor_toolbar_on',
		'vbcms_page',
		'vbcms_searchresult_post',
		'vbcms_toolbar',
		'vbcms_widget_categorynavall_page',
		'vbcms_widget_newarticles_page',
		'vbcms_widget_recentblogposts_page',
		'vbcms_widget_recentcmscomments_page',
		'vbcms_widget_recentforumposts_page',
		'vbcms_widget_sectionnavext_page ',
		'tagbit_wrapper',
		'vbcms_article_css',
		'vbcms_comments_page',
		'vbcms_content_article_page',
		'vbcms_grid_5',
		'newpost_disablesmiliesoption',
		'vbcms_widget_sectionnavext_page',
		'vbcms_widget_static_page',
		'vbcms_widget_rss_page',
		'vbcms_grid_4',
		'vbcms_grid_6',
		'vbcms_content_list',
		'vbcms_content_article_preview',
		'vbcms_content_section_page',
		'vbcms_content_section_type4',
		'vbcms_grid_1',
		'newpost_attachment',
		'vbcms_article_editor',
		'vbcms_content_article_inline',
		'vbcms_content_edit_editbar',
		'vbcms_edit_block',
		'vbcms_edit_metadataeditor',
		'vbcms_edit_page',
		'vbcms_edit_publisher',
		'vbcms_editor_toolbar_on',
		'vbcms_content_section_type2',
		'vbcms_content_section_type1',
		'bbcode_video',
		'vbcms_content_section_type5',
		'vbcms_searchresult_newcomment',
		'postbit_attachment',
		'vbcms_widget_column',
		'vbcms_searchresult_article',
		'vbcms_widget_categorynavbu_page',
		'vbcms_widget_column',
		'ad_showthread_firstpost_sig',
		'ad_showthread_firstpost_start',
		'ad_thread_first_post_content',
		'blog_postbit_blog_this_post',
		'postbit_ip',
		'postbit_onlinestatus',
		'postbit_reputation',
		'postbit_wrapper',
		'vbcms_comments_block',
		'vbcms_comments_detail',
		'vbcms_postbit_legacy',
		'vbcms_searchresult_article',
		'vbcms_widget_categorynavbu_page',
		'vbcms_widget_column',
		'bbcode_quote',
	);

	define('VBO_CMS_TEMPLATES', implode('|', $add));

	foreach ($add as $cmscache)
	{
		if (!in_array($cmscache, $argument))
		{
			$argument[] = $cmscache;
		}
	}

	unset($add);
}

foreach ($argument AS $template)
{
	$tem = intval($templateassoc["$template"]);

	if ($tem > 0)
	{
		$templateids[] = $tem;
	}
}

if (!empty($templateids))
{
	$templates_database = array();
	$templates_cache = array();

	foreach ($templateids as $templateid)
	{
		$templates_cache[$templateid] = vb_optimise::$cache->get('template_' . $templateid);

		if ($templates_cache[$templateid] == false)
		{
			$templates_database[] = $templateid;
			unset($templates_cache[$templateid]);
		}
	}

	// Remove any "dead" templates from bad mods
	foreach ($templates_database as $tkey => $tdb)
	{
		if ($tdb == '0' || $tdb == 0)
		{
			unset($templates_database[$tkey]);
		}
	}

	vb_optimise::report(' Got ' . count($templates_cache) . ' templates from cache, ' . count($templates_database) . ' have to be fetched from database.');

	if (!empty($templates_database) && trim(implode(',', $templates_database)) != '')
	{
		vb_optimise::report('Querying templates to cache.');

		$fetch_templates = $vbulletin->db->query_read_slave("
			SELECT templateid, title, template
			FROM " . TABLE_PREFIX . "template
			WHERE templateid IN (" . implode(',', $templates_database) . ")
		");

		while ($fetched_template = $vbulletin->db->fetch_array($fetch_templates))
		{
			$templates_cache[$fetched_template['templateid']] = $fetched_template;
			vb_optimise::$cache->set('template_' . $fetched_template['templateid'], $fetched_template);
		}
		$vbulletin->db->free_result($fetch_templates);
	}
	else
	{
		vb_optimise::stat(1); // Technically on CMS pages we're saving a lot more too, but we can't really calculate that here
	}

	if (is_array($templates_cache))
	{
		foreach ($templates_cache as $cache_tempid => $cache_temp)
		{
			$vbulletin->templatecache[$cache_temp['title']] = $cache_temp['template'];
		}
	}

	$argument = array();

	unset($templateids, $templates_cache, $templates_database);
}

$vbulletin->bbcode_style = array(
		'code'  => &$templateassoc['bbcode_code_styleid'],
		'html'  => &$templateassoc['bbcode_html_styleid'],
		'php'   => &$templateassoc['bbcode_php_styleid'],
		'quote' => &$templateassoc['bbcode_quote_styleid']
);
?>