<?php /* maly trener - zobrazeni detailu financi pro clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
if (!IsLoggedSmallManager() && !IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");
include ("./ctable.inc.php");
DrawPageTitle('Finance èlena', false);
?>
<CENTER>
<?
//inicializace id uzivatele pro vypis financi
$user_id = $id;
include ("./user_finance.inc.php");
?>
<br>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON><BR>
</CENTER>
</BODY>
</HTML>