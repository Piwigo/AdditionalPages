<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
load_language('plugin.lang', AP_PATH);

global $conf, $template, $lang;

$ap_conf = explode ("," , $conf['additional_pages']);

// Enregistrement de la configuration
if (isset($_POST['submit']))
{
	foreach($_POST['menu_lang'] as $language_code => $name)
  {
		if ($language_code == 'default') $save_languages[] = $name;
		elseif (!empty($name)) $save_languages[] = $language_code . ':' . $name;
	}
	$languages = implode('/', $save_languages);
    if (!isset($_POST['show_menu']))
        $_POST['show_menu'] = 'off';
    if (!isset($_POST['show_home']))
        $_POST['show_home'] = 'off';
    if (!isset($_POST['show_edit']))
        $_POST['show_edit'] = 'off';
    if (!isset($_POST['redirect']))
        $_POST['redirect'] = 'off';
    if (!isset($_POST['group_perm']))
        $_POST['group_perm'] = 'off';
    if (!isset($_POST['user_perm']))
        $_POST['user_perm'] = 'off';
    $ap_conf = array($languages,
        $_POST['show_menu'],
        $_POST['show_home'],
        $_POST['show_edit'],
        $_POST['redirect'],
        $ap_conf[5], //Ancien emplacement du bbcode
        $_POST['group_perm'],
        $_POST['user_perm']);
    $query = '
UPDATE ' . CONFIG_TABLE . '
  SET value="' . implode ("," , $ap_conf) . '"
  WHERE param="additional_pages"
  LIMIT 1';
    pwg_query($query);
    array_push($page['infos'], l10n('ap_conf_saved'));
}

// Gestion des langues pour le bloc menu
$languages = explode('/', $ap_conf[0]);
foreach($languages as $language)
{
	$array = explode(':', $language);
	if (!isset($array[1])) $menu_langs['default'] = $array[0];
	else $menu_langs[$array[0]] = $array[1];
}

$template->assign('LANG_DEFAULT_VALUE', $menu_langs['default']);
foreach (get_languages() as $language_code => $language_name)
{
	$template->append('language', array(
    'LANGUAGE_NAME' => $language_name,
    'LANGUAGE_CODE' => $language_code,
    'VALUE' => (isset($menu_langs[$language_code]) ? $menu_langs[$language_code] : '')));
}

// Parametrage du template
$template->assign(array(
  'MENU_NAME' => $ap_conf[0],
  'SHOW_MENU' => (isset($ap_conf[1]) and $ap_conf[1] == 'on') ? 'checked="checked"' : '',
  'SHOW_HOME' => (isset($ap_conf[2]) and $ap_conf[2] == 'on') ? 'checked="checked"' : '',
  'SHOW_EDIT' => (isset($ap_conf[3]) and $ap_conf[3] == 'on') ? 'checked="checked"' : '',
  'REDIRECT' => (isset($ap_conf[4]) and $ap_conf[4] == 'on') ? 'checked="checked"' : '',
  'GROUP_PERM' => (isset($ap_conf[6]) and $ap_conf[6] == 'on') ? 'checked="checked"' : '',
  'USER_PERM' => (isset($ap_conf[7]) and $ap_conf[7] == 'on') ? 'checked="checked"' : ''));
		
// Lien de conversion bbcode
if (isset($ap_conf[5]) and $ap_conf[5] == 'on')
{
	$template->assign('convert_bbcode', array('PATH' => get_admin_plugin_menu_link(AP_PATH . 'admin/parse_bbcode.php')));
}

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/admin.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>