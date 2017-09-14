{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
<div class="crm-block crm-form-block crm-contact-task-addtogroup-form-block">
<div id="status_mappings">
  <p class="status_mapping">
    <span class="ordinal">1.</span>
    When {$form.case_status_id.label} is <span class="case_status">{$form.case_status_id.html}</span>
    change {$form.activity_status_id.label} to <span>{$form.activity_status_id.html}</span>
  </p>
</div>
<table class="form-layout">
  <tr><td>{include file="CRM/Activity/Form/Task.tpl"}</td></tr>
</table>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
{include file="CRM/common/showHide.tpl"}
{literal}
<script type="text/javascript">
  jQuery(function() {
      attachOnChange();
      function potentiallyCreateNewStatusMapping() {

        var mappings = jQuery(".status_mapping");
        var mappingsForAllCaseStatuses = mappings.has(".case_status select option:selected[value='']");
        var redundantMappings = mappingsForAllCaseStatuses.first().nextAll();

        redundantMappings.remove();

        if (mappingsForAllCaseStatuses.length == 0) {
          var newMapping = jQuery(".status_mapping").first().clone();
          newMapping.find("select").each(function() {
            jQuery(this).attr('id', jQuery(this).attr('id') + mappings.length);
            jQuery(this).attr('name', jQuery(this).attr('name') + mappings.length);
          });
          newMapping.find(".ordinal").text('' + (mappings.length + 1) + '. ');
          newMapping.find(".case_status select option[value='']").text("All other statuses");
          newMapping.find(".case_status select").change(potentiallyCreateNewStatusMapping);
          newMapping.appendTo(jQuery("#status_mappings"));
        }
      }

      function attachOnChange() {
        jQuery(".case_status select").change(potentiallyCreateNewStatusMapping);
      }
  });
</script>
{/literal}

