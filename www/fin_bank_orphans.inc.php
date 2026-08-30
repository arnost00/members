<?php /* financnik - seznam nespárovaných plateb */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */

DrawPageTitle('Nespárované bankovní platby');
?>
<CENTER>
<script language="javascript">
<!--
	javascript:set_default_size(800,800);
//-->
</script>
<?php

require_once './common_user.inc.php';
require_once './ct_renderer_bank_orphans.inc.php';
require_once './finance_filters.inc.php';

$filter = new BankTransactionFilter($_POST);
$dateFrom = (string)$filter->getValue('date_from');
$dateTo = (string)$filter->getValue('date_to');
$filterVs = (string)$filter->getValue('variable_symbol');
$filterAmountFrom = (string)$filter->getValue('amount_from');
$filterAmountTo = (string)$filter->getValue('amount_to');
$filterMessage = (string)$filter->getValue('message');
?>
<form method="post" action="index.php?id=<?=_FINANCE_GROUP_ID_;?>&subid=6">
	Od data: <input type="date" name="date_from" value="<?=htmlspecialchars($dateFrom)?>">
	Do data: <input type="date" name="date_to" value="<?=htmlspecialchars($dateTo)?>">
	VS: <input type="text" name="variable_symbol" placeholder="variabilní symbol" value="<?=htmlspecialchars($filterVs)?>">
	Částka od: <input type="number" name="amount_from" value="<?=htmlspecialchars($filterAmountFrom)?>">
	do: <input type="number" name="amount_to" value="<?=htmlspecialchars($filterAmountTo)?>">
	Zpráva: <input type="text" name="message" placeholder="text zprávy plátce" value="<?=htmlspecialchars($filterMessage)?>">
	<input type="submit" value="Filtrovat">
</form>
<br>
<?php

$fragment = $filter->getSqlFragment();
$query = "SELECT id, created_at, amount, currency, variable_symbol, constant_symbol, specific_symbol, originator_message "
	."FROM ".TBL_BANK_TRANSACTIONS." WHERE status = 'ORPHAN'";
if (!$fragment->isEmpty()) {
	$query .= ' AND '.$fragment->sql;
}
$query .= ' ORDER BY created_at DESC';

$bankPaymentsLoadFailed = false;
try {
	$stmt = db_prepare($query);
	if ($stmt === false) {
		throw new RuntimeException('Unable to prepare unmatched bank payments query.');
	}

	$vysledek = db_execute(true, $stmt, $fragment->types, $fragment->params);
	if ($vysledek === false) {
		throw new RuntimeException('Unable to execute unmatched bank payments query.');
	}
} catch (RuntimeException $exception) {
	$vysledek = false;
	$bankPaymentsLoadFailed = true;
}

if ($bankPaymentsLoadFailed) {
	echo '<div class="ErrorText">Bankovní platby se nepodařilo načíst.</div>';
} elseif (mysqli_num_rows($vysledek) > 0) {
	$zaznamy = mysqli_fetch_all($vysledek, MYSQLI_ASSOC);

	$tblRenderer = BankOrphanRendererFactory::createTable();
	$tblRenderer->addColumns('poradi', 'created_at', 'amount', 'currency', 'variable_symbol', 'originator_message', 'moznosti');

	echo $tblRenderer->render(new html_table_mc(), $zaznamy);
} else {
	echo 'Nebyly nalezeny žádné nespárované platby v tomto období.';
}
?>
<BR>
</CENTER>
