<?php
/*
Plugin Name: Additional Pages
Version: auto
Description: Add additional pages in menubar.
Plugin URI: http://phpwebgallery.net/ext/extension_view.php?eid=153
Author: P@t
Author URI: http://www.gauchon.com
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable, $conf;

define('AP_DIR' , basename(dirname(__FILE__)));
define('AP_PATH' , PHPWG_PLUGINS_PATH . AP_DIR . '/');
define('ADD_PAGES_TABLE' , $prefixeTable . 'additionalpages');

function additional_pages_admin_menu($menu)
{
    array_push($menu, array(
      'NAME' => 'Additional Pages',
      'URL' => get_admin_plugin_menu_link(AP_PATH . 'admin/admin.php')));
    return $menu;
}

function section_init_additional_page()
{
    global $tokens, $page;
    if ($tokens[0] == 'additional_page')
      $page['section'] = 'additional_page';
}

function index_additional_page()
{
    global $page;
    if (isset($page['section']) and $page['section'] == 'additional_page')
      include(AP_PATH . 'additional_page.php');
}

$ap_conf = explode ("," , $conf['additional_pages']);
if (isset($ap_conf[1]) and $ap_conf[1] == 'on' or is_admin())
{
  include(AP_PATH . 'index_menu.php');
}

add_event_handler('get_admin_plugin_menu_links', 'additional_pages_admin_menu');
add_event_handler('loc_end_section_init', 'section_init_additional_page');
add_event_handler('loc_end_index', 'index_additional_page');

?>