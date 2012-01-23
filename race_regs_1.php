<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require("./cfg/_colors.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
require ("./common.inc.php");

if (!IsLoggedRegistrator() && !IsLoggedManager() && !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require ("./ctable.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common_user.inc.php");
include ("./common_race.inc.php");
include ('./url.inc.php');

DrawPageTitle('Pøihláška èlena na závody');
?>

<SCRIPT LANGUAGE="JavaScript">
//<!--

function zmen_kat(kat)
{
	document.form1.kateg.value=kat;
}

//-->
</SCRIPT>
<?

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

?>

<H3>Vybraný závod</H3>

<?
RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_);
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>
<H3>Pøihlášky</H3>
<?
$termin = raceterms::GetCurr4RegTerm($zaznam_z);

if($termin == 0 && !IsLoggedAdmin() && !IsLoggedRegistrator())
{
	echo('Nelze provádìt pøihlášky, nejspíš už vypršely všechny termíny pøihlášek, je po závodì, èi není aktivní žádný termín pro pøihlášení.');
}
else
{
?>
<p>
Pøihlášení èlena - se provede zapsáním kategorie pro vybraného èlena.<BR>
Odhlášení èlena - se provede vymazáním kategorie (prázné textové pole) pro vybraného èlena.<BR>
Zmìna kategorie - se provede zmìnou textového pole s kategorií pro vybraného èlena.<BR>
</p>
<FORM METHOD=POST ACTION="./race_regs_1_exc.php?gr_id=<?echo $gr_id;?>&id=<?echo $id;?>" name="form1" onReset="javascript:aktu_line();">
<?

$sub_query = (IsLoggedRegistrator() || IsLoggedManager()) ? '' : ' AND '.TBL_USER.'.chief_id = '.$usr->user_id.' OR '.TBL_USER.'.id = '.$usr->user_id;

$query = 'SELECT '.TBL_USER.'.id, prijmeni, jmeno, reg, kat, pozn, pozn_in, termin FROM '.TBL_USER.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_USER.'.id = '.TBL_ZAVXUS.'.id_user AND '.TBL_ZAVXUS.'.id_zavod='.$id.' WHERE '.TBL_USER.'.hidden = 0'.$sub_query.' ORDER BY reg ASC';

@$vysledek=MySQL_Query($query);

echo '<TABLE>';
echo '<TR>';
echo '<TD align="right">Vyber èlena</TD>';
echo '<TD width="5"></TD>';
echo '<TD><SELECT name="user_id" size=1 onchange="javascript:aktu_line();">'."\n";

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_show_on = $is_registrator_on && ($zaznam_z['prihlasky'] > 1);

$i=0;
$prihl_cl=0;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	if($zaznam['kat'] == NULL)
	{	// neni prihlasen
		$us_rows[$i][0] = '';
		$us_rows[$i][1] = '';
		$us_rows[$i][2] = '';
		if($is_termin_show_on)
			$us_rows[$i][3] = (($termin != 0) ? $termin : $zaznam_z['prihlasky']);
	}
	else
	{
		if($zaznam['termin'] == $termin || $is_termin_show_on)
		{
			$us_rows[$i][0] = $zaznam['kat'];
			$us_rows[$i][1] = $zaznam['pozn'];
			$us_rows[$i][2] = $zaznam['pozn_in'];
			if($is_termin_show_on)
				$us_rows[$i][3] = $zaznam['termin'];
			$prihl_cl++;
		}
		else
		{
			continue;
		}
	}
	echo '<option value="'.$zaznam['id'].'">'.$zaznam['prijmeni'].' '.$zaznam['jmeno'].' ['.RegNumToStr($zaznam['reg'])."]</option>\n";
	$i++;
}
echo '</SELECT>&nbsp;*</TD>'."\n";
echo'<SCRIPT LANGUAGE="JavaScript">'."\n";
echo'//<!--'."\n";

echo 'us_rows = new Array('.sizeof($us_rows).');'."\n";
for ($i=0; $i < sizeof($us_rows); $i++)
{
	echo'us_rows['.$i.'] = new Array("'.$us_rows[$i][0].'","'.$us_rows[$i][1].'","'.$us_rows[$i][2].(($is_termin_show_on) ? '","'.$us_rows[$i][3] : '').'");'."\n";;
}
echo'//-->'."\n";
echo'</SCRIPT>'."\n";

echo '<TR>';
echo '<TD align="right">Kategorie</TD>';
echo '<TD width="5"></TD>';
echo '<TD><INPUT TYPE="text" NAME="kateg" SIZE=5></TD>';
echo '</TR><TR>';
echo '<TD align="right">Poznámka</TD>';
echo '<TD width="5"></TD>';
echo '<TD><INPUT TYPE="text" NAME="pozn" size="50" maxlength="250">&nbsp;(do&nbsp;pøihlášky)</TD>';
echo '</TR><TR>';
echo '<TD align="right">Poznámka</TD>';
echo '<TD width="5"></TD>';
echo '<TD><INPUT TYPE="text" NAME="pozn2" size="50" maxlength="250">&nbsp;(interní)</TD>';
echo '</TR>';
if($is_termin_show_on)
{
	echo '<TR>';
	echo '<TD align="right">Termín pøihlášek</TD>';
	echo '<TD width="5"></TD>';
	echo '<TD><INPUT TYPE="text" NAME="new_termin" size="5"></TD>';
	echo '</TR>';
}
?>
<TR><TD align="right" width="100">Možnosti<BR>(kategorie)</TD><TD width="5"></TD><TD width="400">
<?
	$kategorie=explode(';',$zaznam_z['kategorie']);
	for ($i=0; $i<count($kategorie)-1; $i++)
	{
		echo "<button onclick=\"javascript:zmen_kat('".$kategorie[$i]."');return false;\">".$kategorie[$i]."</button>";
	}
echo "\n".'</TD></TR>';
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" value='Proveï zmìnu'></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
</TABLE>
<?
echo "Poèet již pøihlášených èlenù je ".$prihl_cl.".<BR><BR>";
?>
<SCRIPT LANGUAGE="JavaScript">
//<!--
function aktu_line()
{
	var idx = document.form1.user_id.selectedIndex;
	document.form1.kateg.value=us_rows[idx][0];
	document.form1.pozn.value=us_rows[idx][1];
	document.form1.pozn2.value=us_rows[idx][2];
<?
	if($is_termin_show_on)
	{
?>
	document.form1.new_termin.value=us_rows[idx][3];
<?
	}
?>
}
window.onload = aktu_line();
//-->
</SCRIPT>
</FORM>
<?
if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplòující informace o závodì (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}
?>
* Pokud vybíráte èleny pomocí šipek (klávesnice), je potøeba potrvdit výbìr stiskem klávesy Enter.
<?
}
?>
</BODY>
</HTML>
