<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
require("./cfg/_colors.php");

include ("./common.inc.php");
include ("./common_race.inc.php");
include ('./url.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Vytvoøení nového závodu');

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
	<TD width="130" align="right">Poøádající oddíl</TD>
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
	<TD width="130" align="right" valign="top">Žebøíèek</TD>
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
<TR>
	<TD width="130" align="right">Spoleèná doprava</TD>
	<TD width="5"></TD>
	<TD><input type="checkbox" name="transport" id="transport" checked></TD>
</TR>
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
	<TD width="130" align="right">Poèet etap</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="etap" SIZE=2></TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right">Poznámka k závodu</TD>
	<TD width="5"></TD>
	<TD>
	<TEXTAREA name="poznamka" cols="45" rows="5"></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD width="130" align="right">1. datum pøihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky1" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">2. datum pøihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky2" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">3. datum pøihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky3" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">4. datum pøihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky4" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">5. datum pøihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky5" SIZE=8>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Vytvoøit závod">&nbsp;&nbsp;<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
</TABLE>
</FORM>
</BODY>
</HTML>