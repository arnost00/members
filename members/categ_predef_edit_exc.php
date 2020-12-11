<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* finance - editace (pridavani) typu prispevku */
@extract($_REQUEST);

require_once ('connect.inc.php');
require_once ('sess.inc.php');
require_once ('common.inc.php');
require_once("./cfg/_globals.php");

if (IsLoggedRegistrator())
{
	db_Connect();

	if ($name == '')
	{
		header('location: '.$g_baseadr.'error.php?code=72');
		exit;
	}
	else
	{
		if (mb_strlen($cat_list,'UTF-8') > 255)
			$cat_list = mb_substr($cat_list,0,255,'UTF-8');
		
		$name=correct_sql_string($name);
		$cat_list=correct_sql_string($cat_list);
		
		if (IsSet($update))
		{
			$update = (isset($update) && is_numeric($update)) ? (int)$update : 0;
			
			$query = "UPDATE ".TBL_CATEGORIES_PREDEF." SET name='$name', cat_list='$cat_list' WHERE id='$update'";
			$result=query_db($query)
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se změnit předdefinované kategorie.");
		}
		else
		{
			$query = "INSERT INTO ".TBL_CATEGORIES_PREDEF." (name,cat_list) VALUES ('$name','$cat_list')";
			$result = query_db($query)
				or die("Chyba při provádění dotazu do databáze.");
			if ($result == FALSE)
				die ("Nepodařilo se vložit předdefinované kategorie.");
		}
	}
	header('location: '.$g_baseadr.'categ_predef.php');
}
else
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
?>