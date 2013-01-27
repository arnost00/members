<?php /* finance -  show form for outgoing payment*/ 
if (!defined("__HIDE_TEST__")) exit;

/*&user_id=<?=$user_id;?>
*/
?>

<form class="form" action="?payment=in&id=<?=$user_id;?>" method="post">
<select name="id_zavod">
<? 
@$vysledek_zavody=mysql_query("select id, nazev, from_unixtime(datum,'%Y-%c-%d') datum from ".TBL_RACE);
while ($zaznam=MySQL_Fetch_Array($vysledek_zavody))
{
	echo "<option value=".$zaznam["id"].">".$zaznam["nazev"]."&nbsp;-&nbsp;".formatDate($zaznam["datum"])."</option>";
}
?>
</select>
<br>
<label for="amount">»·stka</label>
<input name="amount" type="text"/>
<br>
<label for="note">Pozn·mka</label> 
<input name="note" type="text"/>
<br>
<input type="submit" value="Odeslat"/>
</form>
