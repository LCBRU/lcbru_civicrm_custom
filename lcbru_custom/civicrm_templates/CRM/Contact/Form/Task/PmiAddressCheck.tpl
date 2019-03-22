<h2>Summary</h2>
<table>
	<tbody>
		<tr>
			<td>Contacts</td>
			<td>{$form.contacts.html}</td>
		</tr>
		<tr>
			<td>Patient Not Found In PMI</td>
			<td>{$form.not_in_pmi.html}</td>
		</tr>
		<tr>
			<td>PMI Address Different to CiviCRM Address</td>
			<td>{$form.address_different.html}</td>
		</tr>
		<tr>
			<td>Address Missing in CiviCRM</td>
			<td>{$form.address_missing.html}</td>
		</tr>
		<tr>
			<td>Recently Deceased - Deceased in PMI, but not in CiviCRM</td>
			<td>{$form.newly_deceased.html}</td>
		</tr>
		<tr>
			<td>Disceased Mismatch - Deceased in CiviCRM, but not in the PMI</td>
			<td>{$form.deceased_mismatch.html}</td>
		</tr>
		<tr>
			<td>Missing NHS Number</td>
			<td>{$form.missing_nhs_number.html}</td>
		</tr>
		<tr>
			<td>NHS Number Mismatch</td>
			<td>{$form.nhs_number_mismatch.html}</td>
		</tr>
		<tr>
			<td>Missing Date of Birth</td>
			<td>{$form.missing_dob.html}</td>
		</tr>
		<tr>
			<td>Date of Birth Mismatch</td>
			<td>{$form.dob_mismatch.html}</td>
		</tr>
	</tbody>
</table>
<INPUT TYPE="button" onClick="location.reload();" VALUE="Refresh">
{if $form.not_in_pmi.html != '0'}
<h2>Patient not in UHL PMI</h2>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>Date of Birth</th>
			<th>Address</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopNotInPmi start=0 loop=$form.not_in_pmi.html}
			{assign var=idx value=$smarty.section.loopNotInPmi.index}
			{assign var=contactIdIdx value="NoPmi_contactId_$idx"}
			{assign var=civiAddressIdx value="NoPmi_civiAddress_$idx"}
			{assign var=sNumberIdx value="NoPmi_sNumber_$idx"}
			{assign var=nameIdx value="NoPmi_name_$idx"}
			{assign var=dateOfBirthIdx value="NoPmi_dateOfBirth_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$dateOfBirthIdx.html|date_format:"%d %b %Y"}</td>
				<td>{$form.$civiAddressIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
{/if}

{if $form.address_different.html != '0'}
<h2>Unable to Match Address from CiviCRM with Address in UHL PMI</h2>
<p>Addresses are matched by house number and post code.  If either of these are missing or different, the addresses do not match.</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>Civi Address</th>
			<th>PMI Name</th>
			<th>PMI Address</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopDifferentAddress start=0 loop=$form.address_different.html}
			{assign var=idx value=$smarty.section.loopDifferentAddress.index}
			{assign var=contactIdIdx value="AD_contactId_$idx"}
			{assign var=civiAddressIdx value="AD_civiAddress_$idx"}
			{assign var=pmiAddressIdx value="AD_pmiAddress_$idx"}
			{assign var=sNumberIdx value="AD_sNumber_$idx"}
			{assign var=nameIdx value="AD_name_$idx"}
			{assign var=pmiNameIdx value="AD_pmiName_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$civiAddressIdx.html}</td>
				<td>{$form.$pmiNameIdx.html}</td>
				<td>{$form.$pmiAddressIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
{/if}

{if $form.address_missing.html != '0'}
<h2>Address Missing in CiviCRM</h2>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>PMI Name</th>
			<th>PMI Address</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopMissingAddress start=0 loop=$form.address_missing.html}
			{assign var=idx value=$smarty.section.loopMissingAddress.index}
			{assign var=contactIdIdx value="AM_contactId_$idx"}
			{assign var=pmiAddressIdx value="AM_pmiAddress_$idx"}
			{assign var=sNumberIdx value="AM_sNumber_$idx"}
			{assign var=nameIdx value="AM_name_$idx"}
			{assign var=pmiNameIdx value="AM_pmiName_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$pmiNameIdx.html}</td>
				<td>{$form.$pmiAddressIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
<div>
	{$form.import_addresses.html}
	{$form.import_addresses.label}
</div>
{/if}

{if $form.newly_deceased.html != '0'}
<h2>Recently Deceased</h2>
<p>Deceased in PMI, but not in CiviCRM</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>PMI Name</th>
			<th>PMI Deceased Date</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopNewlyDeceased start=0 loop=$form.newly_deceased.html}
			{assign var=idx value=$smarty.section.loopNewlyDeceased.index}
			{assign var=contactIdIdx value="ND_contactId_$idx"}
			{assign var=sNumberIdx value="ND_sNumber_$idx"}
			{assign var=nameIdx value="ND_name_$idx"}
			{assign var=pmiNameIdx value="ND_pmiName_$idx"}
			{assign var=pmiDeceasedDateIdx value="ND_pmiDeceasedDate_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$pmiNameIdx.html}</td>
				<td>{$form.$pmiDeceasedDateIdx.html|date_format:"%d %b %Y"}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
<div>
	{$form.flag_deceased.html}
	{$form.flag_deceased.label}
</div>

{/if}


{if $form.deceased_mismatch.html != '0'}
<h2>Deceased Status Mismatch</h2>
<p>Deceased in CiviCRM, but not in the PMI</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>CiviCRM Deceased Flag</th>
			<th>CiviCRM Deceased Date</th>
			<th>PMI Name</th>
			<th>PMI Deceased Flag</th>
			<th>PMI Deceased Date</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopDeceasedMismatch start=0 loop=$form.deceased_mismatch.html}
			{assign var=idx value=$smarty.section.loopDeceasedMismatch.index}
			{assign var=contactIdIdx value="DM_contactId_$idx"}
			{assign var=sNumberIdx value="DM_sNumber_$idx"}
			{assign var=nameIdx value="DM_name_$idx"}
			{assign var=civiDeceasedFlagIdx value="DM_civiDeceasedFlag_$idx"}
			{assign var=civiDeceasedDateIdx value="DM_civiDeceasedDate_$idx"}
			{assign var=pmiDeceasedFlagIdx value="DM_pmiDeceasedFlag_$idx"}
			{assign var=pmiNameIdx value="DM_pmiName_$idx"}
			{assign var=pmiDeceasedDateIdx value="DM_pmiDeceasedDate_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$civiDeceasedFlagIdx.html}</td>
				<td>{$form.$civiDeceasedDateIdx.html}</td>
				<td>{$form.$pmiNameIdx.html}</td>
				<td>{$form.$pmiDeceasedFlagIdx.html}</td>
				<td>{$form.$pmiDeceasedDateIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
{/if}


{if $form.nhs_number_mismatch.html != '0'}
<h2>NHS Number Mismatch</h2>
<p>NHS Numbers in CiviCRM and PMI do not match</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>CiviCRM NHS Number</th>
			<th>PMI NHS Number</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopDeceasedMismatch start=0 loop=$form.nhs_number_mismatch.html}
			{assign var=idx value=$smarty.section.loopDeceasedMismatch.index}
			{assign var=contactIdIdx value="NhsMismatch_contactId_$idx"}
			{assign var=sNumberIdx value="NhsMismatch_sNumber_$idx"}
			{assign var=nameIdx value="NhsMismatch_name_$idx"}
			{assign var=civiNhsNumberIdx value="NhsMismatch_cv_nhsnumber_$idx"}
			{assign var=pmiNhsNumberIdx value="NhsMismatch_pmi_nhsnumber_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$civiNhsNumberIdx.html}</td>
				<td>{$form.$pmiNhsNumberIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
{/if}


{if $form.missing_nhs_number.html != '0'}
<h2>NHS Number Missing</h2>
<p>NHS Numbers found in PMI that are missing from CiviCRM</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>PMI NHS Number</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopDeceasedMismatch start=0 loop=$form.missing_nhs_number.html}
			{assign var=idx value=$smarty.section.loopDeceasedMismatch.index}
			{assign var=contactIdIdx value="NhsMissing_contactId_$idx"}
			{assign var=sNumberIdx value="NhsMissing_sNumber_$idx"}
			{assign var=nameIdx value="NhsMissing_name_$idx"}
			{assign var=pmiNhsNumberIdx value="NhsMissing_pmi_nhsnumber_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$pmiNhsNumberIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
<div>
	{$form.import_missing_nhs_number.html}
	{$form.import_missing_nhs_number.label}
</div>
{/if}


{if $form.dob_mismatch.html != '0'}
<h2>Date of Birth Mismatch</h2>
<p>Date of Birth in CiviCRM and PMI do not match</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>CiviCRM date of Birth</th>
			<th>PMI Date of Birth</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopDeceasedMismatch start=0 loop=$form.dob_mismatch.html}
			{assign var=idx value=$smarty.section.loopDeceasedMismatch.index}
			{assign var=contactIdIdx value="DobMismatch_contactId_$idx"}
			{assign var=sNumberIdx value="DobMismatch_sNumber_$idx"}
			{assign var=nameIdx value="DobMismatch_name_$idx"}
			{assign var=civiDOBIdx value="DobMismatch_cv_dob_$idx"}
			{assign var=pmiNhsNumberIdx value="DobMismatch_pmi_dob_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$civiDOBIdx.html}</td>
				<td>{$form.$pmiNhsNumberIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
{/if}


{if $form.missing_dob.html != '0'}
<h2>Date of Birth Missing</h2>
<p>Dates of Birth found in PMI that are missing from CiviCRM</p>
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>UHL S Number</th>
			<th>PMI Date of Birth</th>
		</tr>
	</thead>
	<tbody>
		{section name=loopDeceasedMismatch start=0 loop=$form.missing_dob.html}
			{assign var=idx value=$smarty.section.loopDeceasedMismatch.index}
			{assign var=contactIdIdx value="DobMissing_contactId_$idx"}
			{assign var=sNumberIdx value="DobMissing_sNumber_$idx"}
			{assign var=nameIdx value="DobMissing_name_$idx"}
			{assign var=pmiDOBIdx value="DobMissing_pmi_dob_$idx"}

			<tr>
				<td><a href="/index.php?q=civicrm/contact/view&reset=1&cid={$form.$contactIdIdx.html}" target="_blank">{$form.$nameIdx.html}</a></td>
				<td>{$form.$sNumberIdx.html}</td>
				<td>{$form.$pmiDOBIdx.html}</td>
			</tr>
			
		{/section}
	</tbody>
</table>
<div>
	{$form.import_missing_dob.html}
	{$form.import_missing_dob.label}
</div>
{/if}


{$form.buttons.html}
