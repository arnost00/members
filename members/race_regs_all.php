<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require("./cfg/_colors.php");
require("./cfg/_globals.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
require ("./common.inc.php");

if (!IsLoggedRegistrator() && !IsLoggedManager()&& !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require ("./ctable.inc.php");
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common_race.inc.php");
include ("./common_user.inc.php");
include ('./url.inc.php');
include('./csort.inc.php');
DrawPageTitle('Hromadná pøihlášky na závody');

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

$sc = new column_sort_db();
$sc->add_column('reg','');
$sc->add_column('sort_name','');
$sc->set_url('race_regs_all.php?gr_id='.$gr_id.'&id='.$id,true);
$sc->set_default_sort(0,1);
$sub_query = $sc->get_sql_string();

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_,false,true);
?>
<BR>
<? DrawPageSubTitle('Pøihlášky'); ?>

<p>
Pøihlášení èlena - se provede zapsáním kategorie pro pøíslušného èlena.<BR>
Odhlášení èlena - se provede vymazáním kategorie (prázné textové pole) pro pøíslušného èlena.<BR>
Zmìna kategorie - se provede zmìnou textového pole s kategorií pro pøíslušného èlena.<BR>
<span class="WarningText">Do sloupcù, které nechcete mìnit nezasahujte !!</span>
</p>

<SCRIPT LANGUAGE="JavaScript">
//<!--
var focused_row = -1;

function select_row(row)
{
	focused_row = row;
}

function zmen_kat(kat)
{
	if (focused_row != -1)
	{
		kat_name = 'kateg[' + focused_row +']';
		document.form1.elements[kat_name].value = kat;
	}
	return false;
}

//-->
</SCRIPT>
<?
if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplòující informace o závodì (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}

$termin = raceterms::GetCurr4RegTerm($zaznam_z);

if($termin == 0 && !IsLoggedAdmin() && !IsLoggedRegistrator())
{
	echo('Nelze provádìt pøihlášky, nejspíš už vypršely všechny termíny pøihlášek, je po závodì, èi není aktivní žádný termín pro pøihlášení.');
}
else
{
?>

<FORM METHOD=POST ACTION="./race_regs_all_exc.php?gr_id=<?echo $gr_id;?>&id=<?echo $id;?>" name="form1">
<?

$sub_query2 = (IsLoggedRegistrator() || IsLoggedManager()) ? '' : ' AND '.TBL_USER.'.chief_id = '.$usr->user_id.' OR '.TBL_USER.'.id = '.$usr->user_id;

$query = 'SELECT '.TBL_USER.'.id, prijmeni, jmeno, reg, datum, kat, pozn, pozn_in, termin FROM '.TBL_USER.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_USER.'.id = '.TBL_ZAVXUS.'.id_user AND '.TBL_ZAVXUS.'.id_zavod='.$id.' WHERE '.TBL_USER.'.hidden = 0'.$sub_query2.$sub_query;

@$vysledek=MySQL_Query($query);

//echo "Poèet již pøihlášených èlenù je ".mysql_num_rows($vysledek_p).".<BR>";

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_show_on = ($zaznam_z['prihlasky'] > 1);
$is_termin_edit_on = $is_registrator_on && $is_termin_show_on;

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poø.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Reg.è.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Pøíjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Vìk',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
if($is_termin_show_on)
	$data_tbl->set_header_col_with_help($col++,'T.',ALIGN_CENTER,"Èíslo termínu pøihlášky");
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Poznámka(interní)',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$data_tbl->set_sort_col(1,$sc->get_col_content(0));
$data_tbl->set_sort_col(2,$sc->get_col_content(1));
echo $data_tbl->get_sort_row()."\n";

$i=1;
while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$row = array();
	$row[] = $i++;
	$row[] = RegNumToStr($zaznam['reg']);
	$row[] = $zaznam['prijmeni'];
	$row[] = $zaznam['jmeno'];
	$age = CountManAge($zaznam['datum']);
	$row[] = ($age != -1) ? (($age < GC_SHOW_AGE_LIMIT)? $age :'') : '?';
	$u=$zaznam['id'];
	if ($zaznam['kat'] != NULL)
	{	// jiz prihlasen
		if($zaznam['termin'] == $termin || $is_termin_edit_on || $zaznam_z['prihlasky'] == 1)
		{	// aktualni termin nebo povelena komplet editace
			$row[] = /*$zaznam['termin'].'/'.$termin.*/'<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 value="'.$zaznam['kat'].'" onfocus="javascript:select_row('.$u.');">';
			if($is_termin_edit_on)
				$row[] = '<INPUT TYPE="text" NAME="term['.$u.']" SIZE=1 value="'.$zaznam['termin'].'" onfocus="javascript:select_row('.$u.');">';
			else if($is_termin_show_on)
				$row[] = $zaznam['termin'];
			$row[] = '<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn'].'" onfocus="javascript:select_row('.$u.');">';
			$row[] = '<INPUT TYPE="text" NAME="pozn2['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn_in'].'" onfocus="javascript:select_row('.$u.');">';
		}
		else
		{
			$row[] = /*$zaznam['termin'].'/'.$termin.*/'<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 value="'.$zaznam['kat'].'" onfocus="javascript:select_row('.$u.');" disabled readonly>';
			if($is_termin_edit_on)
				$row[] = '<INPUT TYPE="text" NAME="term['.$u.']" SIZE=1 value="'.$zaznam['termin'].'" onfocus="javascript:select_row('.$u.');" disabled readonly>';
			if($is_termin_show_on)
				$row[] = $zaznam['termin'];
			$row[] = '<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn'].'" onfocus="javascript:select_row('.$u.');">';
			$row[] = '<INPUT TYPE="text" NAME="pozn2['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn_in'].'" onfocus="javascript:select_row('.$u.');">';
		}
	}
	else
	{	// neprihlasen
		$row[] = '<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 onfocus="javascript:select_row('.$u.');">';
		if($is_termin_edit_on)
		{
			$row[] = '<INPUT TYPE="text" NAME="term['.$u.']" SIZE=1 value="'.(($termin != 0) ? $termin : $zaznam_z['prihlasky']).'" onfocus="javascript:select_row('.$u.');">';
		}
		else if($is_termin_show_on)
		{
			$row[] = (($termin != 0) ? $termin : $zaznam_z['prihlasky']);
		}
		
		$row[] = '<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" onfocus="javascript:select_row('.$u.');">';
		$row[] = '<INPUT TYPE="text" NAME="pozn2['.$u.']" size="25" maxlength="250" onfocus="javascript:select_row('.$u.');">';
	}
	if ($zaznam['id'] == $usr->user_id) 
		$data_tbl->set_next_row_highlighted();
	echo $data_tbl->get_new_row_arr($row)."\n";
}

echo $data_tbl->get_footer()."\n";
?>
Možnosti (kategorie)<BR>
<?
	echo "<button onclick=\"javascript:zmen_kat('');return false;\">Vyprázdnit</button>&nbsp;";
	$kategorie=explode(';',$zaznam_z['kategorie']);
	for ($i=0; $i<count($kategorie)-1; $i++)
	{
		echo "<button onclick=\"javascript:zmen_kat('".$kategorie[$i]."');return false;\">".$kategorie[$i]."</button>";
	}
?>
<BR>
Vyberte závodníka klepnutím do políèka kategorie u závodníka a následnì vložte vybranou kategorii pomocí tlaèítka s názvem kategorie.<BR>
<BR>
<INPUT TYPE="submit" value='Proveï zmìny'>
</FORM>
<?
}
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>
</BODY>
</HTML>
