<?php

function plugin_install()
{
  global $prefixeTable, $conf;

  $query = 'SHOW TABLES LIKE "' . $prefixeTable . 'additionalpages"';
  $result = pwg_query($query);
  if (!mysql_fetch_row($result))
  {
    $query = 'CREATE TABLE ' . $prefixeTable . 'additionalpages (
id SMALLINT( 5 ) UNSIGNED NOT NULL ,
pos SMALLINT( 5 ) NULL default NULL ,
lang VARCHAR( 255 ) NULL default NULL ,
title VARCHAR( 255 ) NOT NULL ,
content LONGTEXT NOT NULL ,
permalink VARCHAR( 64 ) NULL DEFAULT NULL ,
PRIMARY KEY (id) ,
INDEX (pos) ,
INDEX (lang))
DEFAULT CHARACTER SET utf8;';
    pwg_query($query);
  }

  if (!isset($conf['additional_pages']))
  {
    $config = array(
      'languages' => array('default' => 'Additional Pages'),
      'show_menu' => true,
      'show_home' => true,
      'redirect' => false,
      'group_perm' => false,
      'user_perm' => false,
      'homepage' => null,
    );
    $query = 'INSERT INTO ' . CONFIG_TABLE . ' (param,value,comment)
VALUES ("additional_pages" , "'.addslashes(serialize($config)).'" , "Additional Pages config configuration");';
    pwg_query($query);
  }
}

function plugin_activate()
{
  global $prefixeTable;

  $q = pwg_query('SHOW COLUMNS FROM ' . HISTORY_TABLE . ' LIKE "section"');
  $section = mysql_fetch_array($q);
  $type = $section['Type'];

  // Add additional page section into history table
  if (strpos($type, 'additional_page') === false)
  {
    $type = strtr($type , array(')' => ',\'additional_page\')'));
    pwg_query('ALTER TABLE ' . HISTORY_TABLE . ' CHANGE section section ' . $type . ' DEFAULT NULL');
  }
  
  // Check if upgrade is needed
  $query = 'SHOW FULL COLUMNS FROM ' . $prefixeTable . 'additionalpages;';
  $result = array_from_query($query, 'Collation');
  if (strpos($result[4], 'utf8') === false)
  {
    upgrade_ap_from_17();
  }
  $result = array_from_query($query, 'Field');
  if (!in_array('permalink', $result))
  {
    upgrade_ap_from_21();
  }
}

function plugin_uninstall()
{
  global $prefixeTable;

	$q = 'DROP TABLE ' . $prefixeTable . 'additionalpages;';
  pwg_query($q);

	$q = 'DELETE FROM ' . CONFIG_TABLE . ' WHERE param="additional_pages" LIMIT 1;';
  pwg_query($q);
}

function upgrade_ap_from_17()
{
  global $prefixeTable;

  $query = 'ALTER TABLE ' . $prefixeTable . 'additionalpages
MODIFY COLUMN lang varchar(255) CHARACTER SET utf8 NOT NULL,
MODIFY COLUMN title varchar(255) CHARACTER SET utf8 NOT NULL,
MODIFY COLUMN text longtext CHARACTER SET utf8 NOT NULL,
DEFAULT CHARACTER SET utf8;';

  pwg_query($query);
}

function upgrade_ap_from_21()
{
  global $prefixeTable, $conf;

  $query = 'ALTER TABLE ' . $prefixeTable . 'additionalpages
CHANGE `id` `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE `pos` `pos` SMALLINT( 5 ) NULL DEFAULT NULL ,
CHANGE `lang` `lang` VARCHAR( 255 ) NULL DEFAULT NULL ,
CHANGE `text` `content` LONGTEXT NOT NULL ,
ADD `users` VARCHAR( 255 ) NULL DEFAULT NULL ,
ADD `groups` VARCHAR( 255 ) NULL DEFAULT NULL ,
ADD `permalink` VARCHAR( 64 ) NULL DEFAULT NULL;';
  pwg_query($query);

  $query = '
SELECT id, pos, title, lang
FROM '.$prefixeTable.'additionalpages
ORDER BY pos ASC, id ASC
;';
  $result = pwg_query($query);
  while ($row = mysql_fetch_assoc($result))
  {
    $title = $row['title'];
    $authorized_users = 'NULL';
    $authorized_groups = 'NULL';

    if (strpos($title , '/user_id='))
    {
      $array = explode('/user_id=' , $title);
      $title = $array[0];
      $authorized_users = '"'.$array[1].'"';
    }
    if (strpos($title , '/group_id='))
    {
      $array = explode('/group_id=' , $title);
      $title = $array[0];
      $authorized_groups = '"'.$array[1].'"';
    }

    $position = $row['pos'];
    if ($row['pos'] === '0')
      $position = '-1';
    elseif (empty($row['pos']))
      $position = '0';

    $language = $row['lang'] != 'ALL' ? '"'.$row['lang'].'"' : 'NULL';

    $query = '
UPDATE '.$prefixeTable.'additionalpages
  SET title = "'.addslashes($title).'",
      pos = '.$position.',
      lang = '.$language.',
      users = '.$authorized_users.',
      groups = '.$authorized_groups.'
  WHERE id = '.$row['id'].'
;';
    pwg_query($query);
  }

  $old_conf = explode ("," , $conf['additional_pages']);

  $new_conf = array(
    'show_menu' => @($old_conf[1] == 'on'),
    'show_home' => @($old_conf[2] == 'on'),
    'redirect' => @($old_conf[4] == 'on'),
    'group_perm' => @($old_conf[6] == 'on'),
    'user_perm' => @($old_conf[7] == 'on'),
    'homepage' => null,
    );

  $languages = explode('/', $old_conf[0]);
  $new_conf['languages'] = array();
  foreach($languages as $language)
  {
    $array = explode(':', $language);
    if (!isset($array[1])) $new_conf['languages']['default'] = $array[0];
    else $new_conf['languages'][$array[0]] = $array[1];
  }

  $query = '
UPDATE '.CONFIG_TABLE.'
  SET value = "'.addslashes(serialize($new_conf)).'"
  WHERE param = "additional_pages"
;';
  pwg_query($query);
}

?>