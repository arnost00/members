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
$rtype= (IsSet($rtype) && is_numeric($rtype)) ? (int)$rtype: 0;

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
	$datum=correct_sql_string($datum);
	$datum2=correct_sql_string($datum2);
	$nazev=correct_sql_string($nazev);
	$misto=correct_sql_string($misto);
	$typ=correct_sql_string($typ);
	$zebricek2=correct_sql_string($zebricek2);
	$ranking=correct_sql_string($ranking);
	$prihlasky=correct_sql_string($prihlasky);
	$prihlasky1=correct_sql_string($prihlasky1);
	$prihlasky2=correct_sql_string($prihlasky2);
	$prihlasky3=correct_sql_string($prihlasky3);
	$prihlasky4=correct_sql_string($prihlasky4);
	$prihlasky5=correct_sql_string($prihlasky5);
	$etap=correct_sql_string($etap);
	$oddil=correct_sql_string($oddil);
	$modify_flag=correct_sql_string($modify_flag);
	
	!isset($transport)?$transport=null:$transport=1;
	
	
	$result=MySQL_Query("INSERT INTO ".TBL_RACE." (datum, datum2, nazev, misto, typ, zebricek, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, etap, poznamka, vicedenni, oddil, modify_flag, transport) VALUES ('$datum', '$datum2', '$nazev', '$misto', '$typ', '$zebricek2', '$ranking', '$odkaz', '$prihlasky', '$prihlasky1', '$prihlasky2', '$prihlasky3', '$prihlasky4', '$prihlasky5', '$etap', '$poznamka', '$vicedenni', '$oddil', '$modify_flag', '$transport')")
		or die("Chyba p�i prov�d�n� dotazu do datab�ze.");
	if ($result == FALSE)
		die ("Nepoda�ilo se vlo�it �daje o z�vod�.");
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.location.reload();

	window.opener.focus();
	window.close();
//-->
</SCRIPT>
