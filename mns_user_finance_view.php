<?php /* maly trener - zobrazeni detailu financi pro clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
if (!IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();
// id je z tabulky "acounts"
@$vysledek=MySQL_Query("SELECT * FROM ".TBL_ACCOUNT." WHERE id = '$id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
$account_id=$id;
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");
include ("./ctable.inc.php");


include ("./user_finance.inc.php");

?>
<CENTER>
<BR><img src="imgs/line_navW.gif" width="95%" height=3 border="0"><BR><BR>
<A HREF="index.php?id=600&subid=10">Zpìt na seznam èlenù</A><BR>
<BR><img src="imgs/line_navW.gif" width="95%" height=3 border="0"><BR><BR>
<!-- Footer Begin -->
<?include "./footer.inc.php"?>
<!-- Footer End -->
</CENTER>
</BODY>
</HTML>