<?php /* finance -  show exact user finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<?
@$vysledek=MySQL_Query("select us.sort_name name from ".TBL_USER." us inner join ".TBL_USXUS." usx on us.id = usx.id_users where usx.id_accounts = ".$account_id);
$zaznam=MySQL_Fetch_Array($vysledek);
?>
<H2 class="PageName">Hisotrie úètu pro <? echo $zaznam['name'] ?></H2>

<CENTER>
<style type="text/css">
.TextAlert21 {
	color: #00FF00;
}
.TextAlert7 {
	color: #FFFF00;
}
.TextAlert2 {
	color: #FF0000;
	text-decoration : blink;
}
.TextAlertExp {
	color: #666666;
}
</style>

<?php
include_once ("./common_race.inc.php");
include_once ('./url.inc.php');

@$vysledek=MySQL_Query("select fin.amount amount, fin.note note, us.sort_name name, fin.date `date` from ".TBL_FINANCE." fin inner join ".TBL_USXUS." usx on fin.id_accounts_editor = usx.id_accounts
		inner join ".TBL_USER." us on usx.id_users = us.id
		where fin.id_accounts_user = ".$account_id." order by fin.id desc");

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Zapsal',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_amount = 0;
//$brk_tbl = false;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$row = array();
	$datum = formatDate($zaznam['date']);
	$row[] = $datum;
	$row[] = $zaznam['note'];
	$row[] = $zaznam['name'];
	$row[] = $zaznam['amount'];
	$sum_amount += $zaznam['amount'];
	
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
echo $data_tbl->get_break_row()."\n";

$row = array();
$row[] = null;
$row[] = "Koneèný zùstatek";
$row[] = null;
$row[] = $sum_amount;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_footer()."\n";

//TODO doplnit formular pro pridani platby

?>

</CENTER>