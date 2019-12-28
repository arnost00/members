<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (IsLoggedRegistrator())
{
	db_Connect();
	$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

	@$vysledek=mysqli_query($db_conn, "SELECT id FROM ".TBL_ZAVXUS." WHERE id_zavod='$id'");
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		mysqli_query($db_conn, "DELETE FROM ".TBL_ZAVXUS." WHERE id='".$zaznam['id']."'");
	}

	@$vysledek=mysqli_query($db_conn, "DELETE FROM ".TBL_RACE." WHERE id='$id'");

	if (IsLoggedAdmin())
		header("location: ".$g_baseadr."index.php?id=300&subid=5");
	else	// registrator
		header("location: ".$g_baseadr."index.php?id=400&subid=4");
}
else
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
?>