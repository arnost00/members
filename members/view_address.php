<? /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./ctable.inc.php");

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();
// id je z tabulky "users"
@$vysledek=query_db("SELECT * FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
@$zaznam=mysqli_fetch_array($vysledek);
$update=$id;
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");

DrawPageTitle('Informace o členovi');
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
echo $data_tbl->get_new_row('Registrační číslo', $g_shortcut.RegNumToStr($zaznam["reg"]));
echo $data_tbl->get_new_row('Číslo SI čipu', SINumToStr($zaznam["si_chip"]));
if (IsLoggedRegistrator())
	echo $data_tbl->get_new_row('Datum narození', SQLDate2String($zaznam["datum"]));
echo $data_tbl->get_new_row('Adresa', $zaznam['adresa']);
echo $data_tbl->get_new_row('Město', $zaznam['mesto']);
echo $data_tbl->get_new_row('PSČ', $zaznam['psc']);
echo $data_tbl->get_new_row('Email', GetEmailHTML(ParseEmails($zaznam["email"])));
echo $data_tbl->get_new_row('Tel. domů', $zaznam["tel_domu"]);
echo $data_tbl->get_new_row('Tel. zaměstnání', $zaznam["tel_zam"]);
echo $data_tbl->get_new_row('Mobil', $zaznam["tel_mobil"]);
echo $data_tbl->get_new_row('Licence OB', $zaznam['lic']);
echo $data_tbl->get_new_row('Licence MTBO', $zaznam['lic_mtbo']);
echo $data_tbl->get_new_row('Licence LOB', $zaznam['lic_lob']);

echo $data_tbl->get_footer()."\n";
?>

<BR><BUTTON onclick="javascript:close_popup();">Zavřít</BUTTON></TD></TR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
</TABLE>

<?
HTML_Footer();
?>