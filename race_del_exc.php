<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");

if (IsLoggedRegistrator())
{
	db_Connect();

	@$vysledek=MySQL_Query("SELECT id FROM ".TBL_ZAVXUS." WHERE id_zavod='$id'");
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		MySQL_Query("DELETE FROM ".TBL_ZAVXUS." WHERE id='".$zaznam['id']."'");
	}

	@$vysledek=MySQL_Query("DELETE FROM ".TBL_RACE." WHERE id='$id'");

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