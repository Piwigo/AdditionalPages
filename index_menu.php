<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

add_event_handler('blockmanager_register_blocks', 'register_ap_menubar_blocks');
add_event_handler('blockmanager_apply', 'ap_apply');

function register_ap_menubar_blocks( $menu_ref_arr )
{
  $menu = & $menu_ref_arr[0];
  if ($menu->get_id() != 'menubar')
    return;
  $menu->register_block( new RegisteredBlock( 'mbAdditionalPages', 'Additional Pages', 'AP'));
}

function ap_apply($menu_ref_arr)
{
  global $template, $conf, $user, $lang;

  $ap_conf = explode ("," , $conf['additional_pages']);

  $menu = & $menu_ref_arr[0];
  
  if ( ($block = $menu->get_block( 'mbAdditionalPages' ) ) != null )
  {
    if (!file_exists(AP_PATH . 'template/' . $user['template'] . '.tpl'))
    {
      $user['template'] = 'yoga';
    }

    load_language('plugin.lang', AP_PATH);
    
    // Gestion des langues pour le nom du menu
    $languages = explode('/', $ap_conf[0]);
    foreach($languages as $language)
    {
      $array = explode(':', $language);
      if (!isset($array[1])) $menu_langs['default'] = $array[0];
      else $menu_langs[$array[0]] = $array[1];
    }

    $data = array();

    if (is_admin())
    {
      array_push($data, array(
        'URL' => PHPWG_ROOT_PATH . 'admin.php?page=plugin&amp;section=' . AP_DIR . '%2Fadmin%2Fadd_page.php',
        'LABEL' => l10n('ap_add_page')));
      $clauses = '';
    }
    else
    {
      $clauses = 'WHERE (lang = "' . $user['language'] . '" OR lang = "ALL")';
    }
  
    // Recupération des groupes de l'utilisateur
    $q = 'SELECT group_id FROM ' . USER_GROUP_TABLE . ' WHERE user_id = ' . $user['id'] . ';';
    $result = pwg_query($q);
    $groups = array();
    while ($row = mysql_fetch_assoc($result))
    {
      array_push($groups, $row['group_id']);
    }
  
    // Récupération des pages
    $q = 'SELECT id , pos , title
FROM ' . ADD_PAGES_TABLE . '
' . $clauses . '
ORDER BY pos ASC;';
    $result = pwg_query($q);

    while ($row = mysql_fetch_assoc($result))
    {
      if ($row['pos'] != '0' or is_admin())
      {
        if (strpos($row['title'] , '/user_id='))
        {
          $array = explode('/user_id=' , $row['title']);
          $row['title'] = $array[0];
          $authorized_users = explode(',', $array[1]);
        }
        if (strpos($row['title'] , '/group_id='))
        {
          $array = explode('/group_id=' , $row['title']);
          $row['title'] = $array[0];
          $auth = explode(',', $array[1]);
          $authorized_groups = array_intersect($groups, $auth);
        }
        if (is_admin() and isset($ap_conf[3]) and $ap_conf[3] == 'on')
        {
          $row['title'] .= '</a> --- <a href=' . PHPWG_ROOT_PATH . 'admin.php?page=plugin&amp;section=' . AP_DIR . '%2Fadmin%2Fadd_page.php&amp;edit=' . $row['id'] . '>[edit]';
        }
        if (is_admin() or (
          (isset($ap_conf[6]) and $ap_conf[6] == 'off' or !isset($authorized_groups) or !empty($authorized_groups)) and
          (isset($ap_conf[7]) and $ap_conf[7] == 'off' or !isset($authorized_users) or in_array($user['status'], $authorized_users))))
        {
          array_push($data, array(
            'URL' => PHPWG_ROOT_PATH . 'index.php?/additional_page/' . $row['id'],
            'LABEL' => $row['title']));
        }
        unset($authorized_groups);
        unset($authorized_users);
      }
    }

    if (!empty($data))
    {
      $block->set_title(isset($menu_langs[$user['language']]) ? $menu_langs[$user['language']] : $menu_langs['default']);
      $block->template = dirname(__FILE__) . '/template/' . $user['template'] . '.tpl';
      $block->data = $data;
    }
  }
}
?>