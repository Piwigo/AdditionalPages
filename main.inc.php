<?php
/*
Plugin Name: Additional Pages
Version: auto
Description: Add additional pages in menubar.
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=153
Author: P@t
Author URI: http://www.gauchon.com
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable, $conf;

define('AP_DIR' , basename(dirname(__FILE__)));
define('AP_PATH' , PHPWG_PLUGINS_PATH . AP_DIR . '/');
define('ADD_PAGES_TABLE' , $prefixeTable . 'additionalpages');

$conf['additional_pages'] = @unserialize($conf['additional_pages']);

if ($conf['additional_pages'] === false)
  include(AP_PATH.'admin/upgrade_from_21.php');

function additional_pages_admin_menu($menu)
{
    array_push($menu, array(
      'NAME' => 'Additional Pages',
      'URL' => get_admin_plugin_menu_link(AP_PATH . 'admin/admin.php')));
    return $menu;
}

function section_init_additional_page()
{
  global $tokens, $conf, $page;

  $page['ap_homepage'] = (count($tokens) == 1 and empty($tokens[0]));

  if (($tokens[0] == 'page' and !empty($tokens[1])) or ($page['ap_homepage'] and !is_null($conf['additional_pages']['homepage'])))
    include(AP_PATH . 'additional_page.php');

  if ($tokens[0] == 'additional_page' and !empty($tokens[1]))
    redirect(make_index_url().'/page/'.$tokens[1]);
}

include(AP_PATH . 'index_menu.php');

add_event_handler('get_admin_plugin_menu_links', 'additional_pages_admin_menu');
add_event_handler('loc_end_section_init', 'section_init_additional_page');

?>