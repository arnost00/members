<?
// new version will be db.inc.php
// but is still in development :(

require_once('cfg/_cfg.php');
require_once('cfg/_tables.php');

function db_Connect ($silent = false)
{
	global $g_dbserver,$g_dbuser,$g_dbpass,$g_dbname,$g_baseadr;

	@$spojeni=MySQL_Connect($g_dbserver,$g_dbuser,$g_dbpass);
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
	MySQL_Select_DB($g_dbname);
	MySQL_Query("SET CHARACTER SET UTF8");
	return true;
}

// from db.inc.php
function correct_sql_string($str)
{
	return mysql_real_escape_string($str);
}

?>