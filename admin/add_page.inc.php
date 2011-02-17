<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

$edited_page = array();
$edited_page['id'] = 0;
$edited_page['homepage'] = false;
$page_title = l10n('ap_create');

include(AP_PATH.'admin/page_form.inc.php');

?>