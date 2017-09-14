{if $case.caseType == $smarty.const.CIVI_CASETYPE_GRAPHIC2_LABEL}
  {include file='CRM/Case/Audit/Report_GRAPHIC2.tpl'}
{else}
  {include file='CRM/Case/Audit/Report_Default.tpl'}
{/if}