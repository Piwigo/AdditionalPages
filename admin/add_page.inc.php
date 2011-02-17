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
    $permalink = $_POST['permalink'];
    $sanitized_permalink = preg_replace( '#[^a-zA-Z0-9_/-]#', '' ,$permalink);
    $sanitized_permalink = trim($sanitized_permalink, '/');
    $sanitized_permalink = str_replace('//', '/', $sanitized_permalink);
    if ( $sanitized_permalink != $permalink or preg_match( '#^(\d)+(-.*)?$#', $permalink) )
    {
      array_push($page['errors'], l10n('The permalink name must be composed of a-z, A-Z, 0-9, "-", "_" or "/". It must not be numeric or start with number followed by "-"'));
    }
    $query ='
SELECT id FROM '.ADD_PAGES_TABLE.'
WHERE permalink = "'.$permalink.'"
  AND id <> '.$edited_page['id'].'
;';
    $ids = array_from_query($query, 'id');
    if (!empty($ids))
    {
      array_push($page['errors'], sprintf(l10n('Permalink %s is already used by additional page %s'), $permalink, $ids[0]));
    }
    $permalink = '"'.$permalink.'"';
  }
  else
  {
    $permalink = 'NULL';
  }

  $language = $_POST['lang'] != 'ALL' ? '"'.$_POST['lang'].'"' : 'NULL';
  $group_access = !empty($_POST['groups']) ? '"'.implode(',', $_POST['groups']).'"' : 'NULL';
  $user_access = !empty($_POST['users']) ? '"'.implode(',', $_POST['users']).'"' : 'NULL';

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
  permalink = '.$permalink.'
WHERE id = '.$edited_page['id'] .'
;';
      pwg_query($query);
    }
    else
    {
      $query = 'SELECT MAX(ABS(pos)) AS pos FROM ' . ADD_PAGES_TABLE . ';';
      list($position) = array_from_query($query, 'pos');
      
      $query = '
INSERT INTO ' . ADD_PAGES_TABLE . ' ( pos , lang , title , content , users , groups , permalink)
VALUES ('.($position+1).' , '.$language.' , "'.$_POST['title'].'" , "'.$_POST['ap_content'].'" , '.$user_access.' , '.$group_access.' , '.$permalink.');';
      pwg_query($query);
      $edited_page['id'] = mysql_insert_id();
    }

    // Homepage
    if (isset($_POST['homepage']) xor $conf['additional_pages']['homepage'] == $edited_page['id'])
    {
      $conf['additional_pages']['homepage'] = isset($_POST['homepage']) ? $edited_page['id'] : null;
      pwg_query('UPDATE '.CONFIG_TABLE.' SET value = "'.addslashes(serialize($conf['additional_pages'])).'" WHERE param = "additional_pages";');
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
  $edited_page['content'] = stripslashes($_POST['ap_content']);
  $edited_page['groups'] = !empty($_POST['groups']) ? trim($group_access, '"') : '';
  $edited_page['users'] = !empty($_POST['users']) ? trim($user_access, '"') :  '';
  $edited_page['homepage'] = isset($_POST['homepage']);
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
  if (isset($_GET['edit']))
	  $selected_users = isset($edited_page['users']) ? explode(',', $edited_page['users']) : array();
  else
    $selected_users = array('guest', 'generic', 'normal');

	$template->assign('user_perm', array(
    'GUEST' => (in_array('guest', $selected_users) ? 'checked="checked"' : ''),
		'GENERIC' => (in_array('generic', $selected_users) ? 'checked="checked"' : ''),
		'NORMAL' => (in_array('normal', $selected_users) ? 'checked="checked"' : '')));
}

// Chargement des données pour l'édition
if ($page['tab'] == 'edit_page')
{
  $template->assign(array(
    'NAME' => $edited_page['title'],
    'PERMALINK' => $edited_page['permalink'],
    'HOMEPAGE' => $edited_page['homepage'],
    'CONTENT' => $edited_page['content']));
}

// Parametrage du template
$template->assign('AP_TITLE', $page_title);

$template->set_filename('plugin_admin_content', dirname(__FILE__) . '/template/add_page.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>