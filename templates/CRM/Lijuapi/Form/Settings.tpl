{*-------------------------------------------------------+
| SYSTOPIA LijuApi Extension                             |
| Copyright (C) 2022 SYSTOPIA                            |
| Author: P. Batroff (batroff@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*}

<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="top"}
</div>

<br/><h3>{ts domain='de.systopia.lijuapi'}Api Server Configuration{/ts}</h3><br/>

<div class="crm-section lijuapi lijuapi">
  <div class="crm-section">
    <div class="label">{$form.api_base_url.label}<a onclick='CRM.help("{ts domain='de.systopia.lijuapi'}Api BaseURL{/ts}", {literal}{"id":"id-lijuapi-baseurl","file":"CRM\/Lijuapi\/Form\/Settings"}{/literal}); return false;' href="#" title="{ts domain='de.systopia.lijuapi'}Help{/ts}" class="helpicon"></a></div>
    <div class="content">{$form.api_base_url.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.authorization_label.label}<a onclick='CRM.help("{ts domain='de.systopia.lijuapi'}Api Authorization Label{/ts}", {literal}{"id":"id-lijuapi-authorization_label","file":"CRM\/Lijuapi\/Form\/Settings"}{/literal}); return false;' href="#" title="{ts domain='de.systopia.lijuapi'}Help{/ts}" class="helpicon"></a></div>
    <div class="content">{$form.authorization_label.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.authorizaion_token.label}<a onclick='CRM.help("{ts domain='de.systopia.lijuapi'}Api Authorization Token{/ts}", {literal}{"id":"id-lijuapi-authorizaion_token","file":"CRM\/Lijuapi\/Form\/Settings"}{/literal}); return false;' href="#" title="{ts domain='de.systopia.lijuapi'}Help{/ts}" class="helpicon"></a></div>
    <div class="content">{$form.authorizaion_token.html}</div>
    <div class="clear"></div>
  </div>

</div>


<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
</div>
