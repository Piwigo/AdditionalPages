<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $lang, $conf;

include(get_language_filepath('plugin.lang.php', AP_PATH));

function ap_CheckTags($str){
	//array of known tags
	$known = array('p','b','i','u','s','center','right','ol','ul','li','quote', 'img','url','email','color', 'size');
	//storage stack
	$tags = array();
	
	for ($pos = 0; $pos<strlen($str); $pos++)
	{
		if ($str{$pos} == '[')
			{
			$end_pos = strpos($str, ']', $pos);
			$tag = substr($str, ++$pos, $end_pos-$pos);
			//deals with tags which contains arguments (ie quote)
			if ( ($equal_pos = strpos($tag, '=', 0)) !== FALSE)
				$tag = substr($tag, 0, $equal_pos);
			//check whether we have a defined tag or not.
			if (in_array(strtolower($tag),$known) || in_array(strtolower(substr($tag,1)),$known))
				{
			//closing tag
			if ($tag{0} == '/')
				{
					//cleaned tag
					$tag = substr($tag, 1);		
					$before_tag = substr($str, 0, $pos-1);
					$after_tag = substr($str, $end_pos+1);	
					//pop stack
					while (($temp = array_pop($tags)))
							{
							if ($temp != $tag)
								$before_tag.='[/'.$temp.']';
							else 
								{
								$before_tag.='[/'.$tag.']';
								break;
								}

							}
					$end_pos += strlen($before_tag)+strlen($after_tag)-strlen($str);
					$str = $before_tag.$after_tag;
				}
			else 
				{ // push stack
				array_push($tags,$tag);
				}
			}
		$pos = $end_pos;	
		}
	}
	// empty stack and closing tags
	while ($temp = array_pop($tags))
		{		
		$str.='[/'.$temp.']';
		}

	return $str;
}

function ap_parse_bbcode($comment)
{
	$comment = nl2br($comment);
	$comment = ap_CheckTags($comment);
			
	$patterns = array();
	$replacements = array();
	
	//Paragraph
	$patterns[] = '#\[p\](.*?)\[/p\]#is';
	$replacements[] = '<p>\\1</p>';
    // Bold
	$patterns[] = '#\[b\](.*?)\[/b\]#is';
	$replacements[] = '<strong>\\1</strong>';
	//Italic
	$patterns[] = '#\[i\](.*?)\[/i\]#is';
	$replacements[] = '<em>\\1</em>';
	//Underline	
	$patterns[] = '#\[u\](.*?)\[\/u\]#is';
	$replacements[] = '<u>\\1</u>';
	//Strikethrough
	$patterns[] = '#\[s\](.*?)\[/s\]#is';
	$replacements[] = '<del>\\1</del>';
	//Center
	$patterns[] = '#\[center\](.*?)\[/center\]#is';
	$replacements[] = '</p><div align="center"><p>\\1</p></div><p>';
	//Right
	$patterns[] = '#\[right\](.*?)\[/right\]#is';
	$replacements[] = '</p><div align="right"><p>\\1</p></div><p>';
	//Olist
	$patterns[] = '#\[ol\](.*?)\[/ol\]#is';
	$replacements[] = '<ol>\\1</ol>';
	//Ulist
	$patterns[] = '#\[ul\](.*?)\[/ul\]#is';
	$replacements[] = '<ul>\\1</ul>';
	//List
	$patterns[] = '#\[li\](.*?)\[/li\]#is';
	$replacements[] = '<li>\\1</li>';
	// Quotes
	$patterns[] = "#\[quote\](.*?)\[/quote\]#is";
	$replacements[] = '</p><blockquote><span style="font-size: 11px; line-height: normal">\\1</span></blockquote><p>';
	//Quotes with "user"
	$patterns[] = "#\[quote=&quot;(.*?)&quot;\](.*?)\[/quote\]#is";
	$replacements[] = '</p><blockquote><span style="font-size: 11px; line-height: normal"><b>\\1 : </b><br/>\\2</span></blockquote><p>';
	//Quotes with user
	$patterns[] = "#\[quote=(.*?)\](.*?)\[/quote\]#is";
	$replacements[] = '</p><blockquote><span style="font-size: 11px; line-height: normal"><b>\\1 : </b><br/>\\2</span></blockquote><p>';
	//Images
	$patterns[] = "#\[img\](.*?)\[/img\]#si";
	$replacements[] = "<img src='\\1' alt='' />";
	//[url]xxxx://www.zzzz.yyy[/url]
	$patterns[] = "#\[url\]([\w]+?://[^ \"\n\r\t<]*?)\[/url\]#is"; 
	$replacements[] = '<a href="\\1" target="_blank">\\1</a>'; 
	//[url]www.zzzzz.yyy[/url]
	$patterns[] = "#\[url\]((www|ftp)\.[^ \"\n\r\t<]*?)\[/url\]#is"; 
	$replacements[] = '<a href="http://\\1" target="_blank">\\1</a>'; 
	//[url=xxxx://www.zzzzz.yyy]ZzZzZ[/url] /*No I ain't sleeping yet*/
	$patterns[] = "#\[url=([\w]+?://[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is"; 
	$replacements[] = '<a href="\\1" target="_blank">\\2</a>'; 
	// [url=www.zzzzz.yyy]zZzZz[/url] /*But I'm thinking about*/
	$patterns[] = "#\[url=((www|ftp)\.[^ \"\n\r\t<]*?)\](.*?)\[/url\]#is"; 
	$replacements[] = '<a href="http://\\1" target="_blank">\\2</a>'; 
	// [url="www.zzzzz.yyy"]zZzZz[/url]   /* It's nearly 2 am now */
	$patterns[] = "#\[url=&quot;((www|ftp)\.[^ \n\r\t<]*?)&quot;\](.*?)\[/url\]#is";
	$replacements[] = '<a href="http://\\1" target="_blank">\\3</a>';
	//[url="http://www.zzzzz.yyy"]zZzZz[/url] /*I really dislike commenting code*/
	$patterns[] = "#\[url=&quot;([\w]+?://[^ \n\r\t<]*?)&quot;\](.*?)\[/url\]#is";
	$replacements[] = '<a href="\\1" target="_blank">\\2</a>';
	//[email]samvure@gmail.com[/email]
	$patterns[] = "#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#is";
	$replacements[] = '<a href="mailto:\\1">\\1</a>';
	//Size
	$patterns[] = "#\[size=([1-2]?[0-9])\](.*?)\[/size\]#si";
	$replacements[] = '<span style="font-size: \\1px; line-height: normal">\\2</span>';
	//Colours
	$patterns[] = "#\[color=(\#[0-9A-F]{6}|[a-z]+)\](.*?)\[/color\]#si";
	$replacements[] = '<span style="color: \\1">\\2</span>';
	
	$comment = preg_replace($patterns, $replacements, $comment);
		
	return $comment;
}

// Traitement des pages
$result= pwg_query('SELECT id, text FROM ' . ADD_PAGES_TABLE);
while ($row = mysql_fetch_assoc($result)) {
	$text = ap_parse_bbcode($row['text']);
	pwg_query('UPDATE ' . ADD_PAGES_TABLE . ' SET text="' . addslashes($text) . '" WHERE id=' . $row['id'] . ' LIMIT 1');
}

// Mise à jour de la configuration
$ap_conf = explode ("," , $conf['additional_pages']);
if (isset($ap_conf[5])) {
	$ap_conf[5] = '';
	pwg_query('UPDATE ' . CONFIG_TABLE . ' SET value="' . implode ("," , $ap_conf) . '" WHERE param="additional_pages" LIMIT 1');
}

redirect(str_replace('&amp;', '&', get_admin_plugin_menu_link(AP_PATH . 'admin/admin.php')), l10n('ap_convert_bbcode_ok'), 3);

?>