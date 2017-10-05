<p>Submit {$form.contact_count.html} contact(s) to DAPS</p>

<div>
    {$form.file_name.label}
    {$form.file_name.html}
</div>

<p>If the filename is left blank, a filename of CiviCRM_Export will be used.  File names will be postfixed with the current date and time.</p>

<div>
    {$form.dowload_only.label}
    {$form.dowload_only.html}
</div>

<p>If checked, file will not be automatically uploaded to DAPS.  Instead the file will downloaded for you to check and upload manually.</p>

{$form.buttons.html}
