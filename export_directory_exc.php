<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ('./connect.inc.php');
require_once ('./sess.inc.php');
require_once ('./common.inc.php');
require_once ('./common_user.inc.php');

if (!IsLoggedManager() && !IsLoggedRegistrator() && !IsLoggedSmallAdmin() && !IsLoggedAdmin())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$par1 = (IsSet($par1) && is_numeric($par1)) ? $par1 : 1;
$par2 = (IsSet($par2) && is_numeric($par2)) ? $par2 : 1;
$par3 = (IsSet($par3) && is_numeric($par3)) ? $par3 : 1;
$oris = (IsSet($oris) && is_numeric($oris)) ? $oris : 0;

switch($par1)
{
	case 2:
		$delim = "\t";
		break;
	case 1:
	default:
		$delim = ';';
}
TXT_Header();

db_Connect();

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE hidden = 0 ORDER BY sort_name ASC")
	or die("Chyba při provádění dotazu do databáze.");

require_once ('exports.inc.php');

if ($oris == 2)
{
	$users = new ORIS_Export($g_shortcut);
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$users->add_line_contact($zaznam['reg'],$zaznam['email'], $zaznam['tel_mobil']);
	}
	echo($users->generate_contacts());
}
else if ($oris == 1)
{
	$users = new ORIS_Export($g_shortcut);
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$users->add_line_user($zaznam['prijmeni'], $zaznam['jmeno'], $zaznam['reg'], $zaznam['si_chip'], $zaznam['poh'], $zaznam['narodnost'], $zaznam['datum'], $zaznam['rc']);
	}
	echo($users->generate_users());
}
else
{
	$users = new CSV_Export($g_shortcut,$delim,$par2,$par3);
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$users->add_line_user($zaznam);
	}
	echo($users->generate_users());
}
?>