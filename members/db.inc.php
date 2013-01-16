<?

// new version for connect.inc.php
// but is still in development :(
// and not finished :(

if (!defined('_DB_INCLUDED')) {
	define('_DB_INCLUDED', 1);

	require('cfg/_cfg.php');
	require('cfg/_tables.php');

///////////////////////////////////////////////////////////////////////////////
function error_db($err=12)
{
	global $g_baseadr;

	if(_LOG_DB_ERRORS)
	{
		$str = 'err:'.$err."\t".mysql_error()."\r\n";
		LogToFile(_LOG_DB_ERROR_FILE,$str);
	}

	if (!headers_sent($filename, $linenum))	// only in php > 4.3.0
	{
		header('location: '.$g_baseadr.'error.php?code='.$err);
	}
	else
	{
		// test -->
		echo 'Headers already sent in '.$filename.' on line '.$linenum."\n".
			'Cannot redirect, for now please click this <a ' .
			'href="http://www.example.com">link</a> instead'."\n";
		
	}

	// pokud dojde k volani fce v prubehu zobrazovani php scriptu tak se jiz presmerovani neprovede.
	// headers_sent ?
	exit;
}
///////////////////////////////////////////////////////////////////////////////
function connect_db()
{
	global $g_db;

	@$spojeni=mysql_connect($g_db['server'],$g_db['user'],$g_db['pass'])
	or error_db(11);

	if ($spojeni)
	{
		mysql_select_db($g_db['name']);
	}
}
///////////////////////////////////////////////////////////////////////////////
function close_db()
{
	mysql_close();
}
///////////////////////////////////////////////////////////////////////////////
function correct_sql_string($str)
{
	return mysql_real_escape_string($str);
}
///////////////////////////////////////////////////////////////////////////////
$db_query_cnt = 0;
function query_db($sql_query)
{
	global $db_query_cnt;
	$db_query_cnt++;
	$result=mysql_query($sql_query)
		or error_db();
	return $result;
}

}	// endif
?>