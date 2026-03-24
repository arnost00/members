<?php /* finance -  show form for outgoing payment*/ 
if (!defined("__HIDE_TEST__")) exit;
?>
<h3>Platba člena [-]</h3>
<form class="form" action="?payment=out&user_id=<?=$user_id;?>" method="post">
<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

$race_sel = '';
$race_sel .= '<select name="id_zavod">';
$race_sel .= '<option value=null>---</option>';

@$vysledek_zavody=query_db("select id, nazev, from_unixtime(datum,'%Y-%c-%e') datum_text from ".TBL_RACE." order by datum desc");
while ($zaznam=mysqli_fetch_assoc($vysledek_zavody))
{
	$selected = (isset($race_id) && $zaznam["id"] == $race_id) ? ' selected' : '';
	$race_sel .= "<option value=\"{$zaznam["id"]}\"{$selected}>".$zaznam["nazev"]."&nbsp;-&nbsp;".formatDate($zaznam["datum_text"])."</option>";
}
$race_sel .= '</select>&nbsp;&nbsp;nepovinná položka';

echo $data_tbl->get_new_row('<label for="id_zavod">Závod</label>', $race_sel);
echo $data_tbl->get_new_row('<label for="amount">Částka</label>', '<input name="amount" type="text" onkeyup="checkAmount(this);" size="5" maxlength="10" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" size="40" maxlength="200" />');
echo $data_tbl->get_new_row('<label for="datum">Datum platby</label>', '<input name="datum" type="text" value="'.GetCurrentDateString().'" size="8" />&nbsp;&nbsp;(DD.MM.RRRR)');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Odeslat platbu"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
