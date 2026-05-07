<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
$id = $_REQUEST['id'] ?? null;
$rtype = $_REQUEST['rtype'] ?? null;
$datum = $_REQUEST['datum'] ?? null;
$datum2 = $_REQUEST['datum2'] ?? null;
$etap = $_REQUEST['etap'] ?? null;
$zebricek = $_REQUEST['zebricek'] ?? null;
$prihlasky1 = $_REQUEST['prihlasky1'] ?? null;
$prihlasky2 = $_REQUEST['prihlasky2'] ?? null;
$prihlasky3 = $_REQUEST['prihlasky3'] ?? null;
$prihlasky4 = $_REQUEST['prihlasky4'] ?? null;
$prihlasky5 = $_REQUEST['prihlasky5'] ?? null;
$odkaz = $_REQUEST['odkaz'] ?? null;
$nazev = $_REQUEST['nazev'] ?? null;
$ext_id = $_REQUEST['ext_id'] ?? null;
$misto = $_REQUEST['misto'] ?? null;
$typ0 = $_REQUEST['typ0'] ?? null;
$typ = $_REQUEST['typ'] ?? null;
$ranking = $_REQUEST['ranking'] ?? null;
$oddil = $_REQUEST['oddil'] ?? null;
$poznamka = $_REQUEST['poznamka'] ?? null;
$transport = $_REQUEST['transport'] ?? null;
$accommodation = $_REQUEST['accommodation'] ?? null;
$kapacita = $_REQUEST['kapacita'] ?? null;
$cancelled = $_REQUEST['cancelled'] ?? null;

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$rtype = (IsSet($rtype)&& is_numeric($rtype)) ? (int)$rtype : 0;
$refresh_parent = (IsSet($refresh_parent) && (int)$refresh_parent == 0) ? 0 : 1;

$datum = String2DateDMY($datum);

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
if($prihlasky1 != 0) $prihlasky++;
if($prihlasky2 != 0) $prihlasky++;
if($prihlasky3 != 0) $prihlasky++;
if($prihlasky4 != 0) $prihlasky++;
if($prihlasky5 != 0) $prihlasky++;
$result=query_db("SELECT * FROM ".TBL_RACE." WHERE id='$id'");
$item=mysqli_fetch_array($result);
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
	$odkaz = cononize_url($odkaz, 1);

if ($datum==0 || ($datum2==0 && $rtype == 1) || $nazev=='' || $id == 0)
{
	header("location: ".$g_baseadr."error.php?code=42");
	exit;
}
else
{
	$ext_id=correct_sql_string($ext_id);
	$datum=correct_sql_string($datum);
	$datum2=correct_sql_string($datum2);
	$nazev=correct_sql_string($nazev);
	$misto=correct_sql_string($misto);
	$typ0=correct_sql_string($typ0);
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
	$poznamka=correct_sql_string($poznamka);
	$odkaz=correct_sql_string($odkaz);

	if (!isset($transport))
		$transport=0;
	if (!isset($accommodation))
		$accommodation=0;
	if (isset($cancelled) && $cancelled=='on')
		$cancelled=1;
	else
		$cancelled=0;

	$kapacitaSql = isset($kapacita) && $kapacita !== '' && is_numeric($kapacita) ? (int)$kapacita : 'NULL';

	$result=query_db("UPDATE ".TBL_RACE." SET ext_id='$ext_id', datum='$datum', datum2='$datum2', nazev='$nazev', misto='$misto', typ0='$typ0', typ='$typ', zebricek='$zebricek2', ranking='$ranking', prihlasky='$prihlasky', odkaz='$odkaz', prihlasky1='$prihlasky1', prihlasky2='$prihlasky2', prihlasky3='$prihlasky3', prihlasky4='$prihlasky4', prihlasky5='$prihlasky5', etap='$etap', poznamka='$poznamka', oddil='$oddil', modify_flag='$modify_flag', transport='$transport',  ubytovani='$accommodation', kapacita=$kapacitaSql, cancelled='$cancelled' WHERE id='$id'")
		or die("Chyba při provádění dotazu do databáze.");
	if ($result == FALSE)
		die ("Nepodařilo se změnit údaje o závodě.");
}
?>
<SCRIPT LANGUAGE="JavaScript">
<? if ($refresh_parent == 1) { ?>
	if (window.opener) {
		window.opener.location.reload();
		window.opener.focus();
	}
<? } ?>
	window.close();
</SCRIPT>
