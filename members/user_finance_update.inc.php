<?php /* finance -  show form for outgoing payment*/ 
if (!defined("__HIDE_TEST__")) exit;

@$vysledek_platba=mysql_query("select id, id_users_user user_id, amount, note, from_unixtime(date,'%Y-%c-%e') datum from ".TBL_FINANCE." where id = $trn_id");
$zaznam_platba=MySQL_Fetch_Array($vysledek_platba);

?>
<h3>Zmìna platby</h3>
<form class="form" action="?payment=update&user_id=<?=$zaznam_platba["user_id"];?>&trn_id=<?=$trn_id;?>" method="post">

<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('<label for="amount">Èástka</label>', '<input name="amount" type="text" onkeyup="checkAmount(this);" maxlength="5" value="'.$zaznam_platba["amount"].'" />');
echo $data_tbl->get_new_row('<label for="note">Poznámka</label>', '<input name="note" type="text" value="'.$zaznam_platba["note"].'" />');

echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<input type="submit" value="Odeslat"/>');
echo $data_tbl->get_footer()."\n";
?>
</FORM>
