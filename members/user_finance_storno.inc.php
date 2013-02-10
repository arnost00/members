<?php /* finance -  show form for storno payment*/ 
if (!defined("__HIDE_TEST__")) exit;

@$vysledek_platba=mysql_query("select id, id_users_user user_id, amount, note, from_unixtime(date,'%Y-%c-%e') datum from ".TBL_FINANCE." where id = $trn_id");
$zaznam_platba=MySQL_Fetch_Array($vysledek_platba);

?>
<h3>Storno platby</h3>
<form class="form" action="?payment=storno&trn_id=<?=$trn_id;?>&user_id=<?=$zaznam_platba["user_id"];?>" method="post">
<label for="amount">Originální èástka</label>
<input name="amount" type="text" maxlength="5" disabled value="<?=$zaznam_platba["amount"];?>" />
<br>
<label for="note">Originální poznámka</label> 
<input name="note" type="text" disabled value="<?=$zaznam_platba["note"];?>" />
<br>
<label for="note">Poznámka</label> 
<input name="storno_note" type="text"/>
<br>
<input type="submit" value="Odeslat"/>
</form>
