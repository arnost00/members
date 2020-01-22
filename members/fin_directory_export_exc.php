<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ('./connect.inc.php');
require_once ('./sess.inc.php');
require_once ('./common.inc.php');
require_once ('./common_user.inc.php');

if (!IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$delim = ';';
$par2 = 1; //quotes
$par3 = 0; //apostrophes

$arr_labels = array('datum', 'reg.', 'jméno', 'částka', 'kometář', 'závod','datum závodu');
$arr_fields = array('date', 'reg', 'name', 'amount', 'note','zavod_nazev','zavod_datum'); 

TXT_Header();

db_Connect();

$query = "SELECT f.date, concat('".$g_shortcut."',u.reg) as reg, u.sort_name as name, f.amount, f.note, rc.nazev zavod_nazev, from_unixtime(rc.datum,'%Y-%c-%e') zavod_datum FROM `".TBL_FINANCE."` f join `".TBL_USER."` u on u.id = f.id_users_user left join `".TBL_RACE."` rc on f.id_zavod = rc.id where f.storno is null ORDER BY f.date desc";
@$vysledek=query_db($query)
	or die("Chyba při provádění dotazu do databáze.");

require_once ('exports.inc.php');

$users = new CSV_Export($g_shortcut,$delim,$par2,$par3);
while ($zaznam=mysqli_fetch_array($vysledek))
{
	$users->add_line_user($zaznam);
}
echo($users->generate_csv($arr_labels, $arr_fields));

?>