<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$mng = (IsSet($mng) && is_numeric($mng)) ? (int)$mng : 0;

if($id > 0)
{
	$result=mysqli_query($db_conn, "UPDATE ".TBL_USER." SET `chief_id`='$mng', `chief_pay`=null  WHERE `id` = '$id'")
		or die("Chyba při provádění dotazu do databáze.");
	if ($result == FALSE)
		die ("Nepodařilo se změnit přihlášku člena.");
}
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.location.reload();

	window.opener.focus();
	window.close();
//-->
</SCRIPT>
