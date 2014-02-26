<? /* trenerova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Èlenská základna - Editace uživatele');
?>
<CENTER>

<?

if (IsSet($chiefPayFor))
{
	if (empty($chief_pay)) $chief_pay = "null";
	$chief_pay_query = "update `".TBL_USER."` set chief_pay = $chief_pay where id = $chiefPayFor";
	$chief_pay_result = mysql_query($chief_pay_query);
// 	echo $chief_pay_query;
}

// id je z tabulky "users"
@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE id = '$usr->user_id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
$update=$usr->user_id;
include ("./common_user.inc.php");
?>
<BR><hr><BR>
<? include "./user_new.inc.php"; ?>

<?
//pridani formulare pro moznost zaskrtnuti placeni trenerem
$return_url = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
$return_url = parse_url($return_url, PHP_URL_QUERY);

$user_id = $usr->user_id;
$chief_query = "select u.chief_id, ch.sort_name as chief_name, u.chief_pay from `".TBL_USER."` u join `".TBL_USER."` ch on u.chief_id = ch.id where u.id = $user_id";
// echo "|$chief_query|";
$chief_result = mysql_query($chief_query);
$chief_record = mysql_fetch_array($chief_result);
$chief_id = $chief_record["chief_id"]; 

if ($chief_id > 0) include 'us_setup_nursechild_form.inc.php';
?>

</CENTER>
