<?php /* finance -  show form for outgoing payment*/ 
if (!defined("__HIDE_TEST__")) exit;

$sql_query = "select fin.id, fin.id_users_user, fin.id_users_editor, fin.amount, fin.note, fin.date, rc.nazev zavod_nazev, from_unixtime(rc.datum,'%Y-%c-%e') zavod_datum from ".TBL_FINANCE." fin 
	left join ".TBL_RACE." rc on fin.id_zavod = rc.id
	where fin.id = ".$trn_id;
$vysledek_platba=mysql_query($sql_query);
$zaznam_platba=MySQL_Fetch_Array($vysledek_platba);

//vytazeni jmena uzivatele
@$vysledek_user_name=MySQL_Query("select us.sort_name name from ".TBL_USER." us where us.id = ".$zaznam_platba['id_users_user']);
$zaznam_user_name=MySQL_Fetch_Array($vysledek_user_name);

DrawPageSubTitle('ZmÏna platby pro Ëlena: '.$zaznam_user_name['name']);
$user_id = $zaznam_platba['id_users_user'];
?>
<form class="form" action="?payment=update&user_id=<?=$zaznam_platba["id_users_user"];?>&trn_id=<?=$trn_id;?>" method="post">

<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

if ($zaznam_platba['zavod_nazev'] == null)
	$race_text = '---';
else
	$race_text = $zaznam_platba["zavod_nazev"]."&nbsp;-&nbsp;".formatDate($zaznam_platba["zavod_datum"]);

echo $data_tbl->get_new_row('<label for="datum">Datum platby</label>', '<input name="datum" type="text" disabled value="'.SQLDate2String($zaznam_platba['date']).'" size="8" />');
echo $data_tbl->get_new_row('<label for="race">Z·vod</label>', '<input name="race" type="text" disabled value="'.$race_text.'" size="40" maxlength="100" />');
echo $data_tbl->get_new_row('<label for="amount">»·stka</label>', '<input name="amount" type="text" onkeyup="checkAmount(this);" maxlength="5" value="'.$zaznam_platba["amount"].'" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Pozn·mka</label>', '<input name="note" type="text" value="'.$zaznam_platba["note"].'" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Odeslat"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
