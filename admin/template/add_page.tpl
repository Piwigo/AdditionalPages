{combine_script id='common' load='footer' path='admin/themes/default/js/common.js'}

{footer_script require='jquery'}{literal}
jQuery().ready( function () {
  jQuery('#title').focusout(function () {
    if (jQuery('#permalink').val() == '' && auto_permalink)
      jQuery.post("plugins/AdditionalPages/admin/ajax.php", { str: this.value }, function(data) {
        jQuery('#permalink').val(data);
        auto_permalink = false;
      });
  });
  
  var content_changed = false;
  jQuery("#ap_content").change(function() {
    content_changed = true;
  });
  
  jQuery("#template").change(function() {
    if ($(this).val() != '-1') {
{/literal}
      if (content_changed == false || confirm("{'The content of the page changed, are your sure you wan\'t to quit without saving?'|@translate|escape:javascript}")) {ldelim}
        window.location.href = "admin.php?page=plugin-AdditionalPages-add_page&load_template="+ $(this).val();
      } else {ldelim}
        $(this).val('-1');
      }
{literal}
    }
  });

  jQuery("#groupsCheckAll").click(function() {
    jQuery("input[name^=groups]").prop("checked", true);
    return false;
  });

  jQuery("#groupsUncheckAll").click(function() {
    jQuery("input[name^=groups]").prop("checked", false);
    return false;
  });
});
var auto_permalink = true;
{/literal}{/footer_script}
{html_head}{literal}
<style type="text/css">
form p {margin:1em; text-align:left;}
form p label {font-weight:normal !important;}
</style>
{/literal}{/html_head}

<div class="titrePage">
	<h2>{$AP_TITLE}</h2>
</div>
<form method="post" action="" class="properties" id="configContent" ENCTYPE="multipart/form-data">
{if $TEMPLATES}
  <p>
    <strong>{'Load a page model'|@translate}</strong>
    <br>
    <select name="template" id="template">
      <option value="-1">---------</option>
  {foreach from=$TEMPLATES item=tpl}
      <option value="{$tpl.tpl_id}" {if $template_selected==$tpl.tpl_id}selected="selected"{/if}>{$tpl.name}</option>
  {/foreach}
    </select>
  </p>
{/if}
    
  <p>
    <strong>{'ap_page_name'|@translate}</strong>
    <br>
    <input type="text" size="60" maxlength="255" value="{if isset($NAME)}{$NAME}{/if}" name="title" id="title"/>
  </p>

  <p>
    <strong>{'Permalink'|@translate}</strong>
    <br>
    <input type="text" size="60" value="{if isset($PERMALINK)}{$PERMALINK}{/if}" name="permalink" id="permalink"/>
  </p>

{if isset($lang)}
  <p>
    <strong>{'ap_page_lang'|@translate}</strong>
    <br>
    {html_options name=lang id=lang options=$lang selected=$selected_lang}
  </p>
{/if}

  <p>
    <label class="font-checkbox">
      <span class="icon-check"></span>
      <input type="checkbox" name="homepage" id="homepage" {if isset($HOMEPAGE) and $HOMEPAGE}checked="checked"{/if}/> <strong>{'ap_set_as_homepage'|@translate}</strong>
    </label>
    <i>{'ap_homepage_tip'|@translate}</i>
  </p>
      
  <p>
    <label class="font-checkbox">
      <span class="icon-check"></span>
      <input type="checkbox" name="standalone" id="standalone" {if isset($STANDALONE) and $STANDALONE}checked="checked"{/if}/> <strong>{'ap_standalone_page'|@translate}</strong>
    </label>
    <i>{'ap_standalone_tip'|@translate}</i>
  </p>

{if isset($level_perm)}
  <p style="margin-top:15px;">
    <strong>{'Privacy level'|@translate}</strong>
    <br>
    <select name="level" size="1">{html_options options=$level_perm selected=$level_selected id=privacy}</select>
  </p>
{/if}

{if isset($users)}
  <p style="margin-top:15px;">
    <strong>{'ap_authorized_users'|@translate}</strong>
    <br>
  {html_checkboxes options=$users selected=$selected_users name=users}
  </p>
{/if}

{if isset($groups)}
  <p style="margin-top:15px;">
    <strong>{'ap_authorized_group'|@translate}</strong>
    <i>{'ap_guest'|@translate}</i>
    <a href="#" id="groupsCheckAll">{'ap_select_all'|@translate}</a> /
    <a href="#" id="groupsUncheckAll">{'ap_unselect_all'|@translate}</a> &nbsp; 
    <br>
  {html_checkboxes options=$groups selected=$selected_groups name=groups}
  </p>
{/if}

  <p>
    <strong>{'ap_page_content'|@translate}</strong>
    <br>
    <textarea name="ap_content" id="ap_content" rows="30" cols="50" style="width:100%;">{if isset($CONTENT)}{$CONTENT}{/if}</textarea>
    {if isset($EXTDESC_BUTTON)}{$EXTDESC_BUTTON}{/if}
  </p>

  <p>
    <input class="submit" type="submit" value="{'ap_save'|@translate}" name="save">
{if isset($delete)}
    <input class="submit" type="submit" value="{'ap_delete'|@translate}" name="delete" onclick="return confirm('{'Are you sure?'|@translate}');"/>
{/if}
</form>
