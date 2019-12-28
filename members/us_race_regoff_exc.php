<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;
$id_us = (IsSet($id_us) && is_numeric($id_us)) ? (int)$id_us : 0;

@$vysledek2=mysqli_query($db_conn, "SELECT * FROM ".TBL_USER." where id=$id_us");
$entry_lock = false;
if ($zaznam2=mysqli_fetch_array($vysledek2))
{
	$entry_lock = ($zaznam2['entry_locked'] != 0);
}

if (!$entry_lock)
{
	@$vysledek=mysqli_query($db_conn, "DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id_zav' AND id_user = '$id_us'");
}
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.location.reload();

	window.opener.focus();
	window.close();
//-->
</SCRIPT>