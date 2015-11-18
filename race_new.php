<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once("./cfg/_colors.php");

require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Vytvoření nového závodu');

db_Connect();

$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;
if($type == 1)
{	// vicedenni
?>
<FORM METHOD=POST ACTION="./race_new_exc.php?rtype=1">
<TABLE width="90%">
<TR>
	<TD width="130" align="right">Datum od</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=8>&nbsp;&nbsp(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">Datum do</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum2" SIZE=8>&nbsp;&nbsp(DD.MM.RRRR)</TD>
</TR>
<?
}
else
{	// jednodenni
?>
<FORM METHOD=POST ACTION="./race_new_exc.php?rtype=0">
<TABLE width="90%">
<TR>
	<TD width="130" align="right">Datum</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right">Název</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="nazev" SIZE=60 maxlength=50></TD>
</TR>
<TR>
	<TD width="130" align="right">Místo</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="misto" SIZE=60 maxlength=50></TD>
</TR>
<TR>
	<TD width="130" align="right">Pořádající oddíl</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="oddil" SIZE=9 maxlength=7>&nbsp;&nbsp;(XYZ) nebo (XYZ+ABC)</TD>
</TR>
<TR>
	<TD width="130" align="right">Typ</TD>
	<TD width="5"></TD>
	<TD>
		<select name='typ'>
<?
		$tmp_typ = $g_racetype [0]['enum'];
		for ($ii = 0; $ii < $g_racetype_cnt; $ii++)
		{
			echo("\t\t\t<option value='".$g_racetype [$ii]['enum']."'".(($tmp_typ==$g_racetype [$ii]['enum'])?' SELECTED':'').">".$g_racetype [$ii]['nm']."</option>\n");
		}
?>
		</select>
	</TD>
</TR>
<TR>
	<TD width="130" align="right" valign="top">Žebříček</TD>
	<TD width="5"></TD>
	<TD>
<?
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		echo('<input type="checkbox" name="zebricek['.$ii.']" value="1" id="id_'.$ii.'"><label for="id_'.$ii.'">'.$g_zebricek [$ii]['nm'].'</label>');
		echo('<br>');
	}
?>
	</TD>
</TR>
<TR>
	<TD width="130" align="right">Ranking</TD>
	<TD width="5"></TD>
	<TD>
		<select name='ranking'>
			<option value='1' SELECTED>ANO</option>
			<option value='0'>NE</option>
		</select>
	</TD>
</TR>
<?
if ($g_enable_race_transport)
{
?>
<TR>
	<TD width="130" align="right" valign="top">Společná doprava</TD>
	<TD width="5"></TD>
	<TD>
		<input type="radio" name="transport" value="0" id="radio_ff0" <?if ($g_race_transport_default==0) echo "checked=\"checked\"";?>><label for="radio_ff0">Bez společné dopravy</label><br>
		<input type="radio" name="transport" value="1" id="radio_ff1" <?if ($g_race_transport_default==1) echo "checked=\"checked\"";?>><label for="radio_ff1">Společná doprava s výběrem účasti</label><br>
		<input type="radio" name="transport" value="2" id="radio_ff2" <?if ($g_race_transport_default==2) echo "checked=\"checked\"";?>><label for="radio_ff2">Automatická společná doprava</label>
	</TD>
</TR>
<?
}
if ($g_enable_race_accommodation)
{
?>
<TR>
	<TD width="130" align="right" valign="top">Společné ubytování</TD>
	<TD width="5"></TD>
	<TD>
		<input type="radio" name="accommodation" value="0" id="radio_acc0" <?if ($g_race_accommodation_default==0) echo "checked=\"checked\"";?>><label for="radio_acc0">Bez společného ubytování</label><br>
		<input type="radio" name="accommodation" value="1" id="radio_acc1" <?if ($g_race_accommodation_default==1) echo "checked=\"checked\"";?>><label for="radio_acc1">Společné ubytování s výběrem účasti</label><br>
		<input type="radio" name="accommodation" value="2" id="radio_acc2" <?if ($g_race_accommodation_default==2) echo "checked=\"checked\"";?>><label for="radio_acc2">Automatické společné ubytování</label>
	</TD>
</TR>
<?
}
?>	
<TR>
	<TD width="130" align="right">Odkaz</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="odkaz" SIZE=60 maxlength=100 VALUE=""></TD>
</TR>
<?
if($type == 1)
{	// vicedenni
?>
<TR>
	<TD width="130" align="right">Počet etap</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="etap" SIZE=2></TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right" valign="top">Poznámka k závodu</TD>
	<TD width="5"></TD>
	<TD>
	<TEXTAREA name="poznamka" cols="45" rows="5"></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD width="130" align="right">1. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky1" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">2. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky2" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">3. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky3" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">4. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky4" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">5. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky5" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Vytvořit závod">&nbsp;&nbsp;<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
</TABLE>
</FORM>
<?
HTML_Footer();
?>