<?php
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */

DrawPageTitle('Historie transakcí');
?>
<CENTER>
<?php
require_once('./finance_filters.inc.php');
require_once('./ct_renderer_fin_history.inc.php');

$filter = new FinanceHistoryFilter($_GET);
$historyLoadFailed = false;
try {
	$expandedRanges = $filter->getExpandedRanges();
	$zaznamy = FinanceHistoryRepository::findForRanges($filter, $expandedRanges);
} catch (RuntimeException $exception) {
	$expandedRanges = [];
	$zaznamy = [];
	$historyLoadFailed = true;
}

$filterDateFrom = (string)$filter->getValue('date_from');
$filterDateTo = (string)$filter->getValue('date_to');
$filterMember = (string)$filter->getValue('member');
$filterAmountFrom = (string)$filter->getValue('amount_from');
$filterAmountTo = (string)$filter->getValue('amount_to');
$filterNote = (string)$filter->getValue('note');
$filterClaimOnly = (bool)$filter->getValue('claim_only');
?>
<form method="get" action="index.php" style="margin-bottom: 10px;">
	<input type="hidden" name="id" value="<?=_FINANCE_GROUP_ID_;?>">
	<input type="hidden" name="subid" value="7">
	Od data: <input type="date" name="date_from" value="<?=htmlspecialchars($filterDateFrom)?>">
	Do data: <input type="date" name="date_to" value="<?=htmlspecialchars($filterDateTo)?>">
	Člen: <input type="text" name="member" placeholder="reg. č. nebo jméno" value="<?=htmlspecialchars($filterMember)?>">
	Částka od: <input type="number" name="amount_from" value="<?=htmlspecialchars($filterAmountFrom)?>">
	do: <input type="number" name="amount_to" value="<?=htmlspecialchars($filterAmountTo)?>">
	Popis: <input type="text" name="note" placeholder="text v poznámce" value="<?=htmlspecialchars($filterNote)?>">
	<label><input type="checkbox" name="claim_only" value="1" <?=$filterClaimOnly ? 'checked' : ''?>> jen reklamace</label>
	<input type="submit" value="Filtrovat">
	<?php if ($filter->hasActiveFilters()): ?>
		<a href="index.php?id=<?=_FINANCE_GROUP_ID_;?>&subid=7">Zrušit filtr</a>
	<?php endif; ?>
</form>
<?php

if ($historyLoadFailed) {
	echo '<div class="ErrorText">Transakce se nepodařilo načíst.</div>';
} else {
	$endpointParams = $filter->getActiveParams();
	$endpoint = 'fin_history_range.php';
	if (count($endpointParams) > 0) {
		$endpoint .= '?'.http_build_query($endpointParams);
	}

	$tblRenderer = FinanceHistoryRendererFactory::createHistoryTable();
	$tblRenderer->setFilter($filter);
	$tblRenderer->setRangeExpansionEndpoint($endpoint);

	echo $tblRenderer->render(new html_table_mc(), $zaznamy);
}
?>
<BR>
</CENTER>
