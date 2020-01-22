<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");

if (!IsLoggedRegistrator() && !IsLoggedManager() && !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common_user.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

DrawPageTitle('Přihláška člena na závody');
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

@$vysledek_z=query_db("SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = mysqli_fetch_array($vysledek_z);

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_,false,true);
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>
<?
DrawPageSubTitle('Přihlášky');

$termin = raceterms::GetCurr4RegTerm($zaznam_z);

if($termin == 0 && !IsLoggedAdmin() && !IsLoggedRegistrator())
{
	echo('Nelze provádět přihlášky, nejspíš už vypršely všechny termíny přihlášek, je po závodě, či není aktivní žádný termín pro přihlášení.');
}
else
{
?>
<p>
Přihlášení člena - se provede zapsáním kategorie pro vybraného člena.<BR>
Odhlášení člena - se provede vymazáním kategorie (prázné textové pole) pro vybraného člena.<BR>
Změna kategorie - se provede změnou textového pole s kategorií pro vybraného člena.<BR>
</p>
<FORM METHOD=POST ACTION="./race_regs_1_exc.php?gr_id=<?echo $gr_id;?>&id=<?echo $id;?>" name="form1" onReset="javascript:aktu_line();">
<?

$sub_query = (IsLoggedRegistrator() || IsLoggedManager()) ? '' : ' AND '.TBL_USER.'.chief_id = '.$usr->user_id.' OR '.TBL_USER.'.id = '.$usr->user_id;

$query = 'SELECT '.TBL_USER.'.id, prijmeni, jmeno, reg, kat, pozn, pozn_in, termin, entry_locked, '.TBL_ZAVXUS.'.transport, '.TBL_ZAVXUS.'.ubytovani FROM '.TBL_USER.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_USER.'.id = '.TBL_ZAVXUS.'.id_user AND '.TBL_ZAVXUS.'.id_zavod='.$id.' WHERE '.TBL_USER.'.hidden = 0'.$sub_query.' ORDER BY sort_name ASC';

@$vysledek=query_db($query);

echo '<TABLE width="90%">';
echo '<TR>';
echo '<TD align="right">Vyber člena</TD>';
echo '<TD width="5"></TD>';
echo '<TD><SELECT name="user_id" size=1 onchange="javascript:aktu_line();">'."\n";

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_show_on = $is_registrator_on && ($zaznam_z['prihlasky'] > 1);
$is_spol_dopr_on = ($zaznam_z["transport"]==1) && $g_enable_race_transport;
$is_spol_dopr_auto = ($zaznam_z["transport"]==2) && $g_enable_race_transport;
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1) && $g_enable_race_accommodation;
$is_spol_ubyt_auto = ($zaznam_z["ubytovani"]==2) && $g_enable_race_accommodation;

$i=0;
$us_rows = array();
while ($zaznam=mysqli_fetch_array($vysledek))
{
	if ($zaznam['entry_locked'] == 0)
	{
		if($zaznam['kat'] != NULL)
		{
			if($zaznam['termin'] == $termin || $is_termin_show_on)
			{
				$us_rows[$i][0] = $zaznam['kat'];
				$us_rows[$i][1] = $zaznam['pozn'];
				$us_rows[$i][2] = $zaznam['pozn_in'];
				$us_rows[$i][3] = ($is_termin_show_on) ? $zaznam['termin'] : 0;
				$us_rows[$i][4] = ($is_spol_dopr_on) ? $zaznam['transport'] == 1 : 0;
				$us_rows[$i][5] = ($is_spol_ubyt_on) ? $zaznam['ubytovani'] == 1 : 0;
			}
			else
			{
				continue;
			}
		}
		echo '<option value="'.$zaznam['id'].'">'.$zaznam['prijmeni'].' '.$zaznam['jmeno'].' ['.RegNumToStr($zaznam['reg'])."]</option>\n";
		$i++;
	}
}
echo '</SELECT>&nbsp;*</TD>'."\n";
echo'<SCRIPT LANGUAGE="JavaScript">'."\n";
echo'//<!--'."\n";

echo 'us_rows = new Array('.sizeof($us_rows).');'."\n";
foreach ($us_rows as $i => $value)
{
	echo 'us_rows['.$i.'] = ["'.$value[0].'","'.$value[1].'","'.$value[2].'","'.$value[3].'","'.($value[4] ? 'true' : 'false').'","'.($value[5] ? 'true' : 'false').'"];'."\n";
}

echo'//-->'."\n";
echo'</SCRIPT>'."\n";

echo '<TR>';
echo '<TD align="right">Kategorie</TD>';
echo '<TD width="5"></TD>';
echo '<TD><INPUT TYPE="text" NAME="kateg" SIZE=5></TD>';
echo '</TR>';
if($is_spol_dopr_on)
{
	echo '<TR>';
	echo '<TD align="right">Společná doprava</TD>';
	echo '<TD width="5"></TD>';
	echo '<TD><INPUT TYPE="checkbox" NAME="transport"></TD>';
	echo '</TR>';
}
else if ($is_spol_dopr_auto)
{
	echo '<TR>';
	echo '<TD align="right">Společná doprava</TD>';
	echo '<TD width="5"></TD>';
	echo '<TD>Automaticky</TD>';
	echo '</TR>';
}
if($is_spol_ubyt_on)
{
	echo '<TR>';
	echo '<TD align="right">Společné ubytování</TD>';
	echo '<TD width="5"></TD>';
	echo '<TD><INPUT TYPE="checkbox" NAME="ubytovani"></TD>';
	echo '</TR>';
}
else if ($is_spol_ubyt_auto)
{
	echo '<TR>';
	echo '<TD align="right">Společné ubytování</TD>';
	echo '<TD width="5"></TD>';
	echo '<TD>Automaticky</TD>';
	echo '</TR>';
}
echo '<TR>';
echo '<TD align="right">Poznámka</TD>';
echo '<TD width="5"></TD>';
echo '<TD><INPUT TYPE="text" NAME="pozn" size="50" maxlength="250">&nbsp;(do&nbsp;přihlášky)</TD>';
echo '</TR><TR>';
echo '<TD align="right">Poznámka</TD>';
echo '<TD width="5"></TD>';
echo '<TD><INPUT TYPE="text" NAME="pozn2" size="50" maxlength="250">&nbsp;(interní)</TD>';
echo '</TR>';
if($is_termin_show_on)
{
	echo '<TR>';
	echo '<TD align="right">Termín přihlášek</TD>';
	echo '<TD width="5"></TD>';
	echo '<TD><INPUT TYPE="text" NAME="new_termin" size="5"></TD>';
	echo '</TR>';
}
	if ($zaznam_z['kategorie'] != '')
	{
		$kategorie=explode(';',$zaznam_z['kategorie']);
?>
<TR><TD align="right" width="100">Možnosti<BR>(kategorie)</TD><TD width="5"></TD><TD width="400">
<?
		for ($i=0; $i<count($kategorie); $i++)
		{
			if ($kategorie[$i] != '')
				echo "<button onclick=\"javascript:zmen_kat('".$kategorie[$i]."');return false;\">".$kategorie[$i]."</button>";
		}
		echo "\n".'</TD></TR>';
	}
?>
<TR>
	<TD colspan="3"></TD>
</TR>
<TR>
	<TD colspan="3" align="center"><INPUT TYPE="submit" value='Proveď změnu'></TD>
</TR>
<TR>
	<TD colspan="3"></TD>
</TR>
</TABLE>

<SCRIPT LANGUAGE="JavaScript">
//<!--
function aktu_line()
{
	var idx = document.form1.user_id.selectedIndex;
	if (us_rows[idx] != null)
	{
		document.form1.kateg.value=us_rows[idx][0];
<?
	if($is_spol_dopr_on)
	{
?>
		document.form1.transport.checked=us_rows[idx][4];
<?
	}
	if($is_spol_ubyt_on)
	{
?>
		document.form1.ubytovani.checked=us_rows[idx][5];
<?
	}
?>
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
	else
	{
		document.form1.kateg.value="";
<?
	if($is_spol_dopr_on)
	{
?>
		document.form1.transport.checked=false;
<?
	}
	if($is_spol_ubyt_on)
	{
?>
		document.form1.ubytovani.checked=false;
<?
	}
?>
		document.form1.pozn.value="";
		document.form1.pozn2.value="";
<?
	if($is_termin_show_on)
	{
?>
		document.form1.new_termin.value= <? echo(($termin != 0) ? $termin : $zaznam_z['prihlasky']); ?>;
<?
	}
?>
	}
}
window.onload = aktu_line();
//-->
</SCRIPT>
</FORM>
<?
if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplňující informace o závodě (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}
?>
* Pokud vybíráte členy pomocí šipek (klávesnice), je potřeba potrvdit výběr stiskem klávesy Enter.
<?
}
?>
<BR><hr><BR>
<?
DrawPageSubTitle('Přihlášení závodníci');

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
if($is_spol_dopr_on)
	$data_tbl->set_header_col_with_help($col++,'SD',ALIGN_CENTER,'Společná doprava');
if($is_spol_ubyt_on)
	$data_tbl->set_header_col_with_help($col++,'SU',ALIGN_CENTER,'Společné ubytování');
if($zaznam_z['prihlasky'] > 1)
	$data_tbl->set_header_col($col++,'Termín',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pozn.',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Pozn.(i)',ALIGN_LEFT);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

@$vysledek=query_db("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod=$id ORDER BY id");

$i=0;
$trans=0;
$ubyt=0;
while ($zaznam=mysqli_fetch_array($vysledek))
{
	@$vysledek1=query_db("SELECT * FROM ".TBL_USER." WHERE id=$zaznam[id_user] LIMIT 1");
	$zaznam1=mysqli_fetch_array($vysledek1);
	if ($zaznam1)
	{
		$i++;

		$row = array();
		$row[] = $i.'<!-- '.$zaznam['id'].' -->';
		$row[] = $zaznam1['jmeno'];
		$row[] = $zaznam1['prijmeni'];
		$row[] = '<B>'.$zaznam['kat'].'</B>';
		if($is_spol_dopr_on)
		{
			if ($zaznam["transport"])
			{
				$row[] = '<B>X</B>';
				$trans++;
			}
			else
				$row[] = '';
		}
		if($is_spol_ubyt_on)
		{
			if ($zaznam["ubytovani"])
			{
				$row[] = '<B>X</B>';
				$ubyt++;
			}
			else
				$row[] = '';
		}
		if($zaznam_z['prihlasky'] > 1)
			$row[] = $zaznam['termin'];
		$row[] = $zaznam['pozn'];
		$row[] = $zaznam['pozn_in'];
		echo $data_tbl->get_new_row_arr($row)."\n";
	}
}
echo $data_tbl->get_footer()."\n";

echo $is_spol_dopr_on?"<BR>Počet přihlášených na dopravu: $trans":"";
echo $is_spol_ubyt_on?"<BR>Počet přihlášených na ubytování: $ubyt":"";
?>

<BR>
<?
HTML_Footer();
?>