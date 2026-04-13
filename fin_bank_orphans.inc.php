<?php /* financnik - seznam nespárovaných plateb */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Nespárované bankovní platby');
?>
<CENTER>
<script language="javascript">
<!-- 
	javascript:set_default_size(800,800);
//-->
</script>
<?php

require_once "./common_user.inc.php";

$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : date('Y-m-d', strtotime('-30 day'));
$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : date('Y-m-d');

?>
<form method="post" action="index.php?id=<?=_FINANCE_GROUP_ID_;?>&subid=6">
	Od data: <input type="date" name="date_from" value="<?=htmlspecialchars($date_from)?>">
	Do data: <input type="date" name="date_to" value="<?=htmlspecialchars($date_to)?>">
	<input type="submit" value="Filtrovat">
</form>
<br>
<?php

$sql_date_from = correct_sql_string($date_from) . ' 00:00:00';
$sql_date_to = correct_sql_string($date_to) . ' 23:59:59';

$query = "SELECT id, created_at, amount, currency, variable_symbol, constant_symbol, specific_symbol, originator_message 
          FROM ".TBL_BANK_TRANSACTIONS." 
          WHERE status = 'ORPHAN' 
            AND created_at >= '$sql_date_from' 
            AND created_at <= '$sql_date_to' 
          ORDER BY created_at DESC";

@$vysledek=query_db($query);

$i=1;
if ($vysledek != FALSE && mysqli_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Datum',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Částka',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Měna',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'VS',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Zpráva',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$row = array();
		$row[] = $i++;
		$row[] = date('d.m.Y H:i:s', strtotime($zaznam['created_at']));
		$row[] = $zaznam['amount'];
		$row[] = $zaznam['currency'];
		$row[] = $zaznam['variable_symbol'];
		$row[] = $zaznam['originator_message'];
		$row[] = '<A HREF="javascript:open_win(\'./fin_bank_orphan_assign.php?tx_id='.$zaznam['id'].'\',\'\')">Přiřadit</A>';
		
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
	echo $data_tbl->get_footer()."\n";
} else {
	echo "Nebyly nalezeny žádné nespárované platby v tomto období.";
}

?>

<BR>
</CENTER>