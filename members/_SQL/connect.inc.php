<?

require_once('../cfg/_cfg.php');
require_once('../cfg/_tables.php');

$db_conn = null;

function db_connect ()
{
	global $g_dbserver,$g_dbuser,$g_dbpass,$g_dbname,$g_baseadr,$db_conn;

	@$spojeni=new mysqli($g_dbserver,$g_dbuser,$g_dbpass) 
		or die ('Chyba pri pripojovani do db.');
	$spojeni->select_db($g_dbname);
	$spojeni->query("SET CHARACTER SET UTF-8");
	$db_conn = $spojeni;
}

function db_close ()
{
	global $db_conn;
	mysqli_close($db_conn);
}
 
?>