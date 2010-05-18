<div class="titrePage">
	<h2>Additional Pages</h2>
</div>

<form name="apform" method="post" action="" class="properties"  ENCTYPE="multipart/form-data">
<fieldset>
	<legend>{'ap_config'|@translate}</legend>
	<table>
		<tr>
			<td><br>{'ap_menu_name'|@translate} : &nbsp;&nbsp;</td>
			<td><br>{'Default'|@translate}&nbsp;&nbsp;</td>
			<td><br><input type="text" size="50" maxlength="255" value="{$LANG_DEFAULT_VALUE}" name="menu_lang[default]"/></td>
		</tr>
		{foreach from=$language item=lang}
		<tr>
			<td></td>
			<td>{$lang.LANGUAGE_NAME}&nbsp;&nbsp;</td>
			<td><input type="text" size="50" maxlength="255" value="{$lang.VALUE}" name="menu_lang[{$lang.LANGUAGE_CODE}]"/></td>
		</tr>
		{/foreach}
		<tr>
			<td colspan="3"><br><hr><br></td>
		</tr>
		<tr>
			<td colspan="3"><input type="checkbox" name="show_menu" value="on" {$SHOW_MENU}/> {'ap_show_menu'|@translate}</td>
		</tr>
		<tr>
			<td colspan="3"><input type="checkbox" name="show_home" value="on" {$SHOW_HOME}/> {'ap_show_home'|@translate}</td>
		</tr>
		<tr>
			<td colspan="3"><input type="checkbox" name="show_edit" value="on" {$SHOW_EDIT}/> {'ap_show_edit'|@translate}</td>
		</tr>
		<tr>
			<td colspan="3"><input type="checkbox" name="redirect" value="on" {$REDIRECT}/> {'ap_redirect'|@translate}</td>
		</tr>
		<tr>
			<td colspan="3"><br><hr><br></td>
		</tr>
		<tr>
			<td colspan="3">{'ap_perm'|@translate}</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="user_perm" value="on" {$USER_PERM}/> <i>{'ap_user_perm'|@translate}</i></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="group_perm" value="on" {$GROUP_PERM}/> <i>{'ap_group_perm'|@translate}</i></td>
		</tr>

	</table>

<br>
</fieldset>
	<p><input class="submit" type="submit" value="{'Submit'|@translate}" name="submit" {$TAG_INPUT_ENABLED}/></p>
</form>

{if isset($convert_bbcode)}
	<p><a href="{$convert_bbcode.PATH}">{'ap_parse_bbcode'|@translate}</a></p>
{/if}
