<?php

error_reporting(E_ALL & ~E_NOTICE);

define('THIS_SCRIPT', 'page_builder');
define('CSRF_PROTECTION', true);

// get special phrase groups
$phrasegroups = array();

// get special data templates from the datastore
$specialtemplates = array();

// pre-cache templates used by all actions
$globaltemplates = array('TEST',
);

// pre-cache templates used by specific actions
$actiontemplates = array();

require_once('./global.php');

// we need a slug to begin with
if (empty($_GET['slug'])) {
    standard_error("No page slug was provided.");
}

// clean the slug
$vbulletin->input->clean_gpc('g', 'slug', TYPE_STR);
$slug = $vbulletin->GPC['slug'];

// check if slug has been set up as a page
$checkPage = $vbulletin->db->query_read("
    SELECT `id`,
           `name`,
           `use_template`,
           `template_name`,
           `meta_title`,
           `meta_description`,
           `meta_keywords`,
           `content`
    FROM   `" . TABLE_PREFIX . "page_builder`
    WHERE  `slug` = '" . $vbulletin->db->escape_string($slug) . "'
    LIMIT  1
");

// if there's no pages for that slug, show an error message
if ($vbulletin->db->num_rows($checkPage) === 0) {
    standard_error("No pages exist for that slug.");
}

// ok, the page exists, let's now display it with a custom template if necessary, or fall back to our built in template
$page = $vbulletin->db->fetch_array($checkPage);

$navbits = construct_navbits(['' => 'Test Page']);
$navbar = render_navbar_template($navbits);

$page['content'] = replace_content_vars($page['content']);

if (boolval($page['use_template']) === true && ! empty($page['template_name'])) {
    // we're going to display a custom template, as long as it exists
    $templater = vB_Template::create($page['template_name']);

} else {
    // use the built in page template
    $templater = vB_Template::create('page_builder_shell');
    $templater->register_page_templates();
    $templater->register('navbar', $navbar);
    $templater->register('page', $page);
    print_output($templater->render());
}

function replace_content_vars($content)
{
    global $vbulletin;

    // username
    $content = preg_replace('/{username}/', $vbulletin->userinfo['username'], $content);

    // musername
    $content = preg_replace('/{musername}/', fetch_musername($vbulletin->userinfo), $content);

    // bbtitle
    $content = preg_replace('/{bbtitle}/', $vbulletin->options['bbtitle'], $content);

    // bburl
    $content = preg_replace('/{bburl}/', $vbulletin->options['bburl'], $content);

    return $content;
}