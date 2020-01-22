<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
if (!IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once ("./common_user.inc.php");
require_once ("./common.inc.php");

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();
$query = "SELECT jmeno,prijmeni,datum,hidden FROM ".TBL_USER." WHERE id = '$id' LIMIT 1";
@$vysledek=query_db($query);
@$zaznam=mysqli_fetch_array($vysledek);
if (!$zaznam)
{	// error not exist
	header("location: ".$g_baseadr."error.php?code=201");
	exit;
}
require_once "./header.inc.php"; // header obsahuje uvod html a konci <BODY>

DrawPageTitle('Členská základna - Editace uživatelských účtů');

?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?
	$id_acc = GetUserAccountId_Users($id);
	$query = "SELECT login,podpis,policy_news,policy_regs,policy_mng,locked FROM ".TBL_ACCOUNT." WHERE id = '$id_acc' LIMIT 1";
	$vysledek2=query_db($query);
	$zaznam2=mysqli_fetch_array($vysledek2);
?>
<BR><hr><BR>
<? DrawPageSubTitle('Základní údaje o vybraném členovi'); ?>
<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Příjmení</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><?echo $zaznam["prijmeni"]?></TD>
</TR>
<TR>
	<TD width="45%" align="right">Jméno</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><?echo $zaznam["jmeno"]?></TD>
</TR>
<TR>
	<TD width="45%" align="right">Datum narození</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><?echo Date2String($zaznam["datum"])?></TD>
</TR>
<TR>
	<TD width="45%" align="right">Přihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><?echo $zaznam2["login"]?></TD>
</TR>
<? if ($zaznam["hidden"] != 0) { ?>
<TR>
	<TD width="45%" align="right">Tento uživatel je</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><span class="WarningText">skrytý uživatel</span></TD>
</TR>
<? } ?>
</TABLE>
<BR><hr>
<? DrawPageSubTitle('Změna hesla'); ?>

<FORM METHOD=POST ACTION="./user_login_edit_exc.php?type=3&id=<?echo $id;?>">
<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Nové heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Nové heslo (ověření):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo2" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Změnit heslo"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Heslo : </b>Hesla zadávejte bez diakritiky, mohou být i číslice, minimálně 4 znaky dlouhá. A nejlépe taková co každého nenapadnou jako první. Hesla typu "12345", "brno" nebo vaše přezdívka budou bez varování změněny! Nedoporučuji ani používání jmen dětí, rodičů případně domácích mazlíčků v původní podobě. Použijte alespoň zdrobnělinu nebo domácí variantu, případně doplňte jméno nějakým číslem (kromě registračního nebo roku narození).<BR></TD>
</TR>
</TABLE>
</FORM>
<BR><hr><BR>
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