<? /* trenerova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Členská základna - Editace uživatele');
?>
<CENTER>

<?

if (IsSet($chiefPayFor))
{
	if (empty($chief_pay)) $chief_pay = "null";
	$chief_pay_query = "update `".TBL_USER."` set chief_pay = $chief_pay where id = $chiefPayFor";
	$chief_pay_result = query_db($chief_pay_query);
// 	echo $chief_pay_query;
}

// id je z tabulky "users"
@$vysledek=query_db("SELECT * FROM ".TBL_USER." WHERE id = '$usr->user_id' LIMIT 1");
@$zaznam=mysqli_fetch_array($vysledek);
$update=$usr->user_id;
$self_edit = (IsLoggedSmallAdmin() || IsLoggedAdmin()) ? false : true;
require_once ("./common_user.inc.php");
?>
<BR><hr><BR>
<? require_once "./user_new.inc.php"; ?>

<?
//pridani formulare pro moznost zaskrtnuti placeni trenerem
$return_url = full_url();
$return_url = parse_url($return_url, PHP_URL_QUERY);

$user_id = $usr->user_id;
$chief_query = "select u.chief_id, ch.sort_name as chief_name, u.chief_pay from `".TBL_USER."` u join `".TBL_USER."` ch on u.chief_id = ch.id where u.id = $user_id";
// echo "|$chief_query|";
$chief_result = query_db($chief_query);
$chief_record = mysqli_fetch_array($chief_result);
$chief_id = ($chief_record) ? $chief_record["chief_id"] : 0; 

if ($chief_id > 0) require_once 'us_setup_nursechild_form.inc.php';
?>

</CENTER>
