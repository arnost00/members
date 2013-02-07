<?php /* finance -  show exact race finance */
?>

<?
$query = "SELECT zu.id, zu.id_user, zu.id_zavod, zu.kat, z.nazev, u.sort_name, f.amount amount, f.note note, f.datum datum
FROM ".TBL_ZAVXUS." zu 
inner join ".TBL_RACE." z on z.id = zu.id_zavod 
inner join ".TBL_USER." u on u.id = zu.id_user
left join ".TBL_FINANCE." f on (f.id_users_user = u.id and f.id_zavod = z.id)
WHERE z.id = ".$race_id." and f.storno is null order by f.id desc";
echo $query;

//platby pro: prihlaseni union platici neprihlaseni 
// (
// select u.id u_id, u.sort_name, f.id, f.amount from tst_users u inner join
// tst_zavxus zu on u.id = zu.id_user left join
// tst_finance f on f.id_users_user = zu.id_user
// where zu.id_zavod = 35 and f.storno is null
// ) union (
// select u.id u_id, u.sort_name, f.id, f.amount from tst_users u inner join
// tst_finance f on f.id_users_user = u.id
// where f.id_zavod = 35 and f.storno is null
// )

//neprihlaseni uzivatele bez platby
// select id from tst_users where id not in
// (SELECT distinct(f.id_users_user) id
// FROM tst_finance f where f.id_zavod = 35
// union
// SELECT distinct(zu.id_user) id
// FROM tst_zavxus zu where zu.id_zavod = 35)


@$vysledek_historie=MySQL_Query($query);

//vytazeni informaci o zavode
@$vysledek_race=MySQL_Query("select z.nazev, from_unixtime(z.datum, '%Y-%c-%d') datum from ".TBL_RACE." z where z.id = ".$race_id);
$zaznam_race=MySQL_Fetch_Array($vysledek_race);


DrawPageSubTitle('Historie úèetnictví pro '.$zaznam_race['nazev'].' '.formatDate($zaznam_race['datum']));

include_once ("./common_race.inc.php");
include_once ('./url.inc.php');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum transakce',ALIGN_CENTER);
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
