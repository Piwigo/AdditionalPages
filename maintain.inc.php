<?php

function plugin_install()
{
  global $prefixeTable, $conf;

  $query = 'SHOW TABLES LIKE "' . $prefixeTable . 'additionalpages"';
  $result = pwg_query($query);
  if (!mysql_fetch_row($result))
  {
    $query = 'CREATE TABLE ' . $prefixeTable . 'additionalpages (
id SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT,
pos SMALLINT( 5 ) NULL default NULL ,
lang VARCHAR( 255 ) NULL default NULL ,
title VARCHAR( 255 ) NOT NULL ,
content LONGTEXT NOT NULL ,
users VARCHAR( 255 ) NULL DEFAULT NULL ,
groups VARCHAR( 255 ) NULL DEFAULT NULL ,
permalink VARCHAR( 64 ) NULL DEFAULT NULL ,
standalone ENUM( "true", "false" ) NOT NULL DEFAULT "false" ,
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
      'show_home' => true,
      'group_perm' => false,
      'user_perm' => false,
      'homepage' => null,
    );

    $query = 'INSERT INTO ' . CONFIG_TABLE . ' (param,value,comment)
VALUES ("additional_pages" , "'.pwg_db_real_escape_string(serialize($config)).'" , "Additional Pages config configuration");';
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

?>