<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* finance - editace (pridavani) typu prispevku */
@extract($_REQUEST);

require_once ('connect.inc.php');
require_once ('sess.inc.php');
require_once ('common.inc.php');
require_once("./cfg/_globals.php");

if (IsLoggedFinance())
{
	db_Connect();

	if ($nazev == '')
	{
		header('location: '.$g_baseadr.'error.php?code=62');
		exit;
	}
	else
	{
		if (mb_strlen($popis,'UTF-8') > 255)
			$popis = mb_substr($popis,0,255,'UTF-8');
		
		$nazev=correct_sql_string($nazev);
		$popis=correct_sql_string($popis);
		
		if (IsSet($update))
		{
			$update = (isset($update) && is_numeric($update)) ? (int)$update : 0;
			
			$query = "UPDATE ".TBL_FINANCE_TYPES." SET nazev='$nazev', popis='$popis' WHERE id='$update'";
			$result=query_db($query)
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit typ příspěvku.");
		}
		else
		{
			$query = "INSERT INTO ".TBL_FINANCE_TYPES." (nazev,popis) VALUES ('$nazev','$popis')";
			$result = query_db($query)
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se vložit typ příspěvku.");
		}
	}
	header('location: '.$g_baseadr.'index.php?id='._FINANCE_GROUP_ID_.'&subid=4');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>