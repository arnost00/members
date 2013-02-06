<?php /* finance -  show exact user finance */
?>

<?
$query = "SELECT zu.id, zu.id_user, zu.id_zavod, zu.kat, z.nazev, u.sort_name, f.amount amount, f.note note
FROM ".TBL_ZAVXUS." zu 
inner join ".TBL_RACE." z on z.id = zu.id_zavod 
inner join ".TBL_USER." u on u.id = zu.id_user
left join ".TBL_FINANCE." f on (f.id_users_user = u.id and f.id_zavod = z.id)
WHERE z.id = ".$race_id." and f.storno is null order by f.id desc";
echo $query;
@$vysledek_historie=MySQL_Query($query);

//vytazeni jmena uzivatele
@$vysledek_user_name=MySQL_Query("select us.sort_name name from ".TBL_RACE." us where us.id = ".$user_id);
$zaznam_user_name=MySQL_Fetch_Array($vysledek_user_name);

DrawPageSubTitle('Historie úètu pro '.$zaznam_user_name['name']);
echo "qwewqe";
echo $Revision;
echo $LastChangedRevision;
echo $Rev;
echo "tretrte";

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
while ($zaznam=mysql_fetch_assoc($vysledek_historie))
{
	print_r($zaznam['']);
	$row = array();
	$datum = formatDate($zaznam['date']);
	$row[] = $datum;
	$row[] = $zaznam['nazev'];
	$row[] = formatDate($zaznam['zavod_datum']);
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['note'];
	$row[] = $zaznam['kat'];
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
