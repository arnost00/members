<?php /* finance -  show exact user finance */
 /* zamezeni samostatneho vykonani */ ?>

<?
@$vysledek_historie=MySQL_Query("select fin.id fin_id, rc.nazev zavod_nazev, from_unixtime(rc.datum,'%Y-%c-%d') zavod_datum, fin.amount amount, fin.note note, us.sort_name name, fin.date `date` from ".TBL_FINANCE." fin 
		inner join ".TBL_USER." us on fin.id_users_editor = us.id
		left join ".TBL_RACE." rc on fin.id_zavod = rc.id
		where fin.id_users_user = ".$user_id." and fin.storno is null  order by fin.id desc");

//vytazeni jmena uzivatele a posunuti ukazatele zpatky 
//TODO nutno pridat vytazeni i financi z users
//TODO pri zalozeni uzivatele vlozit finance do users = 0
//tim se vyresi problem s jmenem
//vytazeni jmena uzivatele
@$vysledek_user_name=MySQL_Query("select us.sort_name name from ".TBL_USER." us where us.id = ".$user_id);
$zaznam_user_name=MySQL_Fetch_Array($vysledek_user_name);

DrawPageSubTitle('Historie úètu pro '.$zaznam_user_name['name']);

include_once ("./common_race.inc.php");
include_once ('./url.inc.php');


$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum transakce',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Závod',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Datum závodu',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Zapsal',ALIGN_LEFT);
IsLoggedFinance()?$data_tbl->set_header_col($col++,'Možnosti',ALIGN_LEFT):"";


echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_amount = 0;
$i = 0;
while ($zaznam=MySQL_Fetch_Array($vysledek_historie))
{
	$row = array();
	$datum = formatDate($zaznam['date']);
	$row[] = $datum;
	$row[] = $zaznam['zavod_nazev'];
	$row[] = formatDate($zaznam['zavod_datum']);
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['note'];
	$row[] = $zaznam['name'];
	IsLoggedFinance()?$row[]=" <a href=\"?change=change&trn_id=".$zaznam['fin_id']."\">Zmìnit</a> / <a href=\"?storno=storno&trn_id=".$zaznam['fin_id']."\">Storno</a>":"";
	
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
