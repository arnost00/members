<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require("./cfg/_colors.php");
require ("./connect.inc.php");
require ("./sess.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require ("./ctable.inc.php");

db_Connect();
// id je z tabulky "users"
@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
$update=$id;
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");

DrawPageTitle('Informace o èlenovi', false);
?>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>

<?
$data_tbl = new html_table_nfo;
$data_tbl->table_width = 100;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Jméno', $zaznam["jmeno"].' '.$zaznam["prijmeni"]);
echo $data_tbl->get_new_row('Registraèní èíslo', $g_shortcut.RegNumToStr($zaznam["reg"]));
echo $data_tbl->get_new_row('Èíslo SI èipu', SINumToStr($zaznam["si_chip"]));
if (IsLoggedRegistrator())
	echo $data_tbl->get_new_row('Datum narození', SQLDate2String($zaznam["datum"]));
echo $data_tbl->get_new_row('Adresa', $zaznam['adresa']);
echo $data_tbl->get_new_row('Mìsto', $zaznam['mesto']);
echo $data_tbl->get_new_row('PSÈ', $zaznam['psc']);
echo $data_tbl->get_new_row('Email', GetEmailHTML($zaznam["email"]));
echo $data_tbl->get_new_row('Tel. domù', $zaznam["tel_domu"]);
echo $data_tbl->get_new_row('Tel. zamìstnání', $zaznam["tel_zam"]);
echo $data_tbl->get_new_row('Mobil', $zaznam["tel_mobil"]);
echo $data_tbl->get_new_row('Licence OB', $zaznam['lic']);
echo $data_tbl->get_new_row('Licence MTBO', $zaznam['lic_mtbo']);
echo $data_tbl->get_new_row('Licence LOB', $zaznam['lic_lob']);

echo $data_tbl->get_footer()."\n";
?>

<BR><BUTTON onclick="javascript:close_popup();">Zavøít</BUTTON></TD></TR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
</TABLE>

</BODY>
</HTML>