<?
// new version will be db.inc.php
// but is still in development :(

require_once('cfg/_cfg.php');
require_once('cfg/_tables.php');

$db_conn = null;

function db_Connect ($silent = false)
{
	global $g_dbserver,$g_dbuser,$g_dbpass,$g_dbname,$g_dbport,$g_baseadr, $db_conn;

	$spojeni = new mysqli($g_dbserver,$g_dbuser,$g_dbpass, '', $g_dbport);
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
		if (!$g_is_release) {
		echo "<code>$sql_query |||| pocet ovlivnenych radku : ".$db_conn->affected_rows."</code><br/>";
		/**
		* //log to console
		* // $console_message = "sql query: ".$sql_query." || pocet ovlivnenych radku :".$db_conn->affected_rows;
		* // echo '<script>console.log("'.$console_message.'")</script>';
		*/
	}
	$db_query_cnt++;
	try
	{
		$result=$db_conn->query($sql_query);
	}
	catch (mysqli_sql_exception $ex)
	{
		$msg = 'Popis: '.$ex->getMessage(); 
		echo ('Chyba při provádění dotazu do databáze. '.$msg."<br />\n");
		LogToFile(dirname(__FILE__) . '/logs/.db_errors.txt','Db query error - '.$msg.__FILE__);
		$result = false;
	}
//dokud nebude pripraven db.inc.php nebo sem nepridame funkci error_db()
//		or error_db();
	return $result;
}

///////////////////////////////////////////////////////////////////////////////
// NEW prepared statement helper API
///////////////////////////////////////////////////////////////////////////////

/**
 * Prepares an SQL statement.
 * @param string $sql SQL with placeholders (?)
 * @return mysqli_stmt|false
 */
function db_prepare($sql)
{
	global $db_conn;
	try {
		return $db_conn->prepare($sql);
	} catch (mysqli_sql_exception $ex) {
		LogToFile(dirname(__FILE__) . '/logs/.db_errors.txt', 'Db prepare error - ' . $ex->getMessage());
		return false;
	}
}

/**
 * Executes a prepared statement with parameters.
 * Automatically binds values and executes.
 *
 * @param string $sql SQL string with placeholders
 * @param string $types e.g. "ssi" for string,string,int
 * @param array $params Values to bind
 * @return mysqli_result|bool
 */
function db_execute( bool $isSelect, $stmt, $types = '', array $params = [], bool $doClose = true )
{
	global $db_conn;

	try {
		if ($types && $params) {
			$stmt->bind_param($types, ...$params);
		}

		$stmt->execute();

		// Return result or success
		if ( $isSelect) {
			$result = $stmt->get_result();
			if ( $doClose ) $stmt->close();
			return $result;
		} else {
			$affected = $stmt->affected_rows;
			if ( $doClose ) $stmt->close();
			return $affected;
		}
	} catch (Throwable $ex) {
		LogToFile(dirname(__FILE__) . '/logs/.db_errors.txt', 'Db execute error - ' . $ex->getMessage());
		return false;
	}
}

/**
 * Simple helper for insert/update/delete (non-select).
 * Usage: db_exec("UPDATE table SET x=? WHERE id=?", "si", [$x, $id]);
 */
function db_exec($stmt, $types, array $params)
{
	return db_execute(false, $stmt, $types, $params);
}

/**
 * Simple helper for select queries.
 * Usage: $rows = db_select("SELECT * FROM table WHERE name=?", "s", [$name]);
 */
function db_select($stmt, $types = '', array $params = [])
{
	$res = db_execute(true, $stmt, $types, $params);
	if ($res === false) return [];
	return $res->fetch_all(MYSQLI_ASSOC);
}
?>
