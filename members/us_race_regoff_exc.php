<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

@$vysledek=MySQL_Query("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id_zav' AND id_user = '$id_us'");

?>

<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.focus();
	window.close();
//-->
</SCRIPT>