<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once("./cfg/_colors.php");

require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

require_once ("./connectors.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Vytvoření nového závodu');

db_Connect();

require_once ("./common_race_ed.inc.php");

$raceInfo = null;
$ext_id_info = '';
$connector = null;

if (!empty($ext_id)) { 
	$connector = ConnectorFactory::create();

	// Get race info by race ID
	$raceInfo = $connector->getRaceInfo($ext_id);

	$type = $raceInfo->vicedenni;

	// check if ext_id is not yet used
	$query_ext = 'SELECT id, datum, nazev, ext_id'.
	' FROM '.TBL_RACE.' WHERE ext_id = '.$ext_id.
	' ORDER BY datum, datum2, id';
	$vysledek_ext=query_db($query_ext);

	if($vysledek_ext != FALSE) {
		while ($zaznam_ext=mysqli_fetch_array($vysledek_ext)) {
			if ($ext_id_info != '')
			$ext_id_info .= '<br />';
			$ext_id_info .= " \u{26A0} ID již použito : ".Date2String($zaznam_ext['datum']).' - '.$zaznam_ext['nazev'];
		}
	}
} 

if ($raceInfo === null) {
    // default
    $raceInfo = new Race([]);
}

$type = (IsSet($type) && is_numeric($type)) ? (int)$type : 0;
if($type == 1)
{	// vicedenni
?>
<FORM METHOD=POST ACTION="./race_new_exc.php?rtype=1">
<TABLE width="90%">
<TR>
	<TD width="130" align="right">Datum od</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=8 <? if (isset($raceInfo->datum))echo ('value="'. Date2String($raceInfo->datum).'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">Datum do</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum2" SIZE=8 <? if (isset($raceInfo->datum2))echo ('value="'. Date2String($raceInfo->datum2) .'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
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
	<TD class="DataValue"><INPUT TYPE="text" NAME="datum" SIZE=8 value="<? echo (Date2String($raceInfo->datum)); ?>">&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<?
}
if ( $connector !== null ) { ?>
<TR>
	<TD width="130" align="right"><? echo ($connector->getSystemName() );?> ID</TD>
	<TD width="5"></TD>
	<TD class="DataError"><INPUT TYPE="text" NAME="ext_id" SIZE=8 value="<? echo ($raceInfo->ext_id); ?>"><? echo $ext_id_info?></TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right">Název</TD>
	<TD width="5"></TD>
	<? echo generateTextFieldWithValidator($raceInfo->nazev,60,$rc_form['name']);?>
</TR>
<TR>
	<TD width="130" align="right">Místo</TD>
	<TD width="5"></TD>
	<? echo generateTextFieldWithValidator($raceInfo->misto,60,$rc_form['misto']);?>
</TR>
<TR>
	<TD width="130" align="right">Pořádající oddíl</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="oddil" SIZE=9 maxlength=8 value="<? echo ($raceInfo->oddil); ?>">&nbsp;&nbsp;(XYZ) nebo (XYZ+ABC)</TD>
</TR>
<TR>
	<TD width="130" align="right">Typ akce</TD>
	<TD width="5"></TD>
	<TD>
		<select name='typ0'>
<?
		$tmp_typ = 'Z';
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
		$tmp_typ = $raceInfo->typ;
		for ($ii = 0; $ii < $g_racetype_cnt; $ii++)
		{
			echo("\t\t\t<option value='".$g_racetype [$ii]['enum']."'".(($tmp_typ==$g_racetype [$ii]['id'])?' SELECTED':'').">".$g_racetype [$ii]['nm']."</option>\n");			
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
		if(($raceInfo->zebricek2 & $g_zebricek [$ii]['id']) != 0)
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
			<option value='1' <? if ( $raceInfo->ranking == 1 ) echo ("SELECTED" ); ?>>ANO</option>
			<option value='0' <? if ( $raceInfo->ranking != 1 ) echo ("SELECTED" ); ?>>NE</option>
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
		<input type="radio" name="transport" value="2" id="radio_ff2" <?if ($g_race_transport_default==2) echo "checked=\"checked\"";?>><label for="radio_ff2">Automatická společná doprava</label><br>
		<input type="radio" name="transport" value="3" id="radio_ff3" <?if ($g_race_transport_default==3) echo "checked=\"checked\"";?>><label for="radio_ff3">Sdílená doprava</label>
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
if ($g_enable_race_capacity) {
?>	
<TR>
	<TD width="130" align="right">Limit účastníků</TD>
	<TD width="5"></TD>
	<TD>
		<INPUT TYPE="number" NAME="kapacita" MIN="1" STEP="1">
	</TD>
</TR>
<?
}
?>	
<TR>
	<TD width="130" align="right">Odkaz</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="odkaz" SIZE=60 maxlength=100 value="<? echo ($raceInfo->odkaz); ?>"></TD>
</TR>
<?
if($type == 1)
{	// vicedenni
?>
<TR>
	<TD width="130" align="right">Počet etap</TD>
	<TD width="5"></TD>
	<TD><INPUT TYPE="text" NAME="etap" SIZE=2  value="<? echo ($raceInfo->etap); ?>"></TD>
</TR>
<?
}
?>
<TR>
	<TD width="130" align="right" valign="top">Poznámka k závodu</TD>
	<TD width="5"></TD>
	<TD>
	<TEXTAREA name="poznamka" cols="45" rows="5" value="<? echo ($raceInfo->poznamka); ?>"></TEXTAREA>
	</TD>
</TR>
<TR>
	<TD width="130" align="right">1. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky1" SIZE=8 <? if (!empty($raceInfo->prihlasky))echo ('value="'. Date2String($raceInfo->prihlasky - 86400).'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">2. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky2" SIZE=8 <? if (!empty($raceInfo->prihlasky1))echo ('value="'. Date2String($raceInfo->prihlasky1 - 86400).'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">3. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky3" SIZE=8 <? if (!empty($raceInfo->prihlasky2))echo ('value="'. Date2String($raceInfo->prihlasky2 - 86400).'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">4. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky4" SIZE=8 <? if (!empty($raceInfo->prihlasky3))echo ('value="'. Date2String($raceInfo->prihlasky3 - 86400).'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
</TR>
<TR>
	<TD width="130" align="right">5. datum přihlášek</TD>
	<TD width="5"></TD>
	<TD class="DataValue"><INPUT TYPE="text" NAME="prihlasky5" SIZE=8 <? if (!empty($raceInfo->prihlasky4))echo ('value="'. Date2String($raceInfo->prihlasky4 - 86400).'"'); ?>>&nbsp;&nbsp;(DD.MM.RRRR)</TD>
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
<input type="hidden" id="kategorie" name="kategorie" value="<? echo ($raceInfo->kategorie); ?>">
</FORM>

<?
echo(insertDocuOnLoad());
HTML_Footer();
?>
