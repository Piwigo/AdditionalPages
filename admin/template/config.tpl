<div class="titrePage">
	<h2>Additional Pages - {'Configuration'|@translate}</h2>
</div>

<form name="apform" method="post" action="" class="properties"  ENCTYPE="multipart/form-data">
<fieldset id="indexDisplayConf">
  <legend>{'ap_perm'|@translate}</legend>
  <ul>
    <li>
      <label>
        <span class="property">{'ap_user_perm'|@translate}</span>
        <input type="checkbox" name="user_perm" id="user_perm" value="on" {if $ap_conf.user_perm}checked="checked"{/if}/>
      </label>
    </li>

    <li>
      <label>
        <span class="property">{'ap_group_perm'|@translate}</span>
        <input type="checkbox" name="group_perm" id="group_perm" value="on" {if $ap_conf.group_perm}checked="checked"{/if}/>
      </label>
    </li>
  </ul>
</fieldset>

<fieldset id="indexDisplayConf">
  <legend>{'Display'|@translate}</legend>
  <ul>
    <li>
      <label>
        <span class="property">{'ap_show_home'|@translate}</span>
        <input type="checkbox" name="show_home" id="show_home" value="on" {if $ap_conf.show_home}checked="checked"{/if}/>
      </label>
    </li>

    <li>
      <label>
        <span class="property">{'ap_show_menu'|@translate}</span>
        <input type="checkbox" name="show_menu" id="show_menu" value="on" {if isset($SHOW_MENU)}checked="checked"{/if}/>
      </label>
    </li>

    <li id="menu_name" style="display:none;">
      <span class="property">{'ap_menu_name'|@translate} :
        <select name="lang_desc_select" style="margin-left:30px;">
          {foreach from=$language item=lang}
            <option value="{$lang.LANGUAGE_CODE}" id="opt_{$lang.LANGUAGE_CODE}">{if empty($lang.VALUE)}&#x2718;{else}&#x2714;{/if} &nbsp;{$lang.LANGUAGE_NAME}</option>
          {/foreach}
        </select>
        {foreach from=$language item=lang}
          <input type="text" size="50" name="menu_lang[{$lang.LANGUAGE_CODE}]" id="menu_lang_{$lang.LANGUAGE_CODE}" value="{$lang.VALUE}" style="display:none; margin-left:10px;">
        {/foreach}
      </span>
    </li>
  </ul>
</fieldset>
	<p><input class="submit" type="submit" value="{'Submit'|@translate}" name="submit"/></p>
</form>

<script type="text/javascript">
var languages = new Array();
var filled = new Array;
{foreach from=$language item=lang}
languages["{$lang.LANGUAGE_CODE}"] = "{$lang.LANGUAGE_NAME}";
if ($('input[id=menu_lang_{$lang.LANGUAGE_CODE}]').val() != '')
  filled.push("{$lang.LANGUAGE_CODE}");
{/foreach}

jQuery().ready( function () {ldelim}
  jQuery("#show_menu").click( function() {ldelim}
    if (this.checked) {ldelim}
      jQuery('#menu_name').show();
    }
    else {ldelim}
      jQuery('#menu_name').hide();
    }
  });
  $('select[name="lang_desc_select"]').change(function () {ldelim}
    $('[id^="menu_lang"]').hide();
    $("#menu_lang_"+this.options[this.selectedIndex].value).show();
  });
  $('[id^="menu_lang_"]').keyup(function () {ldelim}
    arr = $(this).attr("id").split("menu_lang_");
    id = arr[1];
    opt = $('select[name="lang_desc_select"] option[id="opt_'+id+'"]');
    if (this.value != '') {ldelim}
      opt.html(opt.html().replace("\u2718", "\u2714"));
      add = true;
      for (i in filled) {ldelim}
        if (filled[i] == id) add = false;
      }
      if (add) {ldelim}
        filled.push(id);
      }
    }
    else {ldelim}
      for (i in filled) {ldelim}
        if (filled[i] == id) filled.splice(i, 1);
      }
      opt.html(opt.html().replace("\u2714", "\u2718"));
    }
  });
});

jQuery('#menu_lang_default').show();
if (jQuery("input[name='show_menu']").attr('checked')) {ldelim}
  jQuery('#menu_name').show();
}
</script>
