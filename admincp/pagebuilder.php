<?php

error_reporting(E_NONE);

require_once('./global.php');

// some necessary functions
/**
 * @param $id int The page ID.
 */
function page_exists($id)
{
    global $vbulletin;

    $fetchPage = $vbulletin->db->query_read("
        SELECT `id` FROM `" . TABLE_PREFIX . "page_builder` WHERE `id` = '" . $vbulletin->db->escape_string($id) . "'
    ");

    if ($vbulletin->db->num_rows($fetchPage) < 1) {
        return false;
    }

    return true;
}

function fetch_page($id, $columns = [])
{
    global $vbulletin;

    if (empty($columns)) {
        $columns = '*';
    }

    if (is_array($columns)) {
        $columnsString = '`' . implode('`, `', $columns) . '`';
    } else {
        $columnsString = $columns;
    }

    $fetchPage = $vbulletin->db->query_read("
        SELECT " . $columnsString . "
        FROM   `" . TABLE_PREFIX . "page_builder`
        WHERE  `id` = '" . $vbulletin->db->escape_string($id) . "'
    ");

    return $vbulletin->db->fetch_array($fetchPage);
}

$do = ! empty($_GET['do']) ? trim($_GET['do']) : 'manage';

switch ($do) {
    default:
    case 'manage':

        print_cp_header($vbphrase['ps_page_builder'] . ': ' . $vbphrase['ps_page_builder_manage_pages']);
        print_table_start();

        $headers = [
            'Title',
            'Slug',
            'Created At',
            'Updated At',
        ];

        $headerCount = count($headers);

        print_table_header('Pages', $headerCount);
        print_cells_row($headers, true);

        $getPages = $vbulletin->db->query_read("
            SELECT   `id`,
                     `name`,
                     `slug`,
                     `created_at`,
                     `updated_at`
            FROM     `" . TABLE_PREFIX . "page_builder`
            ORDER BY `name` ASC
        ");

        if ($vbulletin->db->num_rows($getPages) < 1) {
            print_description_row('There are no pages yet.', false, $headerCount);
        } else {
            while ($page = $vbulletin->db->fetch_array($getPages)) {
                // created / updated times
                $createdAt = sprintf(
                    '%s %s',
                    vbdate($vbulletin->options['dateformat'], $page['created_at']),
                    vbdate($vbulletin->options['timeformat'], $page['created_at'])
                );

                $updatedAt = sprintf(
                    '%s %s',
                    vbdate($vbulletin->options['dateformat'], $page['updated_at']),
                    vbdate($vbulletin->options['timeformat'], $page['updated_at'])
                );

                // frontend slug
                $pageURL = sprintf(
                    '<a href="%s/page.php?slug=%s" target="_blank">%s</a>',
                    $vbulletin->options['bburl'],
                    $page['slug'],
                    $page['slug']
                );

                // edit page
                $editPageURL = sprintf(
                    '<a href="pagebuilder.php?do=edit&id=%s">%s</a>',
                    $page['id'],
                    $page['name']
                );

                print_cells_row([$editPageURL, $pageURL, $createdAt, $updatedAt]);
            }
        }

        print_table_footer();

        break;

    case 'edit':
        print_cp_header($vbphrase['ps_page_builder'] . ': ' . $vbphrase['ps_page_builder_edit_page']);

        $id = ! empty($_GET['id']) ? trim($_GET['id']) : false;

        if ($id === false) {
            print_cp_message('No ID was specified.');
        }

        // clean the input
        $vbulletin->input->clean_gpc('g', 'id', TYPE_INT);
        $id = $vbulletin->GPC['id'];

        if (! page_exists($id)) {
            print_cp_message('No page was found for the given ID.');
        }

        $page = fetch_page($id);

        // page output
        print_form_header('pagebuilder', 'update');
        print_table_header(sprintf('Editing Page: %s', $page['name']));

        print_input_row('Page Name', 'name', $page['name']);
        print_input_row('Page Slug', 'slug', $page['slug']);

        print_table_break();

        print_table_header('Template');
        print_yes_no_row('Use Template', 'use_template', $page['use_template']);
        print_input_row('Template Name', 'template_name', $page['template_name']);

        print_submit_row();

        print_table_footer();

        break;

}

print_cp_footer();