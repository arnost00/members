<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");
require_once ("ctable.inc.php");
if (!IsLoggedFinance()) {
    header("location: ".$g_baseadr."error.php?code=21");
    exit;
}
require_once("cfg/_globals.php");
require_once("cfg/race_enums.php");

db_Connect();

$id = (int)(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? 0);
$is_new = !isset($_REQUEST['id']);
$update=$id; // pouzito v fin_payrule_edit.inc.php pro rozliseni mezi editaci a vlozenim noveho zaznamu

$finTypes = query_db("SELECT * FROM " . TBL_FINANCE_TYPES);
// Fetch all rows into array
$financial_types = $finTypes ? mysqli_fetch_all($finTypes, MYSQLI_ASSOC) : [];

$zaznam = null;

if ( !$is_new ) {
    $res = query_db("SELECT * FROM " . TBL_PAYRULES . " WHERE id = $id");
    $zaznam = mysqli_fetch_array($res);
}

if (!isset($head_addons)) $head_addons = ''; 
$head_addons .="\t".'<script src="finance.js" type="text/javascript"></script>'."\n";
require_once ("./header.inc.php");
require_once ("./common.inc.php");
require_once ("./common_fin.inc.php");

?>

<table cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD>
<CENTER>
<?php include 'fin_payrule_edit.inc.php'; ?>

<BR><hr><BR>
<CENTER><? echo('<A HREF="index.php?id='._FINANCE_GROUP_ID_.'&subid=5">Zpět</A><BR>'); ?></CENTER>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=3 ALIGN=CENTER>
<!-- Footer Begin -->
<? require_once ("footer.inc.php"); ?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<? HTML_Footer(); ?>
