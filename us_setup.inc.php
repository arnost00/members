<?php /* clenova stranka - editace informaci a nastaveni */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Nastavení osobních údajù èlenù oddílu', false);
?>
<CENTER>
<?
if (IsLogged())
{

$vysledek=MySQL_Query("SELECT login,podpis FROM ".TBL_ACCOUNT." WHERE id = '$usr->account_id' LIMIT 1");
$curr_usr=MySQL_Fetch_Array($vysledek);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}
?>
<BR><hr><BR>
<H3>Základní údaje</H3>

<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Pøihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><? echo $curr_usr["login"]; ?></TD>
</TR>
<TR>
	<TD width="45%" align="right">Povoleno psaní novinek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><? echo ($usr->policy_news) ? "ano": "ne"; ?></TD>
</TR>
</TABLE>

<BR><hr><BR>
<H3>Volitelné údaje</H3>

<FORM METHOD=POST ACTION="./us_setup_exc.php?type=1&id=<?echo $usr->account_id;?>">
<TABLE width="90%">
<TR>
	<TD width="40%" align="right">Podpis</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="podpis" SIZE=20 VALUE="<?echo  $curr_usr["podpis"]?>"></TD>
</TR>
<TR>
	<TD width="40%" align="right">Pøihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="login" SIZE=20 VALUE="<? echo $curr_usr["login"]; ?>"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Zmìnit údaje"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><B>Podpis</B> je použit pøi zobrazování novinek jako informace kdo novinku napsal. Také se zobrazuje pøi pøihlášení v navigaèní lištì vlevo dole.</TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Pøihlašovací jméno : </b>Doporuèujeme použití slova bez diakritiky a rozlišování velkým a malých písmen (jedno kterou variantu vyberete). V pøihlašovacím jménì mohou být i èíslice, kromì prvního písmene. Nedoporuèujem též používání mezer v jménu. Volte jméno tak, aby se zabránilo kolizím mezi jmény uživatelù. Minimální délka jsou 4 znaky.<BR></TD>
</TR>
</TABLE>
</FORM>

<BR><hr><BR>
<H3>Zmìna hesla</H3>

<FORM METHOD=POST ACTION="./us_setup_exc.php?type=2&id=<?echo $usr->account_id;?>">
<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Staré heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="oldheslo" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Nové heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="heslo" VALUE="" SIZE="20"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Nové heslo (ovìøení):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="heslo2" VALUE="" SIZE="20"></TD>
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
	<TD colspan="3"><b>Omezení hesla : </b>Hesla zadávejte bez diakritiky, mohou být i èíslice, minimálnì 4 znaky dlouhá. A nejlépe taková co každého nenapadnou jako první. Hesla typu "12345", "brno" nebo vaše pøezdívka budou bez varování zmìnìny! Nedoporuèuji ani používání jmen dìtí, rodièù pøípadnì domácích mazlíèkù v pùvodní podobì. Použijte alespoò zdrobnìlinu nebo domácí variantu, pøípadnì doplòte jméno nìjakým èíslem (kromì registraèního nebo roku narození).</TD>
</TR>
</TABLE>
</FORM>
<BR>
<?
}
?>

</CENTER>