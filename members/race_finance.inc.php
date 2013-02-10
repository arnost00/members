<?php /* finance -  show exact race finance */
?>

<?
// prihlaseni 
// select u.id u_id, u.sort_name, f.id, f.amount from tst_users u inner join
// tst_zavxus zu on u.id = zu.id_user inner join
// tst_finance f on f.id_users_user = zu.id_user
// where zu.id_zavod = 35 and f.storno is null

$query_prihlaseni = "
select u.id u_id, u.sort_name, f.id, f.amount, f.note, zu.kat from ".TBL_USER." u inner join
".TBL_ZAVXUS." zu on u.id = zu.id_user left join
(select * from ".TBL_FINANCE." where id_zavod = $race_id and storno is null) f on f.id_users_user = zu.id_user
where zu.id_zavod = $race_id order by u_id, id
";
$vysledek_prihlaseni = mysql_query($query_prihlaseni);

// platici 
// select u.id u_id, u.sort_name, f.id, f.amount from tst_users u inner join
// tst_finance f on f.id_users_user = u.id
// where f.id_zavod = 35 and f.storno is null

$query_platici = "
select u.id u_id, u.sort_name, f.id, f.amount, f.note, null kat from ".TBL_USER." u inner join
(select * from ".TBL_FINANCE." where id_zavod = $race_id and storno is null) f on f.id_users_user = u.id
where f.id_zavod = $race_id
and u.id not in (select id_user from ".TBL_ZAVXUS." where id_zavod = $race_id)
order by u_id, id
";
$vysledek_platici = mysql_query($query_platici);

// neprihlaseni bez platby
// select u.id, u.sort_name, f.id, f.amount from tst_users u where id not in
// (SELECT distinct(f.id_users_user) id
// FROM tst_finance f where f.id_zavod = 35
// union
// SELECT distinct(zu.id_user) id
// FROM tst_zavxus zu where zu.id_zavod = 35)

$query_neprihlaseni = "
select u.id u_id, u.sort_name, null id, null amount, null note, null kat from ".TBL_USER." u where id not in
(SELECT distinct(f.id_users_user) id
FROM ".TBL_FINANCE." f where f.id_zavod = $race_id and f.storno is null
union
SELECT distinct(zu.id_user) id
FROM ".TBL_ZAVXUS." zu where zu.id_zavod = $race_id) order by u_id, id
";
$vysledek_neprihlaseni = mysql_query($query_neprihlaseni);


//vytazeni informaci o zavode
@$vysledek_race=MySQL_Query("select z.nazev, from_unixtime(z.datum, '%Y-%c-%d') datum from ".TBL_RACE." z where z.id = ".$race_id);
$zaznam_race=MySQL_Fetch_Array($vysledek_race);


DrawPageSubTitle('Historie ��etnictv� pro '.$zaznam_race['nazev'].' '.formatDate($zaznam_race['datum']));

include_once ("./common_race.inc.php");
include_once ('./url.inc.php');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Jm�no',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'��stka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Pozn�mka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_LEFT);
IsLoggedFinance()?$data_tbl->set_header_col($col++,'Mo�nosti',ALIGN_LEFT):"";


echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_amount = 0;
$i = 0;

$row = array();
$row[] = "P�ihl�en� s platbama i bez";
$row[] = null;
$row[] = null;
$row[] = null;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_break_row()."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_prihlaseni))
{
	$row = array();
	$row[] = $zaznam['sort_name'];
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['note'];
	$row[] = $zaznam['kat'];
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
// echo $data_tbl->get_break_row()."\n";
//---------------------------------------------------
echo $data_tbl->get_new_row("&nbsp;","","","")."\n";
$row = array();
$row[] = "Nep�ihl�en� s platbama";
$row[] = null;
$row[] = null;
$row[] = null;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_break_row()."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_platici))
{
	$row = array();
	$row[] = $zaznam['sort_name'];
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['note'];
	$row[] = $zaznam['kat'];
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
// echo $data_tbl->get_break_row()."\n";
//---------------------------------------------------
echo $data_tbl->get_new_row("&nbsp;","","","")."\n";
$row = array();
$row[] = "Nep�ihl�en�";
$row[] = null;
$row[] = null;
$row[] = null;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_break_row()."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_neprihlaseni))
{
	$row = array();
	$row[] = $zaznam['sort_name'];
	$row[] = $zaznam['amount'];
	$row[] = $zaznam['note'];
	$row[] = $zaznam['kat'];
	// 	IsLoggedFinance()?$row[]=" <a href=\"?change=change&trn_id=".$zaznam['fin_id']."\">Zm�nit</a> / <a href=\"?storno=storno&trn_id=".$zaznam['fin_id']."\">Storno</a>":"";
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}



//if ($i > 0) echo $data_tbl->get_break_row()."\n";

// $row = array();
// $row[] = null;
// $row[] = "Kone�n� z�statek";
// $row[] = null;
// $row[] = $sum_amount;
// echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_footer()."\n";

// formular patri opravdu sem ??
//TODO doplnit formular pro pridani platby
// if (IsLoggedFinance()) 
//	include "./payment_create_form.inc.php";

?>
