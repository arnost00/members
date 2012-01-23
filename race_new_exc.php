<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./common.inc.php");
include ('./url.inc.php');
include ('./common_race.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();
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
$modify_flag = $g_modify_flag [1]['id'];

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

if ($datum=='' || ($datum2=='' && $rtype == 1) || $nazev=='')
{
	header("location: ".$g_baseadr."error.php?code=42");
	exit;
}
else
{
	$result=MySQL_Query("INSERT INTO ".TBL_RACE." (datum, datum2, nazev, misto, typ, zebricek, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, etap, poznamka, vicedenni, oddil,modify_flag) VALUES ('$datum', '$datum2', '$nazev', '$misto', '$typ', '$zebricek2', '$ranking', '$odkaz', '$prihlasky', '$prihlasky1', '$prihlasky2', '$prihlasky3', '$prihlasky4', '$prihlasky5', '$etap', '$poznamka', '$vicedenni', '$oddil', '$modify_flag')")
		or die("Chyba pøi provádìní dotazu do databáze.");
	if ($result == FALSE)
		die ("Nepodaøilo se vložit údaje o závodì.");
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.focus();
	window.close();
//-->
</SCRIPT>
