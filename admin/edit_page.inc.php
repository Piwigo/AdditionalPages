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
    pwg_query('UPDATE '.CONFIG_TABLE.' SET value = "'.addslashes(serialize($conf['additional_pages'])).'" WHERE param = "additional_pages";');
  }
  redirect($my_base_url.'&page_deleted=');
}

$q = 'SELECT id , lang , title , content , users , groups , permalink
FROM ' . ADD_PAGES_TABLE . '
WHERE id = '.$_GET['edit'].';';

$edited_page = mysql_fetch_assoc(pwg_query($q));
$page_title = l10n('ap_modify');
$edited_page['homepage'] = $conf['additional_pages']['homepage'] == $edited_page['id'];

// Lien de suppression
$template->assign('delete', true);

include(AP_PATH.'admin/page_form.inc.php');

?>