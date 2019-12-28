<?

function _print_upd_info ()
{
	global $version_upd, $g_dbserver, $g_dbname, $g_baseadr;

	echo '<B>Aktualizace databáze pro verzi '.$version_upd.' a nižší</B>';
	echo "<BR>\n";
	echo "<code><BR>\n";
	echo 'DbServerName : <B>'.$g_dbserver."</B><BR>\n";
	echo 'DatabaseName : <B>'.$g_dbname."</B><BR>\n";
	echo 'Web Base URL : <B>'.$g_baseadr."</B><BR>\n";
	echo "</code><BR>\n";
	echo '<HR>';
}

function _list_sql_queries (&$qlist)
{
	global $this_file_name;

	echo '<U>SQL příkazy</U> :';
	echo "<BR>\n";
	echo "<BR>\n";
	echo '<code>';
	foreach($qlist as $line)
	{
		echo '<B>SQL QUERY</B> = "'.$line.'"';
		echo "<BR>\n";
	}
	echo '</code>';
	echo '<HR>';
	echo '<BUTTON type="button" onclick="window.location = \'./'.$this_file_name.'?action=1\'">Proveď aktualizaci</BUTTON>';
}

function _run_sql_queries (&$qlist)
{
	global $db_conn;
	
	$db_ok = 0;
	$db_err = 0;
	echo '<U>Provádím SQL příkazy</U> :';
	echo "<BR>\n";
	echo "<BR>\n";
	echo '<code>'."\n";
	foreach($qlist as $line)
	{
		echo '<B>SQL QUERY</B> = "'.$line.'"';
		echo "<BR>\n";

		$result=mysqli_query($db_conn, $line);
		echo '&nbsp;\-------- ';
		if ($result == FALSE)
		{
			echo '<span class="ErrorText"><B>Chyba</B><BR>'."\n";
			echo 'Nepodařilo se provést změnu v databázi.<BR>'."\n";
			echo 'Error - '.mysqli_errno($db_conn).': '.mysqli_error($db_conn).'</span><BR>'."\n"; 
			echo '----------<BR>'."\n";
			$db_err ++;
		}
		else
		{
			echo '<B>OK</B><BR>'."\n";
			$db_ok ++;
		}

	}
	echo '</code>'."\n";
	echo '<HR>'."\n";
	echo ' Počet úkonů prováděných v db : <B>'.sizeof($qlist).'</B><BR>'."\n";
	echo ' Správně vykonaných úkonů v db : <B>'.$db_ok.'</B><BR>'."\n";
	echo ' Chybně vykonaných úkonů v db : <span class="ErrorText"><B>'.$db_err.'</B></span><BR>'."\n";
}

?>