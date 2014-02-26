<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
require ("ctable.inc.php");
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
	$chief_pay_result = mysql_query($chief_pay_query);
	// 	echo $chief_pay_query;
}

// id je z tabulky "users"
@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
$update=$id;
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");

DrawPageTitle('Èlenská základna - Editace uživatele');
?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<BR><hr><BR>
<? include "./user_new.inc.php"; ?>
<BR>

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


<A HREF="index.php?id=600&subid=1">Zpìt na seznam èlenù</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?include "./footer.inc.php"?>
<!-- Footer End -->
</TD></TR>
</TABLE>

</BODY>
</HTML>