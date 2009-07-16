<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template, $conf, $user;

load_language('plugin.lang.php', AP_PATH);

$ap_id = explode('additional_page/' , $_SERVER['REQUEST_URI']);
$ap_id = explode('&' , $ap_id[1]);
$ap_conf = explode ("," , $conf['additional_pages']);

// Récupération des données de la page
$q = 'SELECT title , pos , text
FROM ' . ADD_PAGES_TABLE . '
WHERE id = ' . $ap_id[0] . ';';
$result = mysql_fetch_assoc(pwg_query($q));

if (empty($result))
{
  page_not_found('This page does not exist', 'index.php?');
}

// Utilisateurs autorisés
if (strpos($result['title'] , 'user_id='))
{
  $array = explode('/user_id=' , $result['title']);
  $result['title'] = $array[0];
  $authorized_users = explode(',', $array[1]);
  if (!is_admin() and $ap_conf[7] == 'on' and !in_array($user['status'], $authorized_users))
  {
  	page_not_found('User not allowed', 'index.php?');
  }
}

// Groupe autorisé
if (strpos($result['title'] , 'group_id='))
{
  $array = explode('/group_id=' , $result['title']);
  $result['title'] = $array[0];
  $authorized_groups = $array[1];

  $q = 'SELECT *
FROM ' . USER_GROUP_TABLE . '
WHERE user_id = ' . $user['id'] . ' AND group_id IN (' . $authorized_groups . ');';
  $array = mysql_fetch_array(pwg_query($q));
  if (!is_admin() and $ap_conf[6] == 'on' and empty($array))
  {
  	page_not_found('User not allowed', 'index.php?');
  }
}

// Envoi de la page
$template->assign(array(
  'TITLE' => $result['title'],
  'PLUGIN_INDEX_CONTENT_BEGIN' => $result['text']));
if (isset($ap_conf[2]) and $ap_conf[2] == 'on')
{
  $template->assign('PLUGIN_INDEX_ACTIONS' , '
    <li><a href="' . make_index_url() . '" title="' . l10n('return to homepage') . '">
      <img src="' . $template->get_themeconf('icon_dir') . '/home.png" class="button" alt="' . l10n('home') . '"/></a>
    </li>');
}
if (is_admin())
{
  $template->assign('U_EDIT', PHPWG_ROOT_PATH . 'admin.php?page=plugin&amp;section=' . AP_DIR . '%2Fadmin%2Fadd_page.php&amp;edit=' . $ap_id[0]);
}

$template->clear_assign(array('U_MODE_POSTED', 'U_MODE_CREATED'));

?>