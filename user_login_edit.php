<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
if (!IsLoggedSmallAdmin() && !IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once ("./common_user.inc.php");
require_once ("./common.inc.php");

db_Connect();
$id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

@$vysledek=mysqli_query($db_conn, "SELECT jmeno,prijmeni,datum,hidden,email,reg FROM ".TBL_USER." WHERE id = '$id' LIMIT 1");
@$zaznam=mysqli_fetch_array($vysledek);
if (!$zaznam)
{	// error not exist
	header("location: ".$g_baseadr."error.php?code=201");
	exit;
}
require_once "./header.inc.php"; // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Členská základna - Administrace uživatelských účtů');

	$id_acc = GetUserAccountId_Users($id);
	$vysledek2=mysqli_query($db_conn, "SELECT login,podpis,policy_news,policy_regs,policy_mng,policy_adm,policy_fin,locked FROM ".TBL_ACCOUNT." WHERE id = '$id_acc' LIMIT 1");
	$zaznam2=mysqli_fetch_array($vysledek2);
?>
<script>
function changeVisibility(name, atr_id)
{
	var atr = document.getElementById(atr_id);
	var list = document.getElementsByName(name);
	if (atr.checked)
	{
		for (var i = 0; i < list.length; i++) {
			list[i].style.display = "table-row";
		}
	} else {
		for (var i = 0; i < list.length; i++) {
			list[i].style.display = "none";
		} 
	}
}

//funkce pro zobrazeni/schovani vybranych casti
function checkAllVisibilities()
{
<?
	if($zaznam2 == FALSE)
	{	// novy uzivatel
?>
	changeVisibility('acc_email_hide', 'id_email');
	changeVisibility('acc_manual_hide', 'id_manual');
<?
	}
	else
	{	// editace
?>
	changeVisibility('pass_email_hide', 'id_email');
	changeVisibility('pass_manual_hide', 'id_manual');
<?
	}
?>
}

</script>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
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
$new_account = ($zaznam2 == FALSE);
if ($new_account)
{
	$zaznam2["login"] = '';
	$zaznam2["podpis"] = '';
	$zaznam2["policy_news"] = 0;
	$zaznam2["policy_regs"] = 0;
	$zaznam2["policy_mng"] = 0;
	$zaznam2["policy_adm"] = 0;
	$zaznam2["policy_fin"] = 0;
	$zaznam2["locked"] = 0;
	
}

	if(!$new_account)
		DrawPageSubTitle('Editace účtu vybraného člena oddílu');
	else
		DrawPageSubTitle('Založení nového účtu vybranému členu oddílu');
?>
<FORM METHOD=POST ACTION="./user_login_edit_exc.php?type=<? echo ($zaznam2 != FALSE) ? "1" : "2"; echo "&id=".$id;?>">
<TABLE width="90%">
<?
	if($new_account)
	{
?>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('acc_manual_hide', this.id); changeVisibility('acc_email_hide', 'id_email');" type="radio" name="action_type" value="1" checked id="id_manual"><label for="id_manual">Ručně</label></TD></TR>
</TR>
<?
	}
?>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right">Přihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="login" SIZE=20 VALUE="<? echo $zaznam2["login"]; ?>"></TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right">Podpis uživatele</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="podpis" SIZE=20 VALUE="<?echo  $zaznam2["podpis"]?>"></TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="news" SIZE=15 VALUE="1" <? if ($zaznam2["policy_news"]) echo "checked" ?> >Povoleno psaní novinek</TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="mng" SIZE=15 VALUE="<? echo (!IsLoggedSmallAdmin() && $zaznam2["policy_mng"] == _MNG_BIG_INT_VALUE_) ? '2' : '1'; ?>" <? if ($zaznam2["policy_mng"] == _MNG_SMALL_INT_VALUE_) echo "checked"; if (!IsLoggedSmallAdmin() && $zaznam2["policy_mng"] == _MNG_BIG_INT_VALUE_) echo "disabled"; ?> >Uživatel je malým trenenérem (může měnit údaje a přihlášky vybraných členů)</TD>
</TR>
<? if (IsLoggedSmallAdmin())
{ ?>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="mng2" SIZE=15 VALUE="1" <? if ($zaznam2["policy_mng"] == _MNG_BIG_INT_VALUE_) echo "checked" ?> >Uživatel je trenenérem (může měnit údaje a přihlášky členů)</TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="regs" SIZE=15 VALUE="1" <? if ($zaznam2["policy_regs"]) echo "checked" ?> >Uživatel je přihlašovatelem (může editovat přihlášky členů - provádí export)</TD>
</TR>
<? if ($g_enable_finances)
{ ?>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="fin" SIZE=15 VALUE="1" <? if ($zaznam2["policy_fin"]) echo "checked" ?> >Uživatel je finančníkem</TD>
</TR>
<? } ?>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="adm" SIZE=15 VALUE="1" <? if ($zaznam2["policy_adm"]) echo "checked" ?> >Uživatel je správcem</TD>
</TR name="acc_manual_hide" id="acc_manual_hide">
<? if ($zaznam2["locked"] != 0) { ?>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="45%" align="right">Tento uživatel má</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><span class="WarningText">uzamčený účet</span></TD>
</TR>
<? } ?>

<?
}
if($new_account)
{ // novy ucet
require_once ('generators.inc.php');
?>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD colspan="3"></TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right">Nové heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo" VALUE="" SIZE="20"></TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD width="30%" align="right">Nové heslo (ověření):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo2" VALUE="" SIZE="20"></TD>
</TR>
<TR name="acc_manual_hide" id="acc_manual_hide">
	<TD colspan="3">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('acc_email_hide', this.id); changeVisibility('acc_manual_hide', 'id_manual');" type="radio" name="action_type" value="2" id="id_email"><label for="id_email">Vygenerovat a zaslat emailem</label></TD>
</TR>
<TR name="acc_email_hide" id="acc_email_hide">
	<TD width="30%" align="right">Přihlašovací jméno</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="login_g" SIZE=20 VALUE="<? echo GenerateLogin($zaznam) ?>"></TD>
</TR>
<TR name="acc_email_hide" id="acc_email_hide">
	<TD width="30%" align="right">Podpis uživatele</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="podpis_g" SIZE=20 VALUE="<?echo $zaznam["jmeno"]?>"></TD>
</TR>
<TR name="acc_email_hide" id="acc_email_hide">
	<TD width="30%" align="right">Email pro zaslání údajů:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="email" VALUE="<?echo GetFirstEmail($zaznam["email"]);?>" SIZE="40"></TD>
</TR>
<TR name="acc_email_hide" id="acc_email_hide">
	<TD colspan="3">&nbsp;</TD>
</TR>
<?
}
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="<? echo ($zaznam2 != FALSE) ? "Provést změny" : "Založit účet"; ?>"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Přihlašovací jméno : </b>Doporučujeme použití slova bez diakritiky a rozlišování velkým a malých písmen (jedno kterou variantu vyberete). V přihlašovacím jméně mohou být i číslice, kromě prvního písmene. Nedoporučujem též používání mezer v jménu. Volte jméno tak, aby se zabránilo kolizím mezi jmény uživatelů.<BR></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><B>Podpis</B> je použit při zobrazování novinek jako informace kdo novinku napsal. Také se zobrazuje při přihlášení v navigační liště vlevo dole.<BR></TD>
</TR>
<?
if(!$new_account)
{ // zmena uctu
?>
</TABLE>
</FORM>

<BR><hr><BR>
<? DrawPageSubTitle('Změna hesla'); ?>

<FORM METHOD=POST ACTION="./user_login_edit_exc.php?type=3&id=<?echo $id;?>">
<TABLE width="90%">
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('pass_manual_hide', this.id); changeVisibility('pass_email_hide', 'id_email');" type="radio" name="action_type" value="1" checked id="id_manual"><label for="id_manual">Ručně</label></TD></TR>
</TR>
<TR name="pass_manual_hide" id="pass_manual_hide">
	<TD width="45%" align="right">Nové heslo:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo" VALUE="" SIZE="20"></TD>
</TR>
<TR name="pass_manual_hide" id="pass_manual_hide">
	<TD width="45%" align="right">Nové heslo (ověření):</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="password" NAME="nheslo2" VALUE="" SIZE="20"></TD>
</TR>
<TR name="pass_manual_hide" id="pass_manual_hide">
	<TD colspan="3">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="3" style="padding-left:20px;"><input onclick="changeVisibility('pass_email_hide', this.id); changeVisibility('pass_manual_hide', 'id_manual');" type="radio" name="action_type" value="2" id="id_email"><label for="id_email">Vygenerovat a zaslat emailem</label></TD>
</TR>
<TR name="pass_email_hide" id="pass_email_hide">
	<TD width="30%" align="right">Email pro zaslání údajů:</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="email" VALUE="<?echo GetFirstEmail($zaznam["email"]);?>" SIZE="40"></TD>
</TR>
<TR name="pass_email_hide" id="pass_email_hide">
	<TD colspan="3">&nbsp;</TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Změnit heslo"></TD>
</TR>
<?
}
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Heslo : </b>Hesla zadávejte bez diakritiky, mohou být i číslice, minimálně 4 znaky dlouhá. A nejlépe taková co každého nenapadnou jako první. Hesla typu "12345", "brno" nebo vaše přezdívka budou bez varování změněny! Nedoporučuji ani používání jmen dětí, rodičů případně domácích mazlíčků v původní podobě. Použijte alespoň zdrobnělinu nebo domácí variantu, případně doplňte jméno nějakým číslem (kromě registračního nebo roku narození).<BR></TD>
</TR>
</TABLE>
</FORM>
<BR><hr><BR>
<?
	if (!IsSet($cb)) $cb = 0;
	$cb = (int)$cb;
	if ($cb == 0)
	{
		if (IsLoggedSmallAdmin())
			$cb = 700;
		else if (IsLoggedManager())
			$cb = 700;
		else
			$cb = 600;
	}
	echo('<A HREF="index.php?id='.$cb.'&subid=1">Zpět na seznam členů</A><BR>');
?>
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

<!-- pro aktualizaci zobrazeni/schovani casti, ktere nema uzivatel pouzity -->
<style onload="checkAllVisibilities()"/>
<?
HTML_Footer();
?>