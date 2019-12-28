<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;

switch ($type)
{
	case 1:
		// reset trenera
		$result=mysqli_query($db_conn, "UPDATE ".TBL_USER." SET chief_id = '0', chief_pay = null WHERE id='".$id."'");
		break;
	case 2:
		// reset platiciho trenera
		if (IsLoggedSmallAdmin())
		{
			$result=mysqli_query($db_conn, "UPDATE ".TBL_USER." SET chief_pay = null WHERE id='".$id."'");
		}
		break;
	case 3:
		// povol platiciho trenera
		if (IsLoggedSmallAdmin())
		{
			$result=mysqli_query($db_conn, "UPDATE ".TBL_USER." SET chief_pay = chief_id WHERE id='".$id."'");
		}
		break;
}

header("location: ".$g_baseadr."index.php?id=500&subid=3");
?>
