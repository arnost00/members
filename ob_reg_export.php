<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ('./connect.inc.php');
require ('./sess.inc.php');

if (!IsLoggedAdmin())
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}

require ('./common_user.inc.php');
require ('./common_race.inc.php');

header('Content-Type: text/plain; charset=windows-1250');

define('SPACE_CHAR',' ');

db_Connect();

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." ORDER by reg");

while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$name = $zaznam['prijmeni'].SPACE_CHAR.$zaznam["jmeno"];
	$si = (($zaznam['si_chip'] != 0) ? $zaznam['si_chip'] : '');

	$line = RegNumToStr($zaznam['reg']);
	$line .= ';';
	$line .= str_pad($name,25,SPACE_CHAR);
	$line .= ';';
	$line .= str_pad($si,7,SPACE_CHAR);
	$line .= ';';
	$line .= $zaznam['lic'];
	$line .= ';11';

	echo $g_shortcut.$line."\n";
}

?>