<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $template, $user;

$page['section'] = 'additional_page';
$identifier = $page['is_homepage'] ? $conf['additional_pages']['homepage'] : $tokens[1];

load_language('plugin.lang.php', AP_PATH);

if (function_exists('get_extended_desc'))
  add_event_handler('AP_render_content', 'get_extended_desc');

// Récupération des données de la page
$query = 'SELECT id, title , content, users, groups, permalink
FROM ' . ADD_PAGES_TABLE . '
';
$query .= is_numeric($identifier) ?
  'WHERE id = '.$identifier.';' :
  'WHERE permalink = "'.$identifier.'";';

$row = mysql_fetch_assoc(pwg_query($query));

if (empty($row))
{
  if ($page['is_homepage']) return;
  page_not_found('Requested page does not exist');
}

if (is_numeric($identifier) and !empty($row['permalink']) and !$page['is_homepage'])
{
  redirect(make_index_url().'/page/' . $row['permalink']);
}

$page['additional_page'] = array(
  'id' => $row['id'],
  'permalink' => @$row['permalink'],
  'title' => trigger_event('AP_render_content', $row['title']),
  'content' => trigger_event('AP_render_content', $row['content']),
);

// Utilisateurs autorisés
if (!empty($row['users']))
{
  $authorized_users = explode(',', $row['users']);
  if (!is_admin() and $conf['additional_pages']['user_perm'] and !in_array($user['status'], $authorized_users))
  {
    if ($page['is_homepage']) return;
  	page_forbidden(l10n('You are not authorized to access the requested page'));
  }
}

// Groupe autorisé
if (!empty($row['groups']))
{
  $q = 'SELECT *
FROM ' . USER_GROUP_TABLE . '
WHERE user_id = ' . $user['id'] . ' AND group_id IN (' . $row['groups'] . ');';
  $array = mysql_fetch_array(pwg_query($q));
  if (!is_admin() and $conf['additional_pages']['group_perm'] and empty($array))
  {
    if ($page['is_homepage']) return;
  	page_forbidden(l10n('You are not authorized to access the requested page'));
  }
}

add_event_handler('loc_end_index', 'ap_set_index');

function ap_set_index()
{
  global $template, $page, $conf;

  $template->assign(array(
    'TITLE' => $page['additional_page']['title'],
    'PLUGIN_INDEX_CONTENT_BEGIN' => $page['additional_page']['content'],
    )
  );

  if ($conf['additional_pages']['show_home'])
  {
    $template->assign('PLUGIN_INDEX_ACTIONS' , '
      <li><a href="'.make_index_url().'/categories" title="' . l10n('return to homepage') . '">
        <img src="' . $template->get_themeconf('icon_dir') . '/home.png" class="button" alt="' . l10n('home') . '"/></a>
      </li>');
  }
  if (is_admin())
  {
    $template->assign('U_EDIT', PHPWG_ROOT_PATH.'admin.php?page=plugin&amp;section='.AP_DIR.'%2Fadmin%2Fadmin.php&amp;tab=edit_page&amp;edit='.$page['additional_page']['id'].'&amp;redirect=true');
  }
  $template->clear_assign(array('U_MODE_POSTED', 'U_MODE_CREATED'));
}

?>