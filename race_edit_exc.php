<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./common.inc.php");
include ("./common_race.inc.php");
include ('./url.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$rtype = (IsSet($rtype)) ? $rtype : 0;

$datum = String2DateDMY($datum);

if( $rtype == 1)
{	// vicedenni
	$datum2 = String2DateDMY($datum2);
	$vicedenni = 1;
}
else
{	// jednodenni
	$datum2 = '';
	$vicedenni = 0;
	$etap = 1;
}

$zebricek2 = CreateZebricekNumber($zebricek);

$prihlasky1 = String2DateDMY($prihlasky1);
$prihlasky2 = String2DateDMY($prihlasky2);
$prihlasky3 = String2DateDMY($prihlasky3);
$prihlasky4 = String2DateDMY($prihlasky4);
$prihlasky5 = String2DateDMY($prihlasky5);
$prihlasky = 0;
if($prihlasky1 != '') $prihlasky++;
if($prihlasky2 != '') $prihlasky++;
if($prihlasky3 != '') $prihlasky++;
if($prihlasky4 != '') $prihlasky++;
if($prihlasky5 != '') $prihlasky++;
	
if ($odkaz != '')
	$odkaz = cononize_url ($odkaz);

if ($datum=='' || ($datum2=='' && $rtype == 1) || $nazev=='' || $id == 0)
{
	header("location: ".$g_baseadr."error.php?code=42");
	exit;
}
else
{
	$result=MySQL_Query("UPDATE ".TBL_RACE." SET datum='$datum', datum2='$datum2', nazev='$nazev', misto='$misto', typ='$typ', zebricek='$zebricek2', ranking='$ranking', prihlasky='$prihlasky', odkaz='$odkaz', prihlasky1='$prihlasky1', prihlasky2='$prihlasky2', prihlasky3='$prihlasky3', prihlasky4='$prihlasky4', prihlasky5='$prihlasky5', etap='$etap', poznamka='$poznamka', oddil='$oddil' WHERE id='$id'")
		or die("Chyba pøi provádìní dotazu do databáze.");
	if ($result == FALSE)
		die ("Nepodaøilo se zmìnit údaje o závodì.");
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.focus();
	window.close();
//-->
</SCRIPT>
