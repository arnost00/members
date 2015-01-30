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

$arr_labels = array('datum', 'reg.', 'jméno', 'částka', 'kometář');
$arr_fields = array('date', 'reg', 'name', 'amount', 'note'); 

TXT_Header();

db_Connect();

@$vysledek=MySQL_Query("SELECT f.date, concat('".$g_shortcut."',u.reg) as reg, u.sort_name as name, f.amount, f.note FROM `".TBL_FINANCE."` f join `".TBL_USER."` u on u.id = f.id_users_user where f.storno is null ORDER BY f.date desc")
	or die("Chyba při provádění dotazu do databáze.");

require_once ('exports.inc.php');

$users = new CSV_Export($g_shortcut,$delim,$par2,$par3);
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$users->add_line_user($zaznam);
}
echo($users->generate_csv($arr_labels, $arr_fields));

?>