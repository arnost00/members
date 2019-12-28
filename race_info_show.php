<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once('./cfg/_colors.php');
require_once ('./connect.inc.php');
require_once ('./sess.inc.php');

$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? $id_zav : 0;

if($id_zav == 0)
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}

require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

DrawPageTitle('Informace o závodě');

db_Connect();

@$vysledek_z=mysqli_query($db_conn, "SELECT * FROM ".TBL_RACE." WHERE id=$id_zav LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);

RaceInfoTable($zaznam_z,'',false,true,false);
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>

<BR>
<?
if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplňující informace o závodě (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}
?>

<?
HTML_Footer();
?>