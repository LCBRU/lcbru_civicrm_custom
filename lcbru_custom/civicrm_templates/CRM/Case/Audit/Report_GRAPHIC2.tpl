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
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
<head>
  <title>{$pageTitle}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <base href="{crmURL p="" a=1}" /><!--[if IE]></base><![endif]-->
  <style type="text/css" media="screen, print">
    html {ldelim}
        padding: 0;
        margin: 0;
        font-size: 20px;
   {rdelim}

    body {ldelim}
        width: 800px;
        margin: 0 auto;
        position: relative;
        padding: 0;
        font-family: sans-serif;
   {rdelim}

    h1 {ldelim}
        margin-top: 0;
        margin-bottom: 20px;
        text-align: center;
   {rdelim}

    div {ldelim}
        margin-bottom: 10px;
   {rdelim}

    h2 {ldelim}
        margin-top: 20px;
        margin-bottom: 10px;
        font-size: 1.2em;
   {rdelim}

    #report-date {ldelim}
        float: none;
        position:absolute;
        right: 0;
        top: 0;
        color: #666;
        font-size: 0.6em;
   {rdelim}

    #report-version {ldelim}
        float: none;
        position:absolute;
        left: 0;
        top: 0;
        color: #666;
        font-size: 0.6em;
   {rdelim}

    label {ldelim}
        font-weight: bold;
        padding-right: 10px;
        display: inline-block;
   {rdelim}

    .address {ldelim}
        margin-top: 0;
        margin-left: 40px;
   {rdelim}

    td {ldelim}
        vertical-align: top;
        padding-right: 20px;
   {rdelim}

    #familyNumber {ldelim}
        font-size: 25px;
        position: absolute;
        left: 0px;
        font-weight: bold;
   {rdelim}

    #labID {ldelim}
        font-size: 25px;
        position: absolute;
        right: 0px;
        font-weight: bold;
   {rdelim}

    #idNumber {ldelim}
        font-size: 25px;
        display: block;
        text-align: center;
   {rdelim}

    #idNumber label {ldelim}
        font-weight: normal;
   {rdelim}

    #name {ldelim}
        margin-top: 30px;
        font-size: 25px;
        font-weight: bold;
   {rdelim}
  </style>
</head>

<body>
<div id="crm-container" class="crm-container">
{crmAPI entity='contact' action="getsingle" var="contact" contact_id=$clientID}
{crmAPI var="familyNumberField" entity="CustomField" action="getsingle" version="3" sequential="0" name=$smarty.const.FAMILY_ID_NAME}
{assign var="familyNumberId" value=$familyNumberField.id}
{crmAPI var="idNumberField" entity="CustomField" action="getsingle" version="3" sequential="0" name=$smarty.const.GRAPHIC_ID_NAME}
{assign var="idNumberId" value=$idNumberField.id}
{crmAPI var="labIdField" entity="CustomField" action="getsingle" version="3" sequential="0" name=$smarty.const.LAB_ID_NAME}
{assign var="labIdId" value=$labIdField.id}
{crmAPI var="customvalues" entity="CustomValue" action="get" version="3" sequential="0" entity_id=$caseId entity_table="Case"}
{crmAPI var="phoneTypeOptionGroup" entity="OptionGroup" action="getsingle" version="3" sequential="0" name="phone_type"}
{crmAPI var="phoneTypesUnprocessed" entity="OptionValue" action="get" version="3" sequential="0" option_group_id=$phoneTypeOptionGroup.id}
{crmAPI var="phoneNumbers" entity="Phone" action="get" version="3" sequential="0" contact_id=$clientID}
{crmAPI var="family" entity="Case" action="getfamily" version="3" sequential="0" graphic_family_id=$customvalues.values.$familyNumberId.latest}
{crmAPI var="gpSurgeryRelationshipType" entity="RelationshipType" action="get" version="3" sequential="0" name_a_b=$smarty.const.CIVI_REL_SURGERY_PATIENT}
{crmAPI var="gpSurgeryRelationship" entity="Relationship" action="getsingle" version="3" sequential="0" relationship_type_id=$gpSurgeryRelationshipType.id contact_id_a=$clientID is_active=1}
{crmAPI var="gpSurgery" entity="Contact" action="getsingle" version="3" sequential="0" contact_id=$gpSurgeryRelationship.contact_id_b}

{* Find the Appointment Activity. Should be easy, eh?  Just watch. *}

{foreach from=$activities item=activity}

    {* Here I'm trying to find the activity for the appointment *}
    {assign var="IsTheAppointmentActivity" value='0'}
    {foreach from=$activity item=activityValue}
        {if $activityValue.label == "Activity Type" && $activityValue.value == $smarty.const.CIVI_ACTIVITY_RECRUIT_AND_INTERVIEW }
            {assign var="IsTheAppointmentActivity" value='1'}
        {/if}
    {/foreach}

    {* Now I'm looping through again and finding the values *}
    {if $IsTheAppointmentActivity == "1"}
        {foreach from=$activity item=activityValue}
            {if $activityValue.label == "Medium" }
                {assign var="AppointmentMedium" value=$activityValue.value}
            { elseif $activityValue.label == "Location" }
                {assign var="AppointmentLocation" value=$activityValue.value}
            { elseif $activityValue.label == "Date and Time" }
                {assign var="AppointmentDateAndTime" value=$activityValue.value}
            {/if}
        {/foreach}
    {/if}
{/foreach}

{assign var="birth_year" value=$contact.birth_date|date_format:'%Y'}
{assign var="birth_month" value=$contact.birth_date|date_format:'%m'}
{assign var="birth_day" value=$contact.birth_date|date_format:'%d'}
{assign var="current_year" value=$smarty.now|date_format:'%Y'}
{assign var="current_month" value=$smarty.now|date_format:'%m'}
{assign var="current_day" value=$smarty.now|date_format:'%d'}
{if $current_day < $birth_day}
    {assign var="current_month" value=$current_month-1}
{/if}
{if $current_month < $birth_month}
    {assign var="current_year" value=$current_year-1}
{/if}

<h1>{$case.caseType}</h1>

<div id="report-date">{$reportDate}</div>
<div id="report-version">version: 1.0</div>

<div class="vertical">
  <span id="familyNumber">
    <label>Family</label>
    <span>{$customvalues.values.$familyNumberId.latest}</span>
  </span>
  <span id="labID">
    <label>Lab ID</label>
    <span>{$customvalues.values.$labIdId.latest}</span>
  </span>
  <span id="idNumber">
    <label>ID Number</label>
    <span>{$customvalues.values.$idNumberId.latest}</span>
  </span>
</div>

<div id="name">
    <label>Name</label>
    <span>{$contact.last_name|upper}, {$contact.first_name}</span>
</div>

<div>
    <label>Address</label>
    <p class="address">
        {if strlen($contact.supplemental_address_1) gt 0}
            {$contact.supplemental_address_1}<br/>
        {/if}
        {if strlen($contact.street_address) gt 0}
            {$contact.street_address}<br/>
        {/if}
        {if strlen($contact.supplemental_address_2) gt 0}
            {$contact.supplemental_address_2}<br/>
        {/if}
        {if strlen($contact.city) gt 0}
            {$contact.city}<br/>
        {/if}
        {if strlen($contact.state_province_name) gt 0}
            {$contact.state_province_name}<br/>
        {/if}
        {if strlen($contact.postal_code) gt 0}
            {$contact.postal_code}<br/>
        {/if}
        {if strlen($contact.country) gt 0}
            {$contact.country}
        {/if}
    </p>
</div>

<div class="vertical">
    <label>Age</label>
    <span>{$current_year-$birth_year}</span>
    <label>DOB</label>
    <span>{$contact.birth_date|date_format:'%d-%m-%Y'}</span>
</div>

<h2>Phone Numbers</h2>

{foreach from=$phoneNumbers.values item=phoneNumber}
    {assign var="thisPhoneType" value=$phoneNumber.phone_type_id}
    {foreach from=$phoneTypesUnprocessed.values item=phoneType}
        {if $phoneType.value == $phoneNumber.phone_type_id}
            {assign var="phoneTypeName" value=$phoneType.name}
        {/if}
    {/foreach}

    <div>
        <label>{$phoneTypeName}</label>
        <span>{$phoneNumber.phone}</span>
    </div>
{/foreach}

<h2>Family Members</h2>

<table>
    <thead>
        <tr style="display: none;">
            <th>Name</th>
            <th>ID</th>
            <th>Lab ID</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$family.values item=familyMember}
            {if $familyMember.client_id != $clientID}
                <tr>
                    <td>{$familyMember.last_name|upper}, {$familyMember.first_name}</td>
                    <td><label>ID</label>{$familyMember.participant_id}</td>
                    <td><label>Lab ID</label>{$familyMember.lab_id}</td>
                    <td>
                        <table>
                            {crmAPI var="familyMemberPhoneNumbers" entity="Phone" action="get" version="3" sequential="0" contact_id=$familyMember.client_id}
                            {foreach from=$familyMemberPhoneNumbers.values item=phoneNumber}
                                {assign var="thisPhoneType" value=$phoneNumber.phone_type_id}
                                {foreach from=$phoneTypesUnprocessed.values item=phoneType}
                                    {if $phoneType.value == $phoneNumber.phone_type_id}
                                        {assign var="phoneTypeName" value=$phoneType.name}
                                    {/if}
                                {/foreach}

                                <div>
                                    <label>{$phoneTypeName}</label>
                                    <span>{$phoneNumber.phone}</span>
                                </div>
                            {/foreach}
                        </table>
                    </td>
                </tr>
            {/if}
        {/foreach}
    </tbody>
</table>

<div>
    <label>GP Name and Address</label>
    <p class="address">
        {$gpSurgery.display_name}<br/>
        {if strlen($gpSurgery.supplemental_address_1) gt 0}
            {$gpSurgery.supplemental_address_1}<br/>
        {/if}
        {if strlen($gpSurgery.street_address) gt 0}
            {$gpSurgery.street_address}<br/>
        {/if}
        {if strlen($gpSurgery.supplemental_address_2) gt 0}
            {$gpSurgery.supplemental_address_2}<br/>
        {/if}
        {if strlen($gpSurgery.city) gt 0}
            {$gpSurgery.city}<br/>
        {/if}
        {if strlen($gpSurgery.state_province_name) gt 0}
            {$gpSurgery.state_province_name}<br/>
        {/if}
        {if strlen($gpSurgery.postal_code) gt 0}
            {$gpSurgery.postal_code}<br/>
        {/if}
        {if strlen($gpSurgery.country) gt 0}
            {$gpSurgery.country}
        {/if}
    </p>
</div>

<div>
    <label>Appointment Time</label>
    <span>{$AppointmentDateAndTime|date_format:"%A %e %B at %l:%M%p"}</span>
</div>
</div>
    <label>Appointment Location</label>
    <span>{$AppointmentMedium}</span>
    {if strlen($AppointmentLocation) gt 0}
        <span> ({$AppointmentLocation})</span>
    {/if}
</div>

</body>
</html>





