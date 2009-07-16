<?php

function get_html_groups_selection(
  $groups,
  $fieldname,
  $selecteds = array()
  )
{
  global $conf;
  if (count ($groups) == 0 )
  {
    return '';
  }
  $output = '<div id="'.$fieldname.'">';
  $id = 1;
  foreach ($groups as $group)
  {
    $output.=

      '<input type="checkbox" name="'.$fieldname.'[]"'
      .' id="'.$id++.'"'
      .' value="'.$group['id'].'"'
      ;

    if (in_array($group['id'], $selecteds))
    {
      $output.= ' checked="checked"';
    }

    $output.=
      '><label>'
      .'&nbsp;'. $group['name']
      .'</label>'
      ."\n"
      ;
  }
  $output.= '</div>';

  return $output;
}


function get_all_groups()
{
$query = '
SELECT id, name
  FROM '.GROUPS_TABLE.'
  ORDER BY name ASC
;';
$result = pwg_query($query);

$groups = array();
  while ($row = mysql_fetch_assoc($result))
  {
    array_push($groups, $row);
  }

  usort($groups, 'groups_name_compare');

  return $groups;
}


function groups_name_compare($a, $b)
{
  return strcmp(strtolower($a['name']), strtolower($b['name']));
}

?>