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
{* this template is used for adding/editing other (custom) activities. *}

<h3>New activity</h3>
<div class="crm-block crm-form-block crm-activity-form-block">

	{* added onload javascript for source contact*}
	{literal}
		<script type="text/javascript">
			  var assignee_contact = '';

			  {/literal}
			  {if $assignee_contact}
			    var assignee_contact = {$assignee_contact};
			  {/if}
			  {literal}

			  //loop to set the value of cc and bcc if form rule.
			  var assignee_contact_id = null;
			  var toDataUrl = "{/literal}{crmURL p='civicrm/ajax/checkemail' q='id=1&noemail=1' h=0 }{literal}"; {/literal}
			  {foreach from=","|explode:"assignee" key=key item=element}
			    {assign var=currentElement value=`$element`_contact_id}
			    {if $form.$currentElement.value }
			      {literal} var {/literal}{$currentElement}{literal} = cj.ajax({ url: toDataUrl + "&cid={/literal}{$form.$currentElement.value}{literal}", async: false }).responseText;{/literal}
			    {/if}
			  {/foreach}
			  {literal}

			  if ( assignee_contact_id ) {
			    eval( 'assignee_contact = ' + assignee_contact_id );
			  }

			cj(function( ) {

			var sourceDataUrl = "{/literal}{$dataUrl}{literal}";
			var hintText = "{/literal}{ts escape='js'}Type in a partial or complete name of an existing contact.{/ts}{literal}";
			cj('#source_contact_id').autocomplete( sourceDataUrl, { width : 180, selectFirst : false, hintText: hintText, matchContains: true, minChars: 1, max: {/literal}{crmSetting name="search_autocomplete_count" group="Search Preferences"}{literal}})
				.result( function(event, data, formatted) {
					cj( "#source_contact_qid" ).val( data[1] );
				}).bind( 'click', function( ) {
					if (!cj("#source_contact_id").val()) {
						cj( "#source_contact_qid" ).val('');
					}
				});

		    var tokenDataUrl_assignee  = "{/literal}{$tokenUrl}&context=activity_assignee{literal}";
		    cj( "#assignee_contact_id").tokenInput( tokenDataUrl_assignee, { prePopulate: assignee_contact, theme: 'facebook', hintText: hintText });

			});
		</script>
	{/literal}

  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>

	<table class="form-layout">

		<tr class="crm-activity-form-block-activity_type_id">
			<td class="label">{$form.activity_type_id.label}</td>
			<td class="view-value">{$form.activity_type_id.html}</td>
		</tr>

		<tr class="crm-activity-form-block-source_contact_id">
			<td class="label">{$form.source_contact_id.label}</td>
			<td class="view-value">{$form.source_contact_id.html}</td>
		</tr>

		<tr class="crm-activity-form-block-assignee_contact_id">
		<td class="label">{ts}Assigned To{/ts}</td>
		<td>
		{$form.assignee_contact_id.html}
		{edit}
		<span class="description">{ts}You can optionally assign this activity to someone. Assigned activities will appear in their Activities listing at CiviCRM Home.{/ts}
		{if $activityAssigneeNotification}
		<br />{ts}A copy of this activity will be emailed to each Assignee.{/ts}
		{/if}
		</span>
		{/edit}
		</td>
		</tr>

		<tr class="crm-activity-form-block-subject">
			<td class="label">{$form.subject.label}</td>
			<td class="view-value">{$form.subject.html|crmAddClass:huge}</td>
		</tr>

	    <tr class="crm-case-activity-form-block-medium_id">
	      <td class="label">{$form.medium_id.label}</td>
	      <td class="view-value">{$form.medium_id.html}&nbsp;&nbsp;&nbsp;{$form.location.label} &nbsp;{$form.location.html|crmAddClass:huge}</td>
	    </tr>

		<tr class="crm-activity-form-block-activity_date_time">
			<td class="label">{$form.activity_date_time.label}</td>
			<td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=activity_date_time}</td>
		</tr>

		<tr class="crm-activity-form-block-duration">
			<td class="label">{$form.duration.label}</td>
			<td class="view-value">{$form.duration.html}<span class="description">{ts}Total time spent on this activity (in minutes).{/ts}</td>
		</tr>

		<tr class="crm-activity-form-block-status_id">
			<td class="label">{$form.status_id.label}</td>
			<td class="view-value">{$form.status_id.html}</td>
		</tr>

		<tr class="crm-activity-form-block-details">
			<td class="label">{$form.details.label}</td>
			<td class="view-value">
				{if $defaultWysiwygEditor eq 0}{$form.details.html|crmStripAlternatives|crmAddClass:huge}{else}{$form.details.html|crmStripAlternatives}{/if}
			</td>
		</tr>

		<tr class="crm-activity-form-block-priority_id">
			<td class="label">{$form.priority_id.label}</td>
			<td class="view-value">{$form.priority_id.html}</td>
		</tr>

		{if $form.tag.html}
			<tr class="crm-activity-form-block-tag">
				<td class="label">{$form.tag.label}</td>
				<td class="view-value">
					<div class="crm-select-container">{$form.tag.html}</div>
					{literal}
						<script type="text/javascript">
							cj(".crm-activity-form-block-tag select[multiple]").crmasmSelect({
								addItemTarget: 'bottom',
								animate: true,
								highlight: true,
								sortable: true,
								respectParents: true
							});
						</script>
					{/literal}
				</td>
			</tr>
		{/if}

	</table>

	<div class="crm-submit-buttons">
		{include file="CRM/common/formButtons.tpl" location="bottom"}
	</div>
</div>{* end of form block*}
