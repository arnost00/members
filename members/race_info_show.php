<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require('./cfg/_colors.php');
require ('./connect.inc.php');
require ('./sess.inc.php');

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? $id_zav : 0;

if($id_zav == 0)
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}

require ("./ctable.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_race.inc.php");
include ('./url.inc.php');

DrawPageTitle('Informace o závodì');

db_Connect();

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav LIMIT 1");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

RaceInfoTable($zaznam_z,'',false,true,false);
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>

<BR>
<?
if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplòující informace o závodì (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}
?>

</body>
</html>
