<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

add_event_handler('blockmanager_register_blocks', 'register_ap_menubar_blocks');
add_event_handler('blockmanager_apply', 'ap_apply');

function register_ap_menubar_blocks( $menu_ref_arr )
{
  $menu = & $menu_ref_arr[0];
  if ($menu->get_id() != 'menubar')
    return;
  $menu->register_block( new RegisteredBlock( 'mbAdditionalPages', 'Additional Pages', 'P@t'));
}

function ap_apply($menu_ref_arr)
{
  global $template, $conf, $user, $lang;

  $menu = & $menu_ref_arr[0];
  
  if ( ($block = $menu->get_block( 'mbAdditionalPages' ) ) != null )
  {
    $template->set_template_dir(AP_PATH.'template/');

    load_language('plugin.lang', AP_PATH);

    $data = array();

    // Recupération des groupes de l'utilisateur
    $q = 'SELECT group_id FROM ' . USER_GROUP_TABLE . ' WHERE user_id = ' . $user['id'] . ';';
    $result = pwg_query($q);
    $groups = array();
    while ($row = mysql_fetch_assoc($result))
    {
      array_push($groups, $row['group_id']);
    }
  
    // Récupération des pages
    $q = 'SELECT id, pos, title, users, groups, permalink
FROM ' . ADD_PAGES_TABLE . '
WHERE (lang = "' . $user['language'] . '" OR lang IS NULL)
  AND pos >= 0
ORDER BY pos ASC, id ASC
;';
    $result = pwg_query($q);

    while ($row = mysql_fetch_assoc($result))
    {
      if ($row['pos'] != '0' or is_admin())
      {
        $authorized_users = array();
        $authorized_groups = array();
        if (!empty($row['users']))
        {
          $authorized_users = explode(',', $row['users']);
        }
        if (!empty($row['groups']))
        {
          $auth = explode(',', $row['groups']);
          $authorized_groups = array_intersect($groups, $auth);
        }
        if (is_admin() or (
          (!$conf['additional_pages']['group_perm'] or empty($row['groups']) or !empty($authorized_groups)) and
          (!$conf['additional_pages']['user_perm'] or empty($row['users']) or in_array($user['status'], $authorized_users))))
        {
          array_push($data, array(
            'URL' => make_index_url().'/page/'.(isset($row['permalink']) ? $row['permalink'] : $row['id']),
            'LABEL' => $row['title']));
        }
        unset($authorized_groups);
        unset($authorized_users);
      }
    }

    if (!empty($data))
    {
      $block->set_title(
        isset($conf['additional_pages']['languages'][$user['language']]) ?
          $conf['additional_pages']['languages'][$user['language']] :
          @$conf['additional_pages']['languages']['default']
        );
      $block->template = 'AdditionalPages_menu.tpl';
      $block->data = $data;
    }
  }
}
?>