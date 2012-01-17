<?

if (!defined('_CONNECT_INCLUDED')) {
	define('_CONNECT_INCLUDED', 1);

	require('../cfg/_cfg.php');
	require('../cfg/_tables.php');

	function db_connect ()
	{
		global $g_dbserver,$g_dbuser,$g_dbpass,$g_dbname,$g_baseadr;

		@$spojeni=mysql_connect($g_dbserver,$g_dbuser,$g_dbpass) 
			or die ('Chyba pri pripojovani do db.');
		mysql_select_db($g_dbname);
		mysql_query("SET CHARACTER SET cp1250");
	}

	function db_close ()
	{
		mysql_close();
	}
 
}	// endif
?>