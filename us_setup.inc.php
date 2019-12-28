<?php /* clenova stranka - editace informaci a nastaveni */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Nastavení osobních údajů členů oddílu');
?>
<CENTER>
<?
if (IsLogged())
{

$vysledek=mysqli_query($db_conn, "SELECT login,podpis FROM ".TBL_ACCOUNT." WHERE id = '$usr->account_id' LIMIT 1");
$curr_usr=mysqli_fetch_array($vysledek);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require_once('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}
?>
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
   errors += '\nPříliš mnoho znaků v podpisu. Prosím odstraňte '+ (text.length - max_l_podpis)+ ' znaků.';
 }
 else if (podpis.length == 0)
 {
   errors +='\nPodpis nemůže být prázdný.';
 }
 if (login.length < 4)
 {
   errors += '\nPřihlašovací jméno musí mít minimálně 4 znaky.';
 }
 else if (login.length > max_l_login)
 {
   errors += '\nPříliš mnoho znaků v přihlašovacím jménu. Prosím odstraňte '+ (text.length - max_l_login)+ ' znaků.';
 }
 else if (!isValidLogin(login))
 {
   errors += '\nPřihlašovací jméno obsahuje nepovolené znaky, nebo kombinace znaků.';
 }

 if (heslo.length == 0)
 {
   errors +='\nOvěřovací heslo nemůže být prázdné.';
 }

 if (errors.length > 0)
 {
	alert ("Formulář nelze odeslat z následujících důvodů:\n" + errors);
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
   errors += '\nNové heslo musí mít minimálně 4 znaky.';
 }

 if (errors.length > 0)
 {
	alert ("Formulář nelze odeslat z následujících důvodů:\n" + errors);
	return false;
 }
 else
	return true;
}

//-->
</SCRIPT>

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
	<TD width="40%" align="right">Přihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="login" SIZE=20 VALUE="<? echo $curr_usr["login"]; ?>"></TD>
</TR>
<TR>
	<TD width="40%" align="right">Heslo pro ověření změny údajů</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="hesloo" SIZE=20 VALUE=""></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Změnit údaje"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><B>Podpis</B> je použit při zobrazování novinek jako informace kdo novinku napsal. Také se zobrazuje při přihlášení v navigační liště vlevo dole.</TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Přihlašovací jméno : </b>Je požadováno použití písmen bez diakritiky a mohou být velká a malá písmena. V přihlašovacím jméně mohou být i číslice, kromě prvního písmene. Též není dovoleno používání mezer v jménu. Volte jméno tak, aby se zabránilo kolizím mezi jmény uživatelů. Minimální délka jsou 4 znaky.<BR></TD>
</TR>
</TABLE>
</FORM>

<BR><hr><BR>
<? DrawPageSubTitle('Změna hesla'); ?>

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
	<TD width="45%" align="right">Nové heslo (ověření):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="heslo2" VALUE="" SIZE="20"></TD>
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
	<TD colspan="3"><b>Omezení hesla : </b>Hesla zadávejte bez diakritiky, mohou být i číslice, minimálně 4 znaky dlouhá. A nejlépe taková co každého nenapadnou jako první. Hesla typu "12345", "brno" nebo vaše přezdívka budou bez varování změněny! Nedoporučuji ani používání jmen dětí, rodičů případně domácích mazlíčků v původní podobě. Použijte alespoň zdrobnělinu nebo domácí variantu, případně doplňte jméno nějakým číslem (kromě registračního nebo roku narození).</TD>
</TR>
</TABLE>
</FORM>
<BR>
<?
}
?>

</CENTER>