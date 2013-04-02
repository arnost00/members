<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
if (!IsLoggedAdmin() && !IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
include ("./common_user.inc.php");
include ("./common.inc.php");

db_Connect();
$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

@$vysledek=MySQL_Query("SELECT jmeno,prijmeni,datum,hidden FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
@$zaznam=MySQL_Fetch_Array($vysledek);
if (!$zaznam)
{	// error not exist
	header("location: ".$g_baseadr."error.php?code=201");
	exit;
}
include "./header.inc.php"; // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Èlenská základna - Administrace uživatelských úètù');

?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?
	$id_acc = GetUserAccountId_Users($id);
	$vysledek2=MySQL_Query("SELECT login,podpis,policy_news,policy_regs,policy_mng,policy_adm,policy_fin,locked FROM ".TBL_ACCOUNT." WHERE id = '$id_acc' LIMIT 1");
	$zaznam2=MySQL_Fetch_Array($vysledek2);
?>
<BR><hr><BR>
<? DrawPageSubTitle('Základní údaje o vybraném èlenovi'); ?>
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
	<TD class="DataValue"><?echo SQLDate2String($zaznam["datum"])?></TD>
</TR>
<? if ($zaznam["hidden"] != 0) { ?>
<TR>
	<TD width="45%" align="right">Tento uživatel je</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><span class="WarningText">skrytý uživatel</span></TD>
</TR>
<? } ?>
</TABLE>
<BR><hr><BR>
<?
	if($zaznam2 != FALSE)
		DrawPageSubTitle('Editace úètu vybraného èlena oddílu');
	else
		DrawPageSubTitle('Založení nového úètu vybranému èlenu oddílu');
?>
<FORM METHOD=POST ACTION="./user_login_edit_exc.php?type=<? echo ($zaznam2 != FALSE) ? "1" : "2"; echo "&id=".$id;?>">
<TABLE width="90%">
<TR>
	<TD width="30%" align="right">Pøihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="login" SIZE=20 VALUE="<? echo $zaznam2["login"]; ?>"></TD>
</TR>
<TR>
	<TD width="30%" align="right">Podpis uživatele</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="podpis" SIZE=20 VALUE="<?echo  $zaznam2["podpis"]?>"></TD>
</TR>
<TR>
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="news" SIZE=15 VALUE="1" <? if ($zaznam2["policy_news"]) echo "checked" ?> >Povoleno psaní novinek</TD>
</TR>
<TR>
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="mng" SIZE=15 VALUE="<? echo (!IsLoggedAdmin() && $zaznam2["policy_mng"] == _MNG_BIG_INT_VALUE_) ? '2' : '1'; ?>" <? if ($zaznam2["policy_mng"] == _MNG_SMALL_INT_VALUE_) echo "checked"; if (!IsLoggedAdmin() && $zaznam2["policy_mng"] == _MNG_BIG_INT_VALUE_) echo "disabled"; ?> >Uživatel je malým trenenérem (mùže mìnit údaje a pøihlášky vybraných èlenù)</TD>
</TR>
<? if (IsLoggedAdmin())
{ ?>
<TR>
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="mng2" SIZE=15 VALUE="1" <? if ($zaznam2["policy_mng"] == _MNG_BIG_INT_VALUE_) echo "checked" ?> >Uživatel je trenenérem (mùže mìnit údaje a pøihlášky èlenù)</TD>
</TR>
<TR>
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="regs" SIZE=15 VALUE="1" <? if ($zaznam2["policy_regs"]) echo "checked" ?> >Uživatel je pøihlašovatelem (mùže editovat pøihlášky èlenù - provádí export)</TD>
</TR>
<? if ($g_enable_finances)
{ ?>
<TR>
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="fin" SIZE=15 VALUE="1" <? if ($zaznam2["policy_fin"]) echo "checked" ?> >Uživatel je finanèníkem</TD>
</TR>
<? } ?>
<TR>
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="adm" SIZE=15 VALUE="1" <? if ($zaznam2["policy_adm"]) echo "checked" ?> >Uživatel je správcem</TD>
</TR>
<? if ($zaznam2["locked"] != 0) { ?>
<TR>
	<TD width="45%" align="right">Tento uživatel má</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><span class="WarningText">uzamèený úèet</span></TD>
</TR>
<? } ?>

<?
}
?>
<?
if($zaznam2 == FALSE)
{ // novy ucet
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD width="30%" align="right">Nové heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD width="30%" align="right">Nové heslo (ovìøení):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo2" VALUE="" SIZE="20"></TD>
</TR>
<?
}
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="<? echo ($zaznam2 != FALSE) ? "Provést zmìny" : "Založit úèet"; ?>"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Pøihlašovací jméno : </b>Doporuèujeme použití slova bez diakritiky a rozlišování velkým a malých písmen (jedno kterou variantu vyberete). V pøihlašovacím jménì mohou být i èíslice, kromì prvního písmene. Nedoporuèujem též používání mezer v jménu. Volte jméno tak, aby se zabránilo kolizím mezi jmény uživatelù.<BR></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><B>Podpis</B> je použit pøi zobrazování novinek jako informace kdo novinku napsal. Také se zobrazuje pøi pøihlášení v navigaèní lištì vlevo dole.<BR></TD>
</TR>
<?
if($zaznam2 != FALSE)
{ // zmena uctu
?>
</TABLE>
</FORM>

<BR><hr><BR>
<? DrawPageSubTitle('Zmìna hesla'); ?>

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
<?
}
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Heslo : </b>Hesla zadávejte bez diakritiky, mohou být i èíslice, minimálnì 4 znaky dlouhá. A nejlépe taková co každého nenapadnou jako první. Hesla typu "12345", "brno" nebo vaše pøezdívka budou bez varování zmìnìny! Nedoporuèuji ani používání jmen dìtí, rodièù pøípadnì domácích mazlíèkù v pùvodní podobì. Použijte alespoò zdrobnìlinu nebo domácí variantu, pøípadnì doplòte jméno nìjakým èíslem (kromì registraèního nebo roku narození).<BR></TD>
</TR>
</TABLE>
</FORM>
<BR><hr><BR>
<? if (IsLoggedManager()) { ?>
<A HREF="index.php?id=500&subid=1">Zpìt na seznam èlenù</A><BR>
<? } else { ?>
<A HREF="index.php?id=300&subid=3">Zpìt na seznam èlenù</A><BR>
<? } ?>
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