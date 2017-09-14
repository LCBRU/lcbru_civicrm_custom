{if $outputMode != 'pdf' && $outputMode != 'print'}
	{* Use the default layout *}
    {include file="CRM/Report/Form.tpl"}
{else}

    {assign var=l value=$smarty.ldelim}
    {assign var=r value=$smarty.rdelim}

	<style type="text/css">
		body {$l}
			font-family: Arial, Helvetica, sans-serif;
			margin: 0;
			padding: 0;
		{$r}

		#crm-container h1.practiceHeader {$l}
			text-align: center;
			margin: 0;
		{$r}

		#crm-container h2.practiceSubheader {$l}
			text-align: center;
			margin-top: 0;
			color: #999;
		{$r}

		table {$l}
			margin: 20px auto;
		{$r}

		td, th {$l}
			padding: 0 20px;
			white-space: nowrap;
		{$r}

		.report-layout {$l}
			page-break-after: always;
			border-collapse: collapse;
		{$r}

		.report-layout td, .report-layout th {$l}
			border: solid 1px black;
		{$r}

		.numericField {$l}
			text-align: right;
		{$r}

		.highlighted {$l}
			color: red;
		{$r}

	</style>

    {capture assign="tableHeader"}
        <table class="report-layout display">
			<tbody>
				<tr>
					<th>Start Date</th>
					<th>Recruitment Status</th>
					<th>Name</th>
					<th>Participant ID</th>
				</tr>
    {/capture}

    {capture assign="tableFooter"}
			</tbody>
		</table>
    {/capture}

    {foreach from=$rows item=row key=rowid}
        {assign var=practiceName value=$row.recruitment_report_practice_name}
        {isValueChange value=$practiceName key="recruitment_report_practice_name" assign=isValueChanged}
        {if $isValueChanged}

            {$tableFooter}

            <div>
            	<h1 class='practiceHeader'>{$practiceName}</h1>
            	<h2 class='practiceSubheader'>{$caseType} Reimbursement Report</h2>
            	<table>
            		<tr>
            			<td>Patients Recruited</td>
            			<td class="numericField">{$practiceTotals.$practiceName.count}</td>
            		</tr>
            		<tr>
            			<td>Excluded</td>
            			<td class="numericField">{$practiceTotals.$practiceName.excluded}</td>
            		</tr>
            		<tr>
            			<td>Submitted for Reimbursement on {$smarty.now|date_format}</td>
            			<td class="numericField">{$practiceTotals.$practiceName.reimbursed}</td>
            		</tr>
            		<tr>
            			<td>Reimbursement Value</td>
            			<td class="numericField">{$practiceTotals.$practiceName.reimbursementValue|crmMoney}</td>
            		</tr>
            	</table>
            </div>

            {$tableHeader}
        {/if}

    	<tr class="{cycle values="odd-row,even-row"} {$row.class} crm-report {if $row.recruitment_report_is_excluded eq 1 } highlighted{/if}">
    		<td>{$row.recruitment_report_start_date|date_format}</td>
    		<td>{$row.recruitment_report_case_status}</td>
    		<td>{$row.recruitment_report_sort_name}</td>
    		<td>{$row.recruitment_report_patient_study_id}</td>
    	</tr>
    {/foreach}
    {$tableFooter}

{/if}

