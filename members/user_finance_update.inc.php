<?php /* finance -  show form for outgoing payment*/ 
if (!defined("__HIDE_TEST__")) exit;

$sql_query = "select fin.id, fin.id_users_user, fin.id_users_editor, fin.amount, fin.note, fin.date, rc.nazev zavod_nazev, rc.id zavod_id, from_unixtime(rc.datum,'%Y-%c-%e') zavod_datum from ".TBL_FINANCE." fin 
	left join ".TBL_RACE." rc on fin.id_zavod = rc.id
	where fin.id = ".$trn_id;
$vysledek_platba=mysql_query($sql_query);
$zaznam_platba=MySQL_Fetch_Array($vysledek_platba);

//vytazeni jmena uzivatele
@$vysledek_user_name=MySQL_Query("select us.sort_name name from ".TBL_USER." us where us.id = ".$zaznam_platba['id_users_user']);
$zaznam_user_name=MySQL_Fetch_Array($vysledek_user_name);

DrawPageSubTitle('Změna platby pro člena: '.$zaznam_user_name['name']);
$user_id = $zaznam_platba['id_users_user'];
?>
<form class="form" action="?payment=update&user_id=<?=$zaznam_platba["id_users_user"];?>&trn_id=<?=$trn_id;?>" method="post">

<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

if ($zaznam_platba['zavod_nazev'] == null)
{
	$race_text = '---';
	$race_id = null;
}
else
{
	$race_text = $zaznam_platba["zavod_nazev"]."&nbsp;-&nbsp;".formatDate($zaznam_platba["zavod_datum"]);
	$race_id = $zaznam_platba["zavod_id"];
}

$race_sel = '';
$race_sel .= '<select name="id_zavod">';
$race_sel .= '<option value=null>---</option>';

@$vysledek_zavody=mysql_query("select id, nazev, from_unixtime(datum,'%Y-%c-%e') datum_text from ".TBL_RACE." order by datum desc");
while ($zaznam=MySQL_Fetch_Array($vysledek_zavody))
{
	($zaznam["id"] == $race_id)?$selected="selected":$selected="";
	$race_sel .= "<option ".$selected." value=".$zaznam["id"].">".$zaznam["nazev"]."&nbsp;-&nbsp;".formatDate($zaznam["datum_text"])."</option>";
}
$race_sel .= '</select>';

echo $data_tbl->get_new_row('<label for="datum">Datum platby</label>', '<input name="datum" type="text" disabled value="'.SQLDate2String($zaznam_platba['date']).'" size="8" />');
echo $data_tbl->get_new_row('<label for="race">Závod</label>', $race_sel);
echo $data_tbl->get_new_row('<label for="amount">Částka</label>', '<input name="amount" type="text" onkeyup="checkAmount(this);" maxlength="5" value="'.$zaznam_platba["amount"].'" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" value="'.$zaznam_platba["note"].'" size="40" maxlength="200" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Odeslat"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
