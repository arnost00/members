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

function _list_sql_queries (&$qlist, $only_post_actions = false)
{
	global $this_file_name, $post_sql_function_title;

	if (!$only_post_actions)
	{
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
		echo '<BUTTON type="button" onclick="window.location = \'./'.$this_file_name.'?action=2\'">Vypiš pro copy&paste</BUTTON>';
	}
	else
		echo '<HR>';
	if (function_exists('post_sql_function'))
	{
		echo ('<BR><BUTTON type="button" onclick="window.location = \'./'.$this_file_name.'?action=3\'">Proveď akci po aktualizaci SQL</BUTTON>');
		if ($post_sql_function_title)
			echo(' - '.$post_sql_function_title.'<BR>');
	}
}

function _list_sql_queries_copy_paste (&$qlist)
{
	global $this_file_name;

	echo '<U>SQL příkazy</U> :';
	echo "<BR>\n";
	echo "<BR>\n";
	echo '<code>';
	$idx = 0;
	foreach($qlist as $line)
	{
		$idx++;
		echo '<B>SQL QUERY ['.$idx.']</B>';
		echo '<br />';
		echo('<TEXTAREA id="o_'.$idx.'" name="output_'.$idx.'" cols="160" rows="5" readonly>');
		echo($line);
		echo('</TEXTAREA>');
		echo "<BR>\n";
	}
	echo '</code>';
	echo '<HR>';
	echo '<BUTTON type="button" onclick="window.location = \'./'.$this_file_name.'?action=1\'">Proveď aktualizaci</BUTTON>';
	echo '<BUTTON type="button" onclick="window.location = \'./'.$this_file_name.'?action=0\'">Vypiš normálně</BUTTON>';
}

function _echo_db_error(&$db_conn, $exception)
{
	echo '<span class="ErrorText"><B>Chyba</B>'.(($exception) ? ' (vyhozena vyjímka)':'').'<BR>'."\n";
	echo 'Nepodařilo se provést změnu v databázi.<BR>'."\n";
	echo 'Error - '.mysqli_errno($db_conn).': '.mysqli_error($db_conn).'</span><BR>'."\n"; 
	echo '----------<BR>'."\n";
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
		try
		{
			echo '&nbsp;\-------- ';
			$result=mysqli_query($db_conn, $line);
			if ($result == FALSE)
			{
				_echo_db_error($db_conn, false);
				$db_err ++;
			}
			else
			{
				echo '<B>OK</B><BR>'."\n";
				$db_ok ++;
			}
		}
		catch (Exception $e)
		{
			_echo_db_error($db_conn, true);
			$db_err ++;
		}
	}
	echo '</code>'."\n";
	echo '<HR>'."\n";
	echo ' Počet úkonů prováděných v db : <B>'.sizeof($qlist).'</B><BR>'."\n";
	echo ' Správně vykonaných úkonů v db : <B>'.$db_ok.'</B><BR>'."\n";
	echo ' Chybně vykonaných úkonů v db : <span class="ErrorText"><B>'.$db_err.'</B></span><BR>'."\n";
}

?>