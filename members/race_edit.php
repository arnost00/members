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
DrawPageTitle('Editace parametrů závodu');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

@$vysledek=mysqli_query($db_conn, "SELECT * FROM ".TBL_RACE." where id=$id LIMIT 1");
$zaznam=mysqli_fetch_array($vysledek);

if($zaznam['vicedenni'])
{	// vicedenni
?>

<FORM METHOD=POST ACTION="./race_edit_exc.php?rtype=1&id=<?echo $id?>" name="form2">

<TABLE width="90%">
<TR>
	<TD width="130" align="right">Datum od</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=8 value=<?echo Date2String($zaznam["datum"])?>>&nbsp;&nbsp(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">Datum do</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum2" SIZE=8 value=<?echo Date2String($zaznam["datum2"])?>>&nbsp;&nbsp(DD.MM.RRRR)</TD>
</TR>
<?
}
else
{	// jednodenni
?>
<FORM METHOD=POST ACTION="./race_edit_exc.php?rtype=0&id=<?echo $id?>" name="form2">

<TABLE width="90%">
<TR>
	<TD width="130" align="right">Datum</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=8 value=<?echo Date2String($zaznam["datum"])?>>&nbsp;&nbsp(DD.MM.RRRR)</TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right">Název</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="nazev" SIZE=60 maxlength=50 value="<?echo $zaznam["nazev"]?>"></TD>
</TR>
<TR>
	<TD width="130" align="right">Místo</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="misto" SIZE=60 maxlength=50 value="<?echo $zaznam["misto"]?>"></TD>
</TR>
<TR>
	<TD width="130" align="right">Pořádající oddíl</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="oddil" SIZE=9 maxlength=7 value="<?echo $zaznam["oddil"]?>">&nbsp;&nbsp;(XYZ) nebo (XYZ+ABC)</TD>
</TR>
<TR>
	<TD width="130" align="right">Zrušeno</TD>
	<TD width="5"></TD>
	<TD><input type="checkbox" name="cancelled" id="cancelled" <?if ($zaznam["cancelled"]==1) echo " CHECKED";?>></TD>
</TR>
<TR>
	<TD width="130" align="right">Typ akce</TD>
	<TD width="5"></TD>
	<TD>
		<select name='typ0'>
<?
		$tmp_typ = $zaznam['typ0'];
		foreach ( $g_racetype0 as $key => &$value )
		{
			echo("\t\t\t<option value='".$key."'".(($tmp_typ==$key)?' SELECTED':'').">".$value."</option>\n");
		}
?>
		</select>
	</TD>
</TR>
<TR>
	<TD width="130" align="right">Sport</TD>
	<TD width="5"></TD>
	<TD>
		<select name='typ'>
<?
		$tmp_typ = $zaznam['typ'];
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
		echo('<input type="checkbox" name="zebricek['.$ii.']" value="1" id="id_'.$ii.'"');
		if(($zaznam['zebricek'] & $g_zebricek [$ii]['id']) != 0)
			echo(' checked');
		echo('><label for="id_'.$ii.'">'.$g_zebricek [$ii]['nm'].'</label>');
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
			<option value='1' <?if ($zaznam["ranking"]==1) echo " SELECTED";?> >ANO</option>
			<option value='0' <?if ($zaznam["ranking"]==0) echo " SELECTED";?> >NE</option>
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
		<input type="radio" name="transport" value="0" id="radio_ff0" <?if ($zaznam["transport"]==0) echo "checked=\"checked\"";?>><label for="radio_ff0">Bez společné dopravy</label><br>
		<input type="radio" name="transport" value="1" id="radio_ff1" <?if ($zaznam["transport"]==1) echo "checked=\"checked\"";?>><label for="radio_ff1">Společná doprava s výběrem účasti</label><br>
		<input type="radio" name="transport" value="2" id="radio_ff2" <?if ($zaznam["transport"]==2) echo "checked=\"checked\"";?>><label for="radio_ff2">Automatická společná doprava</label>
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
		<input type="radio" name="accommodation" value="0" id="radio_acc0" <?if ($zaznam["ubytovani"]==0) echo "checked=\"checked\"";?>><label for="radio_acc0">Bez společného ubytování</label><br>
		<input type="radio" name="accommodation" value="1" id="radio_acc1" <?if ($zaznam["ubytovani"]==1) echo "checked=\"checked\"";?>><label for="radio_acc1">Společné ubytování s výběrem účasti</label><br>
		<input type="radio" name="accommodation" value="2" id="radio_acc2" <?if ($zaznam["ubytovani"]==2) echo "checked=\"checked\"";?>><label for="radio_acc2">Automatické společné ubytování</label>
	</TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right">Odkaz</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="odkaz" SIZE=60 maxlength=100 value="<?echo (($zaznam['odkaz'] != '') ? cononize_url($zaznam['odkaz'],1):''); ?>"></TD>
</TR>
<?
if($zaznam['vicedenni'])
{	// vicedenni
?>
<TR>
	<TD width="130" align="right">Počet etap</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="etap" SIZE=2 value="<?echo $zaznam["etap"]?>"></TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right" valign="top">Poznámka k závodu</TD>
	<TD width="5"></TD>
	<TD>
	<TEXTAREA name="poznamka" cols="45" rows="5"><?echo $zaznam["poznamka"];?></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD width="130" align="right">1. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky1" SIZE=8 value=<?echo Date2String($zaznam["prihlasky1"])?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">2. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky2" SIZE=8 value=<?echo Date2String($zaznam["prihlasky2"])?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">3. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky3" SIZE=8 value=<?echo Date2String($zaznam["prihlasky3"])?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">4. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky4" SIZE=8 value=<?echo Date2String($zaznam["prihlasky4"])?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">5. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky5" SIZE=8 value=<?echo Date2String($zaznam["prihlasky5"])?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" VALUE="Aktualizovat údaje">&nbsp;&nbsp;<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
</TABLE>
</FORM>
<?
HTML_Footer();
?>