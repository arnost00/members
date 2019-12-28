<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
	@$vysledek=mysqli_query($db_conn, "SELECT podpis,login FROM ".TBL_ACCOUNT." WHERE id = '$usr->account_id' LIMIT 1");
	$zaznam=mysqli_fetch_array($vysledek);
	if ($zaznam["podpis"] != "")
		echo "Přihlášen :: ".$zaznam["podpis"];
	else
		echo "Přihlášen :: ".$zaznam["login"];
?>