<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ('./connect.inc.php');
require ('./sess.inc.php');
require ('./common.inc.php');
require ('./common_user.inc.php');

if (!IsLoggedRegistrator() || !IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$par1 = (IsSet($par1) && is_numeric($par1)) ? $par1 : 1;
$par2 = (IsSet($par2) && is_numeric($par2)) ? $par2 : 1;
$par3 = (IsSet($par3) && is_numeric($par3)) ? $par3 : 1;

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
	or die("Chyba pøi provádìní dotazu do databáze.");

echo('pøíjmeni;jméno;datum narození;reg;email;adresa;mesto;psc;tel.domù;tel.práce; tel.mobilní;si.èip;licence OB;licence MTBO;licence LOB;');
echo("\n");

function echo_col($text)
{
	global $par2;
	global $par3;
	if($par2 == 1)
		echo('"');
	if($par3 == 1 && is_numeric($text))
		echo('\'');
	echo($text);
	if($par2 == 1)
		echo('"');
}

function echo_delim()
{
	global $delim;
	echo($delim);
}

while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	echo_col($zaznam['prijmeni']);
	echo_delim();
	echo_col($zaznam['jmeno']);
	echo_delim();
	echo_col(SQLDate2String($zaznam["datum"]));
	echo_delim();
	echo_col($g_shortcut.RegNumToStr($zaznam['reg']));
	echo_delim();
	echo_col($zaznam['email']);
	echo_delim();
	echo_col($zaznam['adresa']);
	echo_delim();
	echo_col($zaznam['mesto']);
	echo_delim();
	echo_col($zaznam['psc']);
	echo_delim();
	echo_col($zaznam['tel_domu']);
	echo_delim();
	echo_col($zaznam['tel_zam']);
	echo_delim();
	echo_col($zaznam['tel_mobil']);
	echo_delim();
	echo_col($zaznam['si_chip']);
	echo_delim();
	echo_col($zaznam['lic']);
	echo_delim();
	echo_col($zaznam['lic_mtbo']);
	echo_delim();
	echo_col($zaznam['lic_lob']);
	echo_delim();
	echo("\n");
}
echo("\n");

?>