<?php /* adminova stranka - vlozeni clena */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (IsLogged())
{
	if(IsSet($update))
		echo '<H3>Editace údajù vybraného èlena oddílu</H3>';
	else
	{
		echo '<H3>Vložení nového èlena</H3>';
		$zaznam['prijmeni'] = '';
		$zaznam['jmeno'] = '';
		$zaznam['reg'] = '';
		$zaznam['si_chip'] = '';
		$zaznam['datum'] = '';
		$zaznam['adresa'] = '';
		$zaznam['mesto'] = '';
		$zaznam['psc'] = '000 00';
		$zaznam['email'] = '';
		$zaznam['tel_domu'] = '';
		$zaznam['tel_zam'] = '';
		$zaznam['tel_mobil'] = '';
		$zaznam['poh'] = 'H';
		$zaznam['lic'] = 'C';
		$zaznam['lic_mtbo'] = '-';
		$zaznam['lic_lob'] = '-';
		$zaznam['hidden'] = 0;
		$zaznam['fin'] = '';
	}
?>
<FORM METHOD=POST ACTION="./user_new_exc.php<?if (IsSet($update)) echo "?update=".$update?>">
<TABLE width="90%">
<TR>
	<TD width="45%" align="right">Pøíjmení</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="prijmeni" SIZE=20 VALUE="<?echo $zaznam["prijmeni"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Jméno</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="jmeno" SIZE=15 VALUE="<?echo $zaznam["jmeno"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Registraèní èíslo</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><? echo $g_shortcut; ?>&nbsp;&nbsp;<INPUT TYPE="text" NAME="reg" SIZE=4 VALUE="<?echo RegNumToStr($zaznam['reg'])?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Èíslo SI èipu</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="si" SIZE=9 MAXLENGTH=9 VALUE="<?echo $zaznam["si_chip"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Datum narození</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=10 VALUE="<?echo SQLDate2String($zaznam["datum"])?>">&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="45%" align="right">Adresa</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="adresa" SIZE=40 VALUE="<?echo $zaznam["adresa"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Mìsto</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="mesto" SIZE=20 VALUE="<?echo $zaznam["mesto"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">PSÈ</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="psc" SIZE=6 VALUE="<?echo $zaznam["psc"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Email</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="email" SIZE=40 VALUE="<?echo $zaznam["email"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Tel. domù</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="domu" SIZE=15 VALUE="<?echo $zaznam["tel_domu"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Tel. zamìstnání</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="zam" SIZE=15 VALUE="<?echo $zaznam["tel_zam"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Mobil</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="mobil" SIZE=15 VALUE="<?echo $zaznam["tel_mobil"]?>"></TD>
</TR>
<TR>
	<TD width="45%" align="right">Pohlavi</TD>
	<TD width="5"></TD>
	<TD class="DataValue">
	<select name='poh'>
			<option value='H' <?if ($zaznam["poh"]=='H') {echo "SELECTED";}?>>H</option>
			<option value='D' <?if ($zaznam["poh"]=='D') {echo "SELECTED";}?>>D</option>
	</select>
	</TD>
</TR>
<TR>
	<TD width="45%" align="right">Licence OB</TD>
	<TD width="5"></TD>
	<TD class="DataValue">
	<select name='lic'>
			<option value='E' <?if ($zaznam["lic"]=='E') {echo "SELECTED";}?>>E</option>
			<option value='A' <?if ($zaznam["lic"]=='A') {echo "SELECTED";}?>>A</option>
			<option value='B' <?if ($zaznam["lic"]=='B') {echo "SELECTED";}?>>B</option>
			<option value='C' <?if ($zaznam["lic"]=='C') {echo "SELECTED";}?>>C</option>
			<option value='D' <?if ($zaznam["lic"]=='D') {echo "SELECTED";}?>>D</option>
			<option value='R' <?if ($zaznam["lic"]=='R') {echo "SELECTED";}?>>R</option>
			<option value='-' <?if ($zaznam["lic"]=='-') {echo "SELECTED";}?>>-</option>
	</select>
	</TD>
</TR>
<TR>
	<TD width="45%" align="right">Licence MTBO</TD>
	<TD width="5"></TD>
	<TD class="DataValue">
	<select name='lic_mtbo'>
			<option value='E' <?if ($zaznam["lic_mtbo"]=='E') {echo "SELECTED";}?>>E</option>
			<option value='A' <?if ($zaznam["lic_mtbo"]=='A') {echo "SELECTED";}?>>A</option>
			<option value='B' <?if ($zaznam["lic_mtbo"]=='B') {echo "SELECTED";}?>>B</option>
			<option value='C' <?if ($zaznam["lic_mtbo"]=='C') {echo "SELECTED";}?>>C</option>
			<option value='D' <?if ($zaznam["lic_mtbo"]=='D') {echo "SELECTED";}?>>D</option>
			<option value='R' <?if ($zaznam["lic_mtbo"]=='R') {echo "SELECTED";}?>>R</option>
			<option value='-' <?if ($zaznam["lic_mtbo"]=='-') {echo "SELECTED";}?>>-</option>
	</select>
	</TD>
</TR>
<TR>
	<TD width="45%" align="right">Licence LOB</TD>
	<TD width="5"></TD>
	<TD class="DataValue">
	<select name='lic_lob'>
			<option value='E' <?if ($zaznam["lic_lob"]=='E') {echo "SELECTED";}?>>E</option>
			<option value='A' <?if ($zaznam["lic_lob"]=='A') {echo "SELECTED";}?>>A</option>
			<option value='B' <?if ($zaznam["lic_lob"]=='B') {echo "SELECTED";}?>>B</option>
			<option value='C' <?if ($zaznam["lic_lob"]=='C') {echo "SELECTED";}?>>C</option>
			<option value='D' <?if ($zaznam["lic_lob"]=='D') {echo "SELECTED";}?>>D</option>
			<option value='R' <?if ($zaznam["lic_lob"]=='R') {echo "SELECTED";}?>>R</option>
			<option value='-' <?if ($zaznam["lic_lob"]=='-') {echo "SELECTED";}?>>-</option>
	</select>
	</TD>
</TR>
<? 
if (IsLoggedAdmin())
{
?>
<TR>
	<TD width="45%" align="right"></TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="checkbox" NAME="hidden" SIZE=15 VALUE="1" <? if ($zaznam["hidden"]) echo "checked" ?> > Skrytý èlen (vidí ho jen admin)</TD>
</TR>
<?
}
?>
<? /*
<TR> //finance uzivatelu

	<TD width="45%" align="right">Finance</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="fin" size="5" VALUE="<?echo $zaznam["fin"]?>"></TD>
</TR> //finance uzivatelu 
*/ ?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="<?echo (IsSet($update)) ? "Zmìnit údaje èlena" : "Vytvoøit nového èlena"; ?>"></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3"><b>Upozornìní:</b> Zadané jméno a další údaje se používají i pro potøeby registrace závodníka do centrální registrace a pøi prihlašování na závody.</TD>
</TR>
</TABLE>
</FORM>
<?
}
?>