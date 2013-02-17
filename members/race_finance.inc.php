<?php /* finance -  show exact race finance */
?>

<?

$query_prihlaseni = "
select u.id u_id, u.sort_name, f.id, f.amount, f.note, zu.kat from ".TBL_USER." u inner join
".TBL_ZAVXUS." zu on u.id = zu.id_user left join
(select * from ".TBL_FINANCE." where id_zavod = $race_id and storno is null) f on f.id_users_user = zu.id_user
where zu.id_zavod = $race_id order by u_id, id
";
$vysledek_prihlaseni = mysql_query($query_prihlaseni);

$query_platici = "
select u.id u_id, u.sort_name, f.id, f.amount, f.note, null kat from ".TBL_USER." u inner join
(select * from ".TBL_FINANCE." where id_zavod = $race_id and storno is null) f on f.id_users_user = u.id
where f.id_zavod = $race_id
and u.id not in (select id_user from ".TBL_ZAVXUS." where id_zavod = $race_id)
order by u_id, id
";
$vysledek_platici = mysql_query($query_platici);

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
@$vysledek_race=MySQL_Query("select z.nazev, from_unixtime(z.datum, '%Y-%c-%e') datum from ".TBL_RACE." z where z.id = ".$race_id);
$zaznam_race=MySQL_Fetch_Array($vysledek_race);


DrawPageSubTitle('Historie úèetnictví pro '.$zaznam_race['nazev'].' '.formatDate($zaznam_race['datum']));

include_once ("./common_race.inc.php");
include_once ('./url.inc.php');

echo "<form method=\"post\" action=\"?payment=pay&race_id=$race_id\">";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Jméno',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_LEFT);
IsLoggedFinance()?$data_tbl->set_header_col($col++,'Možnosti',ALIGN_LEFT):"";


echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_amount = 0;
$i = 1;

$row = array();
$row[] = "Pøihlášení";
$row[] = null;
$row[] = null;
$row[] = null;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_break_row()."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_prihlaseni))
{
	$id = $zaznam['id'];
	
	$row = array();
	$row[] = $zaznam['sort_name'];
	
	$amount = $zaznam['amount'];
	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" />';
	$row[] = $input_note;
	
	$row[] = $zaznam['kat'];
	$row[] = '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
//---------------------------------------------------
$row = array();
$row[] = "Nepøihlášení s platbama";
$row[] = null;
$row[] = null;
$row[] = null;
echo $data_tbl->get_new_row_arr($row)."\n";
echo $data_tbl->get_break_row()."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_platici))
{
	$id = $zaznam['id'];
	
	$row = array();
	$row[] = $zaznam['sort_name'];

	$amount = $zaznam['amount'];
	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" />';
	$row[] = $input_note;

	$row[] = $zaznam['kat'];
	$row[] = '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}

echo $data_tbl->get_footer()."\n";


echo '<input type="submit"/>';
echo '</form>';
echo "<form method=\"post\" action=\"?payment=pay&race_id=$race_id\">";

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Jméno',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_LEFT);
IsLoggedFinance()?$data_tbl->set_header_col($col++,'Možnosti',ALIGN_LEFT):"";

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i = 1;
while ($zaznam=mysql_fetch_assoc($vysledek_neprihlaseni))
{
	
	$id = $zaznam['id'];
	
	$row = array();
	$row[] = $zaznam['sort_name'];
	
	$amount = $zaznam['amount'];
	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" />';
	$row[] = $input_note;
	
	$row[] = $zaznam['kat'];
	// 	IsLoggedFinance()?$row[]=" <a href=\"?change=change&trn_id=".$zaznam['fin_id']."\">Zmìnit</a> / <a href=\"?storno=storno&trn_id=".$zaznam['fin_id']."\">Storno</a>":"";
	$row[] = '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
echo $data_tbl->get_footer()."\n";

?>
<input type="submit">
</form>