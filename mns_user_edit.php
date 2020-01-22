<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("ctable.inc.php");
if (!IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

//cast pro aktualizaci udaje, zda clenovi plati trener
if (IsSet($chiefPayFor))
{
	if (empty($chief_pay)) $chief_pay = "null";
	$chief_pay_query = "update `".TBL_USER."` set chief_pay = $chief_pay where id = $chiefPayFor";
	$chief_pay_result = query_db($chief_pay_query);
}

// id je z tabulky "users"
$query = "SELECT * FROM ".TBL_USER." WHERE id = '$id' LIMIT 1";
@$vysledek=query_db($query);
@$zaznam=mysqli_fetch_array($vysledek);
$update=$id;
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");

DrawPageTitle('Členská základna - Editace uživatele');
?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<BR><hr><BR>
<? require_once "./user_new.inc.php"; ?>
<BR>

<?
//pridani formulare pro moznost zaskrtnuti placeni trenerem
$return_url = full_url();
$return_url = parse_url($return_url, PHP_URL_QUERY);

$user_id = $id;
$chief_query = "select u.chief_id, ch.sort_name as chief_name, u.chief_pay from `".TBL_USER."` u join `".TBL_USER."` ch on u.chief_id = ch.id where u.id = $user_id";
// echo "|$chief_query|";
$chief_result = query_db($chief_query);
$chief_record = mysqli_fetch_array($chief_result);
$chief_id = $chief_record["chief_id"]; 

if ($chief_id > 0) require_once 'us_setup_nursechild_form.inc.php';
?>


<A HREF="index.php?id=600&subid=1">Zpět na seznam členů</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?require_once "./footer.inc.php"?>
<!-- Footer End -->
</TD></TR>
</TABLE>
<?
HTML_Footer();
?>