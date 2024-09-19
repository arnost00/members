<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');
require_once ('common_fin.inc.php');

if (!IsLoggedUser())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

$notify_type2 = CreateNotifyTypeNumber($notify_type);
$racetype2 = CreateRaceTypeNumber($racetype);
$zebricek2 = CreateZebricekNumber($zebricek);
$daysbefore = (int) $daysbefore;
$daysbefore = ($daysbefore > $g_mailinfo_maximal_daysbefore) ? $g_mailinfo_maximal_daysbefore : (($daysbefore < $g_mailinfo_minimal_daysbefore) ? $g_mailinfo_minimal_daysbefore : $daysbefore);

$ch_data2 = CreateModifyFlag($ch_data);

$active_tf = isset($active_tf) ? (int)$active_tf : 0;
$active_ch = isset($active_ch) ? (int)$active_ch : 0;
$active_rg = isset($active_rg) ? (int)$active_rg : 0;
$active_fin = isset($active_fin) ? (int)$active_fin : 0;
$active_finf = isset($active_finf) ? (int)$active_finf : 0;
$active_news = isset($active_news) ? (int)$active_news : 0;

$fin_limit = isset($fin_limit) ? (int)$fin_limit : 0;
$fin_type2 = CreateFinMailFlag($fin_type);
if ($email=='')
{
	header("location: ".$g_baseadr."error.php?code=52");
	exit;
}
else
{
	$email=correct_sql_string($email);

	$result=query_db("SELECT id FROM ".TBL_MAILINFO." WHERE id_user = '$id' LIMIT 1");
	$zaznam=mysqli_fetch_array($result);
	if ($zaznam != NULL)
	{	// update
		$dbid = $zaznam['id'];
		$result=query_db("UPDATE ".TBL_MAILINFO." SET email='$email', daysbefore='$daysbefore', type='$racetype2', sub_type='$zebricek2', active_tf='$active_tf', active_ch='$active_ch', active_rg='$active_rg', ch_data='$ch_data2', active_fin='$active_fin', active_finf='$active_finf', fin_type='$fin_type2', fin_limit='$fin_limit', active_news='$active_news', notify_type='$notify_type2' WHERE id='$dbid'")
			or die("Chyba při provádění dotazu do databáze. (update)");
		if ($result == FALSE)
			die ("Nepodařilo se změnit údaje o upozorňování.");
	}
	else
	{	// insert
		$query = "INSERT INTO ".TBL_MAILINFO." (email, daysbefore, type, sub_type, id_user, active_tf, active_ch, active_rg, ch_data, active_fin, active_finf, fin_type, fin_limit, active_news, notify_type) VALUES ('$email', '$daysbefore', '$racetype2', '$zebricek2', '$id', '$active_tf', '$active_ch', '$active_rg', '$ch_data2', '$active_fin', '$active_finf', '$fin_type2', '$fin_limit', '$active_news', '$notify_type2')";
		$result=query_db($query)
			or die("Chyba při provádění dotazu do databáze. (insert)");
		if ($result == FALSE)
			die ("Nepodařilo se vytvořit údaje o upozorňování.");
	}
	header("location: ".$g_baseadr."index.php?id=200&subid=4");
	exit;
}

?>
