<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
	@$vysledek=MySQL_Query("SELECT podpis,login FROM ".TBL_ACCOUNT." WHERE id = '$usr->account_id' LIMIT 1");
	$zaznam=MySQL_Fetch_Array($vysledek);
	if ($zaznam["podpis"] != "")
		echo "Pøihlášen :: ".$zaznam["podpis"];
	else
		echo "Pøihlášen :: ".$zaznam["login"];
?>