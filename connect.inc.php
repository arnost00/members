<?
// new version will be db.inc.php
// but is still in development :(

require_once('cfg/_cfg.php');
require_once('cfg/_tables.php');

$db_conn = null;

function db_Connect ($silent = false)
{
	global $g_dbserver,$g_dbuser,$g_dbpass,$g_dbname,$g_baseadr, $db_conn;

	$spojeni= new mysqli($g_dbserver,$g_dbuser,$g_dbpass);
	if (!$spojeni)
	{
		if($silent)
			return false;
		else
		{
			header("location: ".$g_baseadr."error.php?code=11");
			exit;
		}
	}
	$spojeni->select_db ($g_dbname);
	$spojeni->query("SET CHARACTER SET UTF8");
	$db_conn = $spojeni;
	return true;
}

// from db.inc.php
function correct_sql_string($str)
{
	global $db_conn;
	return mysqli_real_escape_string($db_conn, $str);
}
///////////////////////////////////////////////////////////////////////////////
$db_query_cnt = 0;
function query_db($sql_query)
{
	global $db_query_cnt, $db_conn, $g_is_release;
	if (!$g_is_release) echo "<code>$sql_query</code><br/>";
	$db_query_cnt++;
	$result=$db_conn->query($sql_query);
//dokud nebude pripraven db.inc.php nebo sem nepridame funkci error_db()
//		or error_db();
	return $result;
}

?>