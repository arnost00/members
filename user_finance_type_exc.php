<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");

if (!IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$user_id = (isset($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;
$type = (isset($type)&& is_numeric($type)) ? (int)$type : 0;

$result=query_db("UPDATE ".TBL_USER." SET finance_type='$type' WHERE id='$user_id'")
	or die("Chyba při provádění dotazu do databáze.");
if ($result == FALSE)
	die ("Nepodařilo se změnit údaje o závodě.");

header('location: '.$g_baseadr.'?id=800&subid=1');

?>

