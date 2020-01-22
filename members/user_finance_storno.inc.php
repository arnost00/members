<?php /* finance -  show form for storno payment*/ 
if (!defined("__HIDE_TEST__")) exit;

$sql_query = "select fin.id, fin.id_users_user, fin.id_users_editor, fin.amount, fin.note, fin.date, rc.nazev zavod_nazev, from_unixtime(rc.datum,'%Y-%c-%e') zavod_datum from ".TBL_FINANCE." fin 
	left join ".TBL_RACE." rc on fin.id_zavod = rc.id
	where fin.id = ".$trn_id;
$vysledek_platba=query_db($sql_query);
$zaznam_platba=mysqli_fetch_array($vysledek_platba);

//vytazeni jmena uzivatele
@$vysledek_user_name=query_db("select us.sort_name name from ".TBL_USER." us where us.id = ".$zaznam_platba['id_users_user']);
$zaznam_user_name=mysqli_fetch_array($vysledek_user_name);

DrawPageSubTitle('Storno platby pro člena: '.$zaznam_user_name['name']);
$user_id = $zaznam_platba['id_users_user'];
?>
<form class="form" action="?payment=storno&trn_id=<?=$trn_id;?>&user_id=<?=$zaznam_platba["id_users_user"];?>" method="post">

<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

if ($zaznam_platba['zavod_nazev'] == null)
	$race_text = '---';
else
	$race_text = $zaznam_platba["zavod_nazev"]."&nbsp;-&nbsp;".formatDate($zaznam_platba["zavod_datum"]);

echo $data_tbl->get_new_row('<label for="datum">Datum platby</label>', '<input name="datum" type="text" disabled value="'.SQLDate2String($zaznam_platba['date']).'" size="8" />');
echo $data_tbl->get_new_row('<label for="race">Závod</label>', '<input name="race" type="text" disabled value="'.$race_text.'" size="40" maxlength="100" />');
echo $data_tbl->get_new_row('<label for="amount">Originální částka</label>', '<input name="amount" type="text" disabled value="'.$zaznam_platba["amount"].'" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Originální poznámka</label>', '<input name="note" type="text" disabled value="'.$zaznam_platba["note"].'" size="40" maxlength="200" />');
echo $data_tbl->get_new_row('<label for="storno_note">Poznámka</label>', '<input name="storno_note" type="text" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Odeslat"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
