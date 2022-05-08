<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedAdmin())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$accept = (isset($accept) && is_numeric($accept)) ? (int)$accept : 0;

require_once ("./ctable.inc.php");

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");

DrawPageTitle('Reset typu oddílových příspěvků');
?>
<CENTER>
<?
if ($accept == 1)
{
	// action
	db_Connect();
	$query = "UPDATE ".TBL_USER." SET finance_type = '0'";
	$result=query_db($query);
	if ($result == FALSE)
	{
		echo ("Nepodařilo se vynulovat.");
	}
	else
	{
		echo ("Akce proběhla v pořádku, byly změněny ".mysqli_affected_rows($db_conn)." záznamy.<br><br>");
	}

?>
<BUTTON onclick="javascript:close_popup();">Zavřít okno</BUTTON>
<?
}
else
{
?>
Provede vymazání typu oddílových příspěvků u všech členů.<br><br>
<form method=post action="adm_reset_ft.php?accept=1">
<INPUT TYPE="submit" VALUE="Proveď reset"> <BUTTON onclick="javascript:close_popup();">Zavřít okno</BUTTON>
</form>
<?
}

?>

</CENTER>
<?
HTML_Footer();
?>