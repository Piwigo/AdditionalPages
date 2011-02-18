<div class="titrePage">
	<h2>Additional Pages - {'Configuration'|@translate}</h2>
</div>

<form name="apform" method="post" action="" class="properties"  ENCTYPE="multipart/form-data">
<fieldset>
	<legend>{'ap_config'|@translate}</legend>
	<table>
		<tr>
			<td colspan="3">{'ap_perm'|@translate}</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="user_perm" value="on" {if $ap_conf.user_perm}checked="checked"{/if}/> <i>{'ap_user_perm'|@translate}</i></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="group_perm" value="on" {if $ap_conf.group_perm}checked="checked"{/if}/> <i>{'ap_group_perm'|@translate}</i></td>
		</tr>
    <tr>
			<td colspan="3"><br><hr><br></td>
		</tr>
    <tr>
			<td colspan="3"><input type="checkbox" name="show_home" value="on" {if $ap_conf.show_home}checked="checked"{/if}/> {'ap_show_home'|@translate}</td>
		</tr>
    <tr>
			<td colspan="3"><input type="checkbox" name="show_menu" value="on" {if isset($SHOW_MENU)}checked="checked"{/if}/> {'ap_show_menu'|@translate}</td>
		</tr>
		<tr class="menu_languages">
			<td><br>{'ap_menu_name'|@translate} : &nbsp;&nbsp;</td>
			<td><br>{'Default'|@translate}&nbsp;&nbsp;</td>
			<td><br><input type="text" size="50" maxlength="255" value="{$LANG_DEFAULT_VALUE}" name="menu_lang[default]"/></td>
		</tr>
		{foreach from=$language item=lang}
		<tr class="menu_languages">
			<td></td>
			<td>{$lang.LANGUAGE_NAME}&nbsp;&nbsp;</td>
			<td><input type="text" size="50" maxlength="255" value="{$lang.VALUE}" name="menu_lang[{$lang.LANGUAGE_CODE}]"/></td>
		</tr>
		{/foreach}
	</table>

<br>
</fieldset>
	<p><input class="submit" type="submit" value="{'Submit'|@translate}" name="submit"/></p>
</form>

{if isset($convert_bbcode)}
	<p><a href="{$convert_bbcode.PATH}">{'ap_parse_bbcode'|@translate}</a></p>
{/if}

<script type="text/javascript">
jQuery().ready( function () {ldelim}
  jQuery("input[name='show_menu']").click( function() {ldelim}
    if (this.checked)
      jQuery('.menu_languages').show();
    else
      jQuery('.menu_languages').hide();
  });
});
if (!jQuery("input[name='show_menu']").attr('checked'))
  jQuery('.menu_languages').hide();
</script>
