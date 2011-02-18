<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

if (!isset($edited_page))
{
  $edited_page = array();
  $edited_page['id'] = 0;
  $edited_page['homepage'] = false;
  $page_title = l10n('ap_create');
}

// Enregistrement
if (isset($_POST['save']))
{
  if (empty($_POST['title']))
  {
    array_push($page['errors'], l10n('ap_no_name'));
  }
  if (!empty($_POST['permalink']))
  {
    $permalink = trim($_POST['permalink'], ' /');
    $permalink = str_replace(array(' ', '/'), '_',$permalink);

    $query ='
SELECT id FROM '.ADD_PAGES_TABLE.'
WHERE permalink = "'.$permalink.'"
  AND id <> '.$edited_page['id'].'
;';
    $ids = array_from_query($query, 'id');
    if (!empty($ids))
    {
      array_push($page['errors'], sprintf(l10n('ap_permalink_already_used'), $permalink, $ids[0]));
    }
    $permalink = '"'.$permalink.'"';
  }
  else
  {
    $permalink = 'NULL';
  }

  $language = $_POST['lang'] != 'ALL' ? '"'.$_POST['lang'].'"' : 'NULL';
  $group_access = !empty($_POST['groups']) ? '"'.implode(',', $_POST['groups']).'"' : 'NULL';
  $standalone = isset($_POST['standalone']) ? '"true"' : '"false"';

  $user_access = 'NULL';
  if ($conf['additional_pages']['user_perm'])
  {
    $user_access = !empty($_POST['users']) ? '"'.implode(',', $_POST['users']).'"' : '"admin"';
  }

  if (empty($page['errors']))
  {
    if ($page['tab'] == 'edit_page')
    {
      $query = '
UPDATE '.ADD_PAGES_TABLE.'
SET lang = '.$language.',
  title = "'.$_POST['title'].'",
  content = "'.$_POST['ap_content'].'",
  users = '.$user_access.',
  groups = '.$group_access.',
  permalink = '.$permalink.',
  standalone = '.$standalone.'
WHERE id = '.$edited_page['id'] .'
;';
      pwg_query($query);
    }
    else
    {
      $query = 'SELECT MAX(ABS(pos)) AS pos FROM ' . ADD_PAGES_TABLE . ';';
      list($position) = array_from_query($query, 'pos');
      
      $query = '
INSERT INTO ' . ADD_PAGES_TABLE . ' ( pos , lang , title , content , users , groups , permalink, standalone)
VALUES ('.($position+1).' , '.$language.' , "'.$_POST['title'].'" , "'.$_POST['ap_content'].'" , '.$user_access.' , '.$group_access.' , '.$permalink.' , '.$standalone.');';
      pwg_query($query);
      $edited_page['id'] = mysql_insert_id();
    }

    // Homepage
    if (isset($_POST['homepage']) xor $conf['additional_pages']['homepage'] == $edited_page['id'])
    {
      $conf['additional_pages']['homepage'] = isset($_POST['homepage']) ? $edited_page['id'] : null;
      conf_update_param('additional_pages', pwg_db_real_escape_string(serialize($conf['additional_pages'])));
    }

    // Enregistrement du fichier de sauvegarde
    mkgetdir($conf['local_data_dir'], MKGETDIR_DEFAULT&~MKGETDIR_DIE_ON_ERROR);
    mkgetdir($conf['local_data_dir'].'/additional_pages_backup', MKGETDIR_DEFAULT&~MKGETDIR_DIE_ON_ERROR);
    $sav_file = @fopen($conf['local_data_dir'].'/additional_pages_backup/' . $edited_page['id'] . '.txt', "w");
    @fwrite($sav_file, "Title: ".$_POST['title']."
Permalink: ".$_POST['permalink']."
Language: ".$_POST['lang']."

" . $_POST['ap_content']);
    @fclose($sav_file);

    if (isset($_GET['redirect']))
    {
      redirect(make_index_url() . '/page/' . $edited_page['id']);
    }
    redirect($my_base_url.'&page_saved=');
  }

  $edited_page['title'] = stripslashes($_POST['title']);
  $edited_page['permalink'] = $_POST['permalink'];
  $edited_page['lang'] = $_POST['lang'];
  $edited_page['content'] = stripslashes($_POST['ap_content']);
  $edited_page['groups'] = !empty($_POST['groups']) ? trim($group_access, '"') : '';
  $edited_page['users'] = !empty($_POST['users']) ? trim($user_access, '"') :  '';
  $edited_page['homepage'] = isset($_POST['homepage']);
  $edited_page['standalone'] = isset($_POST['standalone']);
}

// Selection des langues
$options['ALL'] = l10n('ap_all_lang');
$selected = 'ALL';
foreach (get_languages() as $language_code => $language_name)
{
  $options[$language_code] = $language_name;
  if (isset($edited_page['lang']) and $edited_page['lang'] == $language_code)
  {
    $selected = $language_code;
  }
}
$template->assign('lang', array(
  'OPTIONS' => $options,
  'SELECTED' => $selected));

// Selection des groupes
if ($conf['additional_pages']['group_perm'])
{
	include_once(AP_PATH . 'admin/functions_groups.php');
  $groups = !empty($edited_page['groups']) ? explode(',', $edited_page['groups']) : array();
	$template->assign('GROUPSELECTION', get_html_groups_selection(get_all_groups(), 'groups', $groups));
}

// Selection des utilisateurs
if ($conf['additional_pages']['user_perm'])
{
  $selected_users = array('guest', 'generic', 'normal');
  if (isset($_GET['edit']) and isset($edited_page['users']))
  {
    $selected_users = explode(',', $edited_page['users']);
  }

	$template->assign('user_perm', array(
      'GUEST' => in_array('guest', $selected_users) ? 'checked="checked"' : '',
      'GENERIC' => in_array('generic', $selected_users) ? 'checked="checked"' : '',
      'NORMAL' => in_array('normal', $selected_users) ? 'checked="checked"' : ''
    )
  );
}

// Chargement des données pour l'édition
if ($page['tab'] == 'edit_page')
{
  $template->assign(array(
    'NAME' => $edited_page['title'],
    'PERMALINK' => $edited_page['permalink'],
    'HOMEPAGE' => $edited_page['homepage'],
    'STANDALONE' => $edited_page['standalone'],
    'CONTENT' => $edited_page['content']));
}

// Parametrage du template
$template->assign('AP_TITLE', $page_title);

$template->set_filename('plugin_admin_content', dirname(__FILE__) . '/template/add_page.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>