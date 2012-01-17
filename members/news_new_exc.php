<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* novinky - editace (pridavani) novinek */
require ('./connect.inc.php');
require ('./sess.inc.php');
require ('./common.inc.php');

if (IsLoggedEditor())
{
	db_Connect();

	if ($datum == '' || $text=='')
	{
		header('location: '.$g_baseadr.'error.php?code=32');
		exit;
	}
	else
	{

		$datum2=String2DateDMY($datum);
		MySQL_Query("INSERT INTO ".TBL_NEWS." (id_user,datum,nadpis,text) VALUES ('$usr->account_id','$datum2','$nadpis','$text')");
	}
	header('location: '.$g_baseadr);
}
else
{
	header('location: '.$g_baseadr.'error.php?code=31');
	exit;
}
?>