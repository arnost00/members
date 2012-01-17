<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");
if (!IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
include ("./common_user.inc.php");
include ("./common.inc.php");

db_Connect();
@$vysledek=MySQL_Query("SELECT jmeno,prijmeni,datum,hidden FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
if (!$zaznam)
{	// error not exist
	header("location: ".$g_baseadr."error.php?code=201");
	exit;
}
include "./header.inc.php"; // header obsahuje uvod html a konci <BODY>
<?
DrawPageTitle('Èlenská základna - Editace uživatelských úètù', false);
?>
?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?
	$id_acc = GetUserAccountId_Users($id);
	$vysledek2=MySQL_Query("SELECT login,podpis,policy_news,policy_regs,policy_mng,locked FROM ".TBL_ACCOUNT." WHERE id = '$id_acc' LIMIT 1");
	$zaznam2=MySQL_Fetch_Array($vysledek2);
?>
<BR><hr><BR>
<H3>Základní údaje o vybraném èlenovi</H3>
<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Pøíjmení</TD>
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
	<TD width="45%" align="right">Pøihlašovací jméno</TD>
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
<H3>Zmìna hesla</H3>

<FORM METHOD=POST ACTION="./user_login_edit_exc.php?type=3&id=<?echo $id;?>">
<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Nové heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Nové heslo (ovìøení):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo2" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Zmìnit heslo"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Heslo : </b>Hesla zadávejte bez diakritiky, mohou být i èíslice, minimálnì 4 znaky dlouhá. A nejlépe taková co každého nenapadnou jako první. Hesla typu "12345", "brno" nebo vaše pøezdívka budou bez varování zmìnìny! Nedoporuèuji ani používání jmen dìtí, rodièù pøípadnì domácích mazlíèkù v pùvodní podobì. Použijte alespoò zdrobnìlinu nebo domácí variantu, pøípadnì doplòte jméno nìjakým èíslem (kromì registraèního nebo roku narození).<BR></TD>
</TR>
</TABLE>
</FORM>
<BR><hr><BR>
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