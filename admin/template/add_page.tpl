{literal}
<script type="text/javascript">
function GereChkbox(conteneur, a_faire) {
var blnEtat=null;
var Chckbox = document.getElementById(conteneur).firstChild;
	while (Chckbox!=null) {
		if (Chckbox.nodeName=="INPUT")
			if (Chckbox.getAttribute("type")=="checkbox") {
				blnEtat = (a_faire=='0') ? false : (a_faire=='1') ? true : (document.getElementById(Chckbox.getAttribute("id")).checked) ? false : true;
				document.getElementById(Chckbox.getAttribute("id")).checked=blnEtat;
			}
		Chckbox = Chckbox.nextSibling;
	}
}
</script>
{/literal}

<div class="titrePage">
	<h2>{$AP_TITLE}</h2>
</div>
<form method="post" action="" class="properties"  ENCTYPE="multipart/form-data">
	<table>
		<tr>
			<td align="right"><label for="title">{'ap_page_name'|@translate}</label> &nbsp;&nbsp;</td>
			<td><input type="text" size="60" maxlength="255" value="{if isset($NAME)}{$NAME}{/if}" name="title" id="title"/></td>
		</tr>
		<tr>
			<td align="right"><label for="permalink">{'Permalink'|@translate}</label> &nbsp;&nbsp;</td>
			<td><input type="text" size="60" value="{if isset($PERMALINK)}{$PERMALINK}{/if}" name="permalink" id="permalink"/></td>
		</tr>
		<tr>
			<td align="right"><label for="lang">{'ap_page_lang'|@translate}</label> &nbsp;&nbsp;</td>
			<td>
        {html_options name=lang id=lang options=$lang.OPTIONS selected=$lang.SELECTED}
			</td>
		</tr>
    <tr><td>&nbsp;</td></tr>
		<tr>
			<td align="right"><label for="homepage">{'ap_set_as_homepage'|@translate}</label> &nbsp;&nbsp;</td>
			<td><input type="checkbox" name="homepage" id="homepage" {if isset($HOMEPAGE) and $HOMEPAGE}checked="checked"{/if}"/>
      &nbsp; <i>{'ap_homepage_tip'|@translate}</i></td>
		</tr>
		<tr>
			<td align="right"><label for="standalone">{'ap_standalone_page'|@translate}</label> &nbsp;&nbsp;</td>
			<td><input type="checkbox" name="standalone" id="standalone" {if isset($STANDALONE) and $STANDALONE}checked="checked"{/if}"/>
      &nbsp; <i>{'ap_standalone_tip'|@translate}</i></td>
		</tr>

		{if isset($user_perm)}
		<tr>
			<td colspan="2"><hr></td>
		</tr>
		<tr>
			<td align="right">{'ap_authorized_users'|@translate} &nbsp;&nbsp;</td>
			<td>
				<div id="users">
				<input type="checkbox" name="users[]" id="guest" value="guest" {$user_perm.GUEST}><label>&nbsp;{'user_status_guest'|@translate}</label>
				<input type="checkbox" name="users[]" id="generic" value="generic" {$user_perm.GENERIC}><label>&nbsp;{'user_status_generic'|@translate}</label>
				<input type="checkbox" name="users[]" id="normal" value="normal" {$user_perm.NORMAL}><label>&nbsp;{'user_status_normal'|@translate}</label>
				<input type="checkbox" name="users[]" id="admin" value="admin" checked="checked" disabled onclick="return false;"><label>&nbsp;{'user_status_admin'|@translate}</label>
				</div>
			</td>
        </tr>
    {/if}

		{if !empty($GROUPSELECTION)}
		<tr>
			<td colspan="2"><hr></td>
		</tr>
		<tr>
			<td align="right">{'ap_authorized_group'|@translate} &nbsp;&nbsp;</td>
			<td>{$GROUPSELECTION}</td>
        </tr>
        <tr>
        	<td></td>
			<td><a href="javascript:GereChkbox('groups','1');">{'ap_select_all'|@translate}</a> / <a href="javascript:GereChkbox('groups','0');">{'ap_unselect_all'|@translate}</a>
			<i>&nbsp;&nbsp; {'ap_guest'|@translate}</i></td>
        </tr>
    {/if}
</table>
<table style="width:95%;">
		<tr>
			<td colspan="2" align="center"><br>
				<b>{'ap_page_content'|@translate}</b><br>
				<textarea name="ap_content" id="ap_content" rows="30" cols="50" style="width:100%;">{if isset($CONTENT)}{$CONTENT}{/if}</textarea>
      </td>
		</tr>

		<tr>
		<td colspan="2" align="center"><br>
		<input class="submit" type="submit" value="{'ap_save'|@translate}" name="save">
		{if isset($delete)}
		<input class="submit" type="submit" value="{'ap_delete'|@translate}" name="delete" onclick="return confirm('Are you sure?'|@translate);"/>
		{/if}
		</tr>
</table>

</form>
