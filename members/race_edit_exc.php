<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

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
$rtype = (IsSet($rtype)&& is_numeric($rtype)) ? (int)$rtype : 0;

$datum = String2DateDMY($datum);
function gen_modify_flag_v2b($val_last,$val_new)
{	// varianta s ignorovanim zmeny terminu a zmeny v zavode pokud je zaroven vytvoren zavod
	// povolene vysledne hodnoty 0,1,2,4,5
	//
	//              puvodni
	//         0   1   2   4   5
	// n   0 | 0 | 1 | 2 | 4 | 5 
	// o   1 | 1 | 1 | 2 | 5 | 5 
	// v   4 | 4 | 5 | 2 | 4 | 5 
	// e   5 | 5 | 5 | 2 | 5 | 5 
	
	global $g_modify_flag;
	
	if ($val_last == $val_new || $val_new == 0)	// beze zmeny, nebo stejna zmena
		$v1 = $val_last;
	else if (($val_last & $g_modify_flag [1]['id'] ) != 0) // byl vytvoren (top level flag)
		$v1 = $g_modify_flag [1]['id'] ;
	else
	{
		if (($val_last & $g_modify_flag [0]['id'] ) != 0 || ($val_last & $g_modify_flag [2]['id'] ) != 0)
			$v1 = $g_modify_flag [0]['id'] + $g_modify_flag [2]['id'];
		else
			$v1 = $val_new;
	}
	return $v1;
}

if( $rtype == 1)
{	// vicedenni
	$datum2 = String2DateDMY($datum2);
	$vicedenni = 1;
}
else
{	// jednodenni
	$datum2 = 0;
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

$result=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id='$id'");
$item=MySQL_Fetch_Array($result);
if ($item != FALSE)
{	// zmena terminu prihlasek
	$modify_flag = ($prihlasky != $item['prihlasky'] || $prihlasky1 != $item['prihlasky1'] || $prihlasky2 != $item['prihlasky2'] || $prihlasky3 != $item['prihlasky3'] || $prihlasky4 != $item['prihlasky4'] || $prihlasky5 != $item['prihlasky5']) ? $g_modify_flag [0]['id'] : 0;
	if ($datum != $item['datum'] || $datum2 != $item['datum2'])
	{	// editace duleziteho parametru (terminu zavodu)
		$modify_flag = $modify_flag + $g_modify_flag [2]['id'];
	}
	$modify_flag = gen_modify_flag_v2b($item['modify_flag'], $modify_flag);
}
else // zavod nenalezen proto nasteven na pridani zavodu
	$modify_flag = $g_modify_flag [1]['id'];

if ($odkaz != '')
	$odkaz = cononize_url ($odkaz);

if ($datum==0 || ($datum2==0 && $rtype == 1) || $nazev=='' || $id == 0)
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

	$transport = !isset($transport)? null: 1;
	$cancelled = !isset($cancelled)? 0: 1;

	$result=MySQL_Query("UPDATE ".TBL_RACE." SET datum='$datum', datum2='$datum2', nazev='$nazev', misto='$misto', typ='$typ', zebricek='$zebricek2', ranking='$ranking', prihlasky='$prihlasky', odkaz='$odkaz', prihlasky1='$prihlasky1', prihlasky2='$prihlasky2', prihlasky3='$prihlasky3', prihlasky4='$prihlasky4', prihlasky5='$prihlasky5', etap='$etap', poznamka='$poznamka', oddil='$oddil', modify_flag='$modify_flag', transport='$transport', cancelled='$cancelled' WHERE id='$id'")
		or die("Chyba pøi provádìní dotazu do databáze.");
	if ($result == FALSE)
		die ("Nepodaøilo se zmìnit údaje o závodì.");
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.location.reload();

	window.opener.focus();
	window.close();
//-->
</SCRIPT>
