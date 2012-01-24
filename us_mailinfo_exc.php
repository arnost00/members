<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./common.inc.php");
include ("./common_race.inc.php");
include ('./url.inc.php');

if (!IsLoggedUser())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$racetype2 = CreateRaceTypeNumber($racetype);
$zebricek2 = CreateZebricekNumber($zebricek);
$daysbefore = (int) $daysbefore;
$daysbefore = ($daysbefore > $g_mailinfo_maximal_daysbefore) ? $g_mailinfo_maximal_daysbefore : (($daysbefore < $g_mailinfo_minimal_daysbefore) ? $g_mailinfo_minimal_daysbefore : $daysbefore);

$ch_data2 = CreateModifyFlag($ch_data);

$active_tf = isset($active_tf) ? (int)$active_tf : 0;
$active_ch = isset($active_ch) ? (int)$active_ch : 0;
$active_rg = isset($active_rg) ? (int)$active_rg : 0;

if ($email=='')
{
	header("location: ".$g_baseadr."error.php?code=52");
	exit;
}
else
{
	$result=MySQL_Query("SELECT id FROM ".TBL_MAILINFO." WHERE id_user = '$id' LIMIT 1");
	$zaznam=MySQL_Fetch_Array($result);
	if ($zaznam != FALSE)
	{	// update
		$dbid = $zaznam['id'];
//		echo('update');
//		echo("UPDATE ".TBL_MAILINFO." SET email='$email', daysbefore='$daysbefore', type='$racetype2', sub_type='$zebricek2', active_tf='$active_tf', active_ch='$active_ch', active_rg='$active_rg', ch_data='$ch_data2' WHERE id='$dbid'");
		$result=MySQL_Query("UPDATE ".TBL_MAILINFO." SET email='$email', daysbefore='$daysbefore', type='$racetype2', sub_type='$zebricek2', active_tf='$active_tf', active_ch='$active_ch', active_rg='$active_rg', ch_data='$ch_data2' WHERE id='$dbid'")
			or die("Chyba p�i prov�d�n� dotazu do datab�ze.");
		if ($result == FALSE)
			die ("Nepoda�ilo se zm�nit �daje o upozor�ov�n�.");
	
	}
	else
	{	// insert
//		echo('insert');
//		echo("INSERT INTO ".TBL_MAILINFO." (email, daysbefore, type, sub_type, id_user, active_tf, active_ch, active_rg, ch_data) VALUES ('$email', '$daysbefore', '$racetype2', '$zebricek2', '$id', '$active_tf', '$active_ch', '$active_rg', '$ch_data2')");
		$result=MySQL_Query("INSERT INTO ".TBL_MAILINFO." (email, daysbefore, type, sub_type, id_user, active_tf, active_ch, active_rg, ch_data) VALUES ('$email', '$daysbefore', '$racetype2', '$zebricek2', '$id', '$active_tf', '$active_ch', '$active_rg', '$ch_data2')")
			or die("Chyba p�i prov�d�n� dotazu do datab�ze.");
		if ($result == FALSE)
			die ("Nepoda�ilo se vytvo�it �daje o upozor�ov�n�.");
	}
	header("location: ".$g_baseadr."index.php?id=200&subid=4");
	exit;
}

?>