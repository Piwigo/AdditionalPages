<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

if (!is_numeric($_GET['edit']))
{
  die('Wrong identifier');
}

// Suppression
if (isset($_REQUEST['delete']) and isset($_GET['edit']))
{
	pwg_query('DELETE FROM ' . ADD_PAGES_TABLE . ' WHERE id = ' . $_GET['edit'] . ';');
  @unlink($conf['local_data_dir'].'/additional_pages_backup/' . $_GET['edit'] . '.txt');
  if ($conf['additional_pages']['homepage'] == $_GET['edit'])
  {
    $conf['additional_pages']['homepage'] = null;
    conf_update_param('additional_pages', pwg_db_real_escape_string(serialize($conf['additional_pages'])));
  }
  redirect($my_base_url.'&page_deleted=');
}

$q = 'SELECT id , lang , title , content , users , groups , level , permalink, standalone
FROM ' . ADD_PAGES_TABLE . '
WHERE id = '.$_GET['edit'].';';

$edited_page = pwg_db_fetch_assoc(pwg_query($q));
$page_title = l10n('ap_modify');
$edited_page['users'] = !empty($edited_page['users']) ? explode(',', $edited_page['users']) : array();
$edited_page['groups'] = !empty($edited_page['groups']) ? explode(',', $edited_page['groups']) : array();
$edited_page['homepage'] = $conf['additional_pages']['homepage'] == $edited_page['id'];
$edited_page['standalone'] = ($edited_page['standalone'] == 'true');

// Lien de suppression
$template->assign('delete', true);

include(AP_PATH.'admin/add_page.inc.php');

?>