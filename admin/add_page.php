<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
load_language('plugin.lang', AP_PATH);
$ap_conf = explode(',' , $conf['additional_pages']);
$edited_page = array();
$page_title = l10n('ap_create');

if (isset($_GET['saved']) and isset($_GET['edit']))
{
	array_push($page['infos'], sprintf(l10n('ap_saved_page') , $_GET['edit']));
}

// Suppression
if (isset($_POST['delete']) and isset($_GET['edit']))
{
	pwg_query('DELETE FROM ' . ADD_PAGES_TABLE . ' WHERE id = ' . $_GET['edit'] . ';');
  array_push($page['infos'], l10n('ap_deleted_page'));
  @unlink(AP_PATH . 'backup/' . $_GET['edit'] . '.txt');
  return;
}

// Enregistrement
if (isset($_POST['save']))
{
  $group_access = (!empty($_POST['groups']) ? implode(',', $_POST['groups']) : '');
  $user_access = (!empty($_POST['users']) ? implode(',', $_POST['users']) : '');
  if (empty($_POST['name']))
  {
    array_push($page['errors'], l10n('ap_no_name'));
    $edited_page['title'] = '';
    $edited_page['pos'] = $_POST['pos'];
    $edited_page['text'] = stripslashes($_POST['ap_content']);
    $edited_page['group'] = (!empty($_POST['groups']) ? $_POST['groups'] : array());
    $edited_page['user'] = (!empty($_POST['users']) ? $_POST['users'] :  array());
  }
  else
  {
    $PageTitle = $_POST['name'];
    if (!empty($group_access))
    {
      $PageTitle .= '/group_id=' . $group_access ;
    }
    if ($ap_conf[7] == 'on')
    {
      $PageTitle .= '/user_id=' . $user_access ;
    }
    if (isset($_GET['edit']))
    {
      $next_element_id = $_GET['edit'];
      pwg_query('DELETE FROM ' . ADD_PAGES_TABLE . ' WHERE id = ' . $_GET['edit'] . ';');
    }
    else
    {
      $q = 'SELECT IF(MAX(id)+1 IS NULL, 1, MAX(id)+1) AS next_element_id  FROM ' . ADD_PAGES_TABLE . ' ;';
      list($next_element_id) = mysql_fetch_array(pwg_query($q));
    }
    if ($_POST['pos'] == '') $_POST['pos'] = 'NULL';
    $q = 'INSERT INTO ' . ADD_PAGES_TABLE . ' ( id , pos , lang , title , text )
VALUES (' . $next_element_id . ' , ' . $_POST['pos'] . ' , "' . $_POST['lang'] . '" , "' . $PageTitle . '" , "' . $_POST['ap_content'] . '");';
    pwg_query($q);

    // Enregistrement du fichier de sauvegarde
    $sav_file = @fopen(AP_PATH . 'backup/' . $next_element_id . '.txt', "w");
    @fwrite($sav_file, "Title: " . $_POST['name'] . "
Position: " . $_POST['pos'] . "
Language: " . $_POST['lang'] . "

" . $_POST['ap_content']);
    @fclose($sav_file);

    if (isset($ap_conf[4]) and $ap_conf[4] == 'on')
    {
      redirect(get_root_url() . 'index.php?/additional_page/' . $next_element_id);
    }
    else
    {
      redirect(get_root_url() . 'admin.php?page=plugin&section=' . AP_DIR . '%2Fadmin%2Fadd_page.php&saved=1&edit=' . $next_element_id);
    }
  }
}

// Chargement des données
if (isset($_GET['edit']))
{
  $q = 'SELECT id , pos , lang , title , text
FROM ' . ADD_PAGES_TABLE . '
WHERE id = ' . $_GET['edit'] . ';';
  $edited_page = mysql_fetch_assoc(pwg_query($q));
  $page_title = l10n('ap_modify');
  // Utilisateurs autorisés
  if (strpos($edited_page['title'] , '/user_id='))
  {
    $array = explode('/user_id=' , $edited_page['title']);
    $edited_page['title'] = $array[0];
    $edited_page['user'] = explode(',', $array[1]);
  }
  // Groupes autorisés
  if (strpos($edited_page['title'] , '/group_id='))
  {
    $array = explode('/group_id=' , $edited_page['title']);
    $edited_page['title'] = $array[0];
    $edited_page['group'] = explode(',', $array[1]);
  }
  // Lien de suppression
  $template->assign('delete', true);
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
if (isset($ap_conf[6]) and $ap_conf[6] == 'on')
{
	include_once(AP_PATH . 'admin/functions_groups.php');
	$template->assign('GROUPSELECTION', get_html_groups_selection(get_all_groups(), 'groups', (!empty($edited_page['group']) ? $edited_page['group'] : array())));
}

// Selection des utilisateurs
if (isset($ap_conf[7]) and $ap_conf[7] == 'on')
{
	$selected_users = (isset($edited_page['user']) ? $edited_page['user'] : array('guest', 'generic', 'normal'));
	$template->assign('user_perm', array(
    'GUEST' => (in_array('guest', $selected_users) ? 'checked="checked"' : ''),
		'GENERIC' => (in_array('generic', $selected_users) ? 'checked="checked"' : ''),
		'NORMAL' => (in_array('normal', $selected_users) ? 'checked="checked"' : '')));
}

// Chargement des données pour l'édition
if (!empty($edited_page))
{
  $template->assign(array(
    'NAME' => $edited_page['title'],
    'POS' => $edited_page['pos'],
    'CONTENT' => $edited_page['text']));
}

// Parametrage du template
$template->assign('AP_TITLE', $page_title);

$template->set_filename('plugin_admin_content', dirname(__FILE__) . '/add_page.tpl');
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>