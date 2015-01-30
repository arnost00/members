<?

require_once('../cfg/_cfg.php');
require_once('../cfg/_tables.php');

function db_connect ()
{
	global $g_dbserver,$g_dbuser,$g_dbpass,$g_dbname,$g_baseadr;

	@$spojeni=mysql_connect($g_dbserver,$g_dbuser,$g_dbpass) 
		or die ('Chyba pri pripojovani do db.');
	mysql_select_db($g_dbname);
	mysql_query("SET CHARACTER SET UTF-8");
}

function db_close ()
{
	mysql_close();
}
 
?>