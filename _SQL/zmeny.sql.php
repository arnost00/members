<?

$zmeny_list = array();
function AddZmenyFile($version)
{
	global $zmeny_list;
	$i = sizeof($zmeny_list);
	$zmeny_list[$i]['file'] = 'zmeny_'.$version.'.sql.php';
	$zmeny_list[$i]['ver'] = $version;
}
//#############################################################################
//	seznam zmenovych souboru
//#############################################################################

AddZmenyFile('1.6.4.39');
AddZmenyFile('1.6.5.41');
AddZmenyFile('1.7.4.51');
AddZmenyFile('1.7.5.53');
AddZmenyFile('1.7.7.57');
AddZmenyFile('1.8.1.61');
AddZmenyFile('1.8.3.65');
AddZmenyFile('1.8.3.65-post');
AddZmenyFile('1.8.4.67');
AddZmenyFile('2.0.1.75');

//#############################################################################

require ('connect.inc.php');
require ('../sess.inc.php');
require ('common.inc.php');
if (!IsLoggedAdmin())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

if (!IsSet($action)) $action = 0;
if ($action == 0)
{
	for ($i = 0; $i < sizeof($zmeny_list); $i++)
	{
		echo '<a href="'.$zmeny_list[$i]['file'].'">Aktualizace pro verzi '.$zmeny_list[$i]['ver'].'</a><BR>'."\n";
	}
	echo '<HR><BR>';
	echo '<a href="./zmeny.sql.php?action=1">Optimalizuj databazi</a><br>';
	echo '<a href="./zmeny.sql.php?action=2">Oprav databazi</a><br>';
}
else if ($action == 1)
{	// optimalization
		db_Connect();
		$line = 'OPTIMIZE TABLE '.TBL_RACE.', '.TBL_NEWS.', '.TBL_USER.', '.TBL_ACCOUNT.', '.TBL_USXUS.', '.TBL_ZAVXUS.', '.TBL_MODLOG;
		echo '<B>SQL QUERY</B> = "'.$line.'"';
		echo "<BR>\n";
		$result=MySQL_Query($line);
		echo '&nbsp;\-------- ';
		if ($result == FALSE)
		{
			echo '<span class="ErrorText"><B>Chyba</B><BR>'."\n";
			echo 'Nepodaøilo se optimalizovat tabulky v databázi.<BR>'."\n";
			echo 'Error - '.mysql_errno().': '.mysql_error().'</span><BR>'."\n"; 
			echo '----------<BR>'."\n";
		}
		else
		{
			echo '<B>OK</B><BR>'."\n";
			echo '<TABLE BORDER=1>';
			echo '<TR><TD>Table</TD><TD>Op</TD><TD>Msg_type</TD><TD>Msg_text</TD></TR>';
			while ($zaznam=MySQL_Fetch_Array($result))
			{
				echo '<TR><TD>'.$zaznam[0].'</TD><TD>'.$zaznam[1].'</TD><TD>'.$zaznam[2].'</TD><TD>'.$zaznam[3].'</TD></TR>';
			}
			echo '</TABLE>';
		}
	echo '<a href="./zmeny.sql.php">Zpìt</a>';
	}
else if ($action == 2)
{	// repair
		db_Connect();
		$line = 'REPAIR TABLE '.TBL_RACE.', '.TBL_NEWS.', '.TBL_USER.', '.TBL_ACCOUNT.', '.TBL_USXUS.', '.TBL_ZAVXUS.', '.TBL_MODLOG;
		echo '<B>SQL QUERY</B> = "'.$line.'"';
		echo "<BR>\n";
		$result=MySQL_Query($line);
		echo '&nbsp;\-------- ';
		if ($result == FALSE)
		{
			echo '<span class="ErrorText"><B>Chyba</B><BR>'."\n";
			echo 'Nepodaøilo se opravit tabulky v databázi.<BR>'."\n";
			echo 'Error - '.mysql_errno().': '.mysql_error().'</span><BR>'."\n"; 
			echo '----------<BR>'."\n";
		}
		else
		{
			echo '<B>OK</B><BR>'."\n";
			echo '<TABLE BORDER=1>';
			echo '<TR><TD>Table</TD><TD>Op</TD><TD>Msg_type</TD><TD>Msg_text</TD></TR>';
			while ($zaznam=MySQL_Fetch_Array($result))
			{
				echo '<TR><TD>'.$zaznam[0].'</TD><TD>'.$zaznam[1].'</TD><TD>'.$zaznam[2].'</TD><TD>'.$zaznam[3].'</TD></TR>';
			}
			echo '</TABLE>';
		}
	echo '<a href="./zmeny.sql.php">Zpìt</a>';
	}
else
{
	echo '- nothing -'."\n";
	echo '<a href="./zmeny.sql.php">Zpìt</a>';
}
echo '<HR>';
echo '<span class="HiddenText">--end--</span>'."\n";
echo '</body></html>';
?>