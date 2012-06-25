<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* novinky - editace (pridavani) novinek */
@extract($_REQUEST);

require ('connect.inc.php');
require ('sess.inc.php');
require ('common.inc.php');
require("./cfg/_globals.php");

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
		if (strlen($text) > GC_NEWS_MAX_TEXT_LENGTH)
		  $text = substr($text,0,GC_NEWS_MAX_TEXT_LENGTH);
		
		$datum2=mysql_escape_string($datum2);
		$nadpis=mysql_escape_string($nadpis);
		$text=mysql_escape_string($text);
		
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