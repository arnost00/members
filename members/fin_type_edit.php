<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("connect.inc.php");
require_once ("sess.inc.php");
require_once ("ctable.inc.php");
if (!IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once("cfg/_globals.php");

db_Connect();
$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

// id je z tabulky "finance types"
$sql_query = 'SELECT * FROM '.TBL_FINANCE_TYPES." WHERE id = '$id' LIMIT 1";


@$vysledek=MySQL_Query($sql_query);
@$zaznam=MySQL_Fetch_Array($vysledek);
$update=$id;
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");

DrawPageTitle('Editace typu oddílového příspěvku');
?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<? require_once ('fin_type_edit.inc.php'); ?>
<BR><hr><BR>
<A HREF="index.php?id=800&subid=4">Zpět</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?require_once ("footer.inc.php");?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<?
HTML_Footer();
?>