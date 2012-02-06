<?php /* finance -  show exact user finance */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>

<?
@$vysledek=MySQL_Query("select fin.amount amount, fin.note note, us.sort_name name, fin.date `date` from ".TBL_FINANCE." fin inner join ".TBL_USER." us on fin.id_users_editor = us.id
		where fin.id_users_user = ".$user_id." order by fin.id desc");

//vytazeni jmena uzivatele a posunuti ukazatele zpatky 
//TODO nutno pridat vytazeni i financi z users
//TODO pri zalozeni uzivatele vlozit finance do users = 0
//tim se vyresi problem s jmenem
if (mysql_num_rows($vysledek) > 0)
{
	$zaznam=MySQL_Fetch_Array($vysledek);
	mysql_data_seek($vysledek, 0);
}
else
{	// reseni pokud nema zatim zadny zaznam ve financich !
	@$vysledek=MySQL_Query("select us.sort_name name from ".TBL_USER." us where us.id = ".$user_id);
	$zaznam=MySQL_Fetch_Array($vysledek);
}
DrawPageSubTitle('Historie úètu pro '.$zaznam['name']);

include_once ("./common_race.inc.php");
include_once ('./url.inc.php');


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
$i = 0;
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
if ($i > 0)
	echo $data_tbl->get_break_row()."\n";

$row = array();
$row[] = null;
$row[] = "Koneèný zùstatek";
$row[] = null;
$row[] = $sum_amount;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_footer()."\n";

// formular patri opravdu sem ??
//TODO doplnit formular pro pridani platby
// if (IsLoggedFinance()) 
//	include "./payment_create_form.inc.php";

?>
