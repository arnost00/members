<?php /* finance -  show form for outgoing payment*/ 
if (!defined("__HIDE_TEST__")) exit;
?>
<h3>Platba Ëlena [-]</h3>
<form class="form" action="?payment=out&user_id=<?=$user_id;?>" method="post">
<select name="id_zavod">
<option value=null>---</option>
<? 
@$vysledek_zavody=mysql_query("select id, nazev, from_unixtime(datum,'%Y-%c-%e') datum from ".TBL_RACE." order by datum desc");
while ($zaznam=MySQL_Fetch_Array($vysledek_zavody))
{
	echo "<option value=".$zaznam["id"].">".$zaznam["nazev"]."&nbsp;-&nbsp;".formatDate($zaznam["datum"])."</option>";
}
?>
</select>
<br>
<label for="amount">»·stka</label>
<input name="amount" type="text" onkeyup="checkAmount(this);" maxlength="5"/>
<br>
<label for="note">Pozn·mka</label> 
<input name="note" type="text"/>
<br>
<label for="datum">Datum platby</label> 
<input name="datum" type="text" value=<?=date("Y-m-d");?> />
<br>
<input type="submit" value="Odeslat platbu"/>
</form>
