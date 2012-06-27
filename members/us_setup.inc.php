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
<? DrawPageSubTitle('Základní údaje'); ?>

<SCRIPT LANGUAGE="JavaScript">
<!--

function check_form_1()
{ // checks ... podpis & login
 var max_l_podpis = 20;
 var max_l_login = 20;
 var podpis=document.forms["us_s1"]["podpis"].value;
 var login=document.forms["us_s1"]["login"].value;
 var heslo=document.forms["us_s1"]["hesloo"].value;
 var errors = "";
 
 if(podpis.length > max_l_podpis)
 {
   errors += '\nPøíliš mnoho znakù v podpisu. Prosím odstraòte '+ (text.length - max_l_podpis)+ ' znakù.';
 }
 else if (podpis.length == 0)
 {
   errors +='\nPodpis nemùže být prázdný.';
 }
 if (login.length < 4)
 {
   errors += '\nPøihlašovací jméno musí mít minimálnì 4 znaky.';
 }
 else if (login.length > max_l_login)
 {
   errors += '\nPøíliš mnoho znakù v pøihlašovacím jménu. Prosím odstraòte '+ (text.length - max_l_login)+ ' znakù.';
 }
 else if (!isValidLogin(login))
 {
   errors += '\nPøihlašovací jméno obsahuje nepovolené znaky, nebo kombinace znakù.';
 }

 if (heslo.length == 0)
 {
   errors +='\nOvìøovací heslo nemùže být prázdné.';
 }

 if (errors.length > 0)
 {
	alert ("Formuláø nelze odeslat z následujících dùvodù:\n" + errors);
	return false;
 }
 else
	return true;
}

function check_form_2()
{ // checks ... password
 var oldheslo=document.forms["us_s2"]["oldheslo"].value;
 var heslo=document.forms["us_s2"]["heslo"].value;
 var heslo2=document.forms["us_s2"]["heslo2"].value;
 var errors = "";
 
 if (oldheslo.length == 0)
 {
   errors +='\nChybí staré heslo.';
 }
 if (heslo !=  heslo2)
 {
   errors += '\nNové heslo se liší v zadáních.';
 }
 else if (heslo.length < 4 || heslo2.length < 4)
 {
   errors += '\nNové heslo musí mít minimálnì 4 znaky.';
 }

 if (errors.length > 0)
 {
	alert ("Formuláø nelze odeslat z následujících dùvodù:\n" + errors);
	return false;
 }
 else
	return true;
}

//-->
</SCRIPT>


<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Povoleno psaní novinek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><? echo ($usr->policy_news) ? "ano": "ne"; ?></TD>
</TR>
</TABLE>

<BR><hr><BR>
<? DrawPageSubTitle('Volitelné údaje'); ?>

<FORM METHOD=POST ACTION="./us_setup_exc.php?type=1&id=<?echo $usr->account_id;?>" name="us_s1" onsubmit="return check_form_1();">
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
	<TD width="40%" align="right">Heslo pro ovìøení zmìny údajù</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="hesloo" SIZE=20 VALUE=""></TD>
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
	<TD colspan="3"><b>Pøihlašovací jméno : </b>Je požadováno použití písmen bez diakritiky a mohou být velká a malá písmena. V pøihlašovacím jménì mohou být i èíslice, kromì prvního písmene. Též není dovoleno používání mezer v jménu. Volte jméno tak, aby se zabránilo kolizím mezi jmény uživatelù. Minimální délka jsou 4 znaky.<BR></TD>
</TR>
</TABLE>
</FORM>

<BR><hr><BR>
<? DrawPageSubTitle('Zmìna hesla'); ?>

<FORM METHOD=POST ACTION="./us_setup_exc.php?type=2&id=<?echo $usr->account_id;?>" name="us_s2" onsubmit="return check_form_2();">
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