<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* novinky - editace (pridavani) novinek */
@extract($_REQUEST);

require_once ('connect.inc.php');
require_once ('sess.inc.php');
require_once ('common.inc.php');
require_once("./cfg/_globals.php");

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
		if (iconv_strlen($text,'UTF-8') > GC_NEWS_MAX_TEXT_LENGTH)
		  $text = mb_substr($text,0,GC_NEWS_MAX_TEXT_LENGTH,'UTF-8');
		
		$datum2=correct_sql_string($datum2);
		$nadpis=correct_sql_string($nadpis);
		$text=correct_sql_string($text);
		
		if (IsSet($update))
		{
			$update = (isset($update) && is_numeric($update)) ? (int)$update : 0;

			$result=MySQL_Query("UPDATE ".TBL_NEWS." SET datum='$datum2', nadpis='$nadpis', text='$text' WHERE id='$update'")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit novinku.");
		}
		else
		{
			$result = MySQL_Query("INSERT INTO ".TBL_NEWS." (id_user,datum,nadpis,text) VALUES ('$usr->account_id','$datum2','$nadpis','$text')")
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se vložit novinku.");
		}
	

	}
	header('location: '.$g_baseadr);
}
else
{
	header('location: '.$g_baseadr.'error.php?code=31');
	exit;
}
?>