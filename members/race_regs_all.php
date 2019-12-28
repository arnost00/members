<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once("./cfg/_globals.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");

if (!IsLoggedRegistrator() && !IsLoggedManager()&& !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common_race.inc.php");
require_once ("./common_user.inc.php");
require_once ('./url.inc.php');
require_once('./csort.inc.php');
DrawPageTitle('Hromadná přihlášky na závody');

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

$sc = new column_sort_db();
$sc->add_column('reg','');
$sc->add_column('sort_name','');
$sc->set_url('race_regs_all.php?gr_id='.$gr_id.'&id='.$id,true);
$sc->set_default_sort(0,1);
$sub_query = $sc->get_sql_string();

@$vysledek_z=mysqli_query($db_conn, "SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = mysqli_fetch_array($vysledek_z);

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_,false,true);
?>
<BR>
<? DrawPageSubTitle('Přihlášky'); ?>

<p>
Přihlášení člena - se provede zapsáním kategorie pro příslušného člena.<BR>
Odhlášení člena - se provede vymazáním kategorie (prázné textové pole) pro příslušného člena.<BR>
Změna kategorie - se provede změnou textového pole s kategorií pro příslušného člena.<BR>
<span class="WarningText">Do sloupců, které nechcete měnit nezasahujte !!</span>
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
<p><b>Doplňující informace o závodě (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.$zaznam_z['poznamka'].'</p>');
}

$termin = raceterms::GetCurr4RegTerm($zaznam_z);

if($termin == 0 && !IsLoggedAdmin() && !IsLoggedRegistrator())
{
	echo('Nelze provádět přihlášky, nejspíš už vypršely všechny termíny přihlášek, je po závodě, či není aktivní žádný termín pro přihlášení.');
}
else
{
?>

<FORM METHOD=POST ACTION="./race_regs_all_exc.php?gr_id=<?echo $gr_id;?>&id=<?echo $id;?>" name="form1">
<?

$sub_query2 = (IsLoggedRegistrator() || IsLoggedManager()) ? '' : ' AND '.TBL_USER.'.chief_id = '.$usr->user_id.' OR '.TBL_USER.'.id = '.$usr->user_id;

$query = 'SELECT '.TBL_USER.'.id, prijmeni, jmeno, reg, datum, kat, pozn, pozn_in, termin, entry_locked, '.TBL_ZAVXUS.'.transport, '.TBL_ZAVXUS.'.ubytovani FROM '.TBL_USER.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_USER.'.id = '.TBL_ZAVXUS.'.id_user AND '.TBL_ZAVXUS.'.id_zavod='.$id.' WHERE '.TBL_USER.'.hidden = 0'.$sub_query2.$sub_query;

@$vysledek=mysqli_query($query);

//echo "Počet již přihlášených členů je ".mysqli_num_rows($vysledek_p).".<BR>";

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_show_on = ($zaznam_z['prihlasky'] > 1);
$is_termin_edit_on = $is_registrator_on && $is_termin_show_on;
$is_spol_dopr_on = ($zaznam_z["transport"]==1);
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1);

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Reg.č.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Věk',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
if($is_spol_dopr_on)
	$data_tbl->set_header_col_with_help($col++,'SD',ALIGN_CENTER,'Společná doprava');
if($is_spol_ubyt_on)
	$data_tbl->set_header_col_with_help($col++,'SU',ALIGN_CENTER,'Společné ubytování');
if($is_termin_show_on)
	$data_tbl->set_header_col_with_help($col++,'T.',ALIGN_CENTER,"Číslo termínu přihlášky");
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Poznámka(interní)',ALIGN_CENTER);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$data_tbl->set_sort_col(1,$sc->get_col_content(0));
$data_tbl->set_sort_col(2,$sc->get_col_content(1));
echo $data_tbl->get_sort_row()."\n";

$i=1;
while ($zaznam=mysqli_fetch_array($vysledek))
{
	$row = array();
	$row[] = $i++;
	$row[] = RegNumToStr($zaznam['reg']);
	$row[] = $zaznam['prijmeni'];
	$row[] = $zaznam['jmeno'];
	$age = CountManAge($zaznam['datum']);
	$row[] = ($age != -1) ? (($age < GC_SHOW_AGE_LIMIT)? $age :'') : '?';
	$u=$zaznam['id'];
	$entry_lock = ($zaznam['entry_locked'] != 0) && !$is_registrator_on;
	$trans=$zaznam['transport']?"CHECKED":"";
	$ubyt=$zaznam['ubytovani']?"CHECKED":"";

	if ($zaznam['kat'] != NULL)
	{	// jiz prihlasen
		if($zaznam['termin'] == $termin || $is_termin_edit_on || $zaznam_z['prihlasky'] == 1)
		{	// aktualni termin nebo povelena komplet editace
			$row[] = ($entry_lock) ? $zaznam['kat']:'<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 value="'.$zaznam['kat'].'" onfocus="javascript:select_row('.$u.');">';
			if($is_spol_dopr_on)
				$row[] = '<INPUT TYPE="checkbox" NAME="transport['.$u.']" '.$trans.' onfocus="javascript:select_row('.$u.');">';
			if($is_spol_ubyt_on)
				$row[] = '<INPUT TYPE="checkbox" NAME="ubytovani['.$u.']" '.$ubyt.' onfocus="javascript:select_row('.$u.');">';
			if($is_termin_edit_on)
				$row[] = '<INPUT TYPE="text" NAME="term['.$u.']" SIZE=1 value="'.$zaznam['termin'].'" onfocus="javascript:select_row('.$u.');">';
			else if($is_termin_show_on)
				$row[] = $zaznam['termin'];
			$row[] = ($entry_lock) ? $zaznam['pozn']:'<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn'].'" onfocus="javascript:select_row('.$u.');">';
			$row[] = ($entry_lock) ? $zaznam['pozn_in']:'<INPUT TYPE="text" NAME="pozn2['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn_in'].'" onfocus="javascript:select_row('.$u.');">';
		}
		else
		{
			$row[] = ($entry_lock) ? $zaznam['kat']:'<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 value="'.$zaznam['kat'].'" onfocus="javascript:select_row('.$u.');" disabled readonly>';
			if($is_spol_dopr_on)
				$row[] = '<INPUT TYPE="checkbox" NAME="transport['.$u.']" '.$trans.' onfocus="javascript:select_row('.$u.');" disabled readonly>';
			if($is_spol_ubyt_on)
				$row[] = '<INPUT TYPE="checkbox" NAME="ubytovani['.$u.']"  '.$ubyt.' onfocus="javascript:select_row('.$u.');" disabled readonly>';
			if($is_termin_edit_on)
				$row[] = '<INPUT TYPE="text" NAME="term['.$u.']" SIZE=1 value="'.$zaznam['termin'].'" onfocus="javascript:select_row('.$u.');" disabled readonly>';
			if($is_termin_show_on)
				$row[] = $zaznam['termin'];
			$row[] = ($entry_lock) ? $zaznam['pozn']:'<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn'].'" onfocus="javascript:select_row('.$u.');">';
			$row[] = ($entry_lock) ? $zaznam['pozn_in']:'<INPUT TYPE="text" NAME="pozn2['.$u.']" size="25" maxlength="250" value="'.$zaznam['pozn_in'].'" onfocus="javascript:select_row('.$u.');">';
		}
	}
	else
	{	// neprihlasen
		$row[] = ($entry_lock) ? '-':'<INPUT TYPE="text" NAME="kateg['.$u.']" SIZE=5 onfocus="javascript:select_row('.$u.');">';
		if($is_spol_dopr_on)
			$row[] = '<INPUT TYPE="checkbox" NAME="transport['.$u.']" onfocus="javascript:select_row('.$u.');">';
		if($is_spol_ubyt_on)
			$row[] = '<INPUT TYPE="checkbox" NAME="ubytovani['.$u.']" onfocus="javascript:select_row('.$u.');">';
		if($is_termin_edit_on)
		{
			$row[] = '<INPUT TYPE="text" NAME="term['.$u.']" SIZE=1 value="'.(($termin != 0) ? $termin : $zaznam_z['prihlasky']).'" onfocus="javascript:select_row('.$u.');">';
		}
		else if($is_termin_show_on)
		{
			$row[] = (($termin != 0) ? $termin : $zaznam_z['prihlasky']);
		}
		
		$row[] = ($entry_lock) ? '-':'<INPUT TYPE="text" NAME="pozn['.$u.']" size="25" maxlength="250" onfocus="javascript:select_row('.$u.');">';
		$row[] = ($entry_lock) ? '-':'<INPUT TYPE="text" NAME="pozn2['.$u.']" size="25" maxlength="250" onfocus="javascript:select_row('.$u.');">';
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
	for ($i=0; $i<count($kategorie); $i++)
	{
		if ($kategorie[$i] != '')
			echo "<button onclick=\"javascript:zmen_kat('".$kategorie[$i]."');return false;\">".$kategorie[$i]."</button>";
	}
?>
<BR>
Vyberte závodníka klepnutím do políčka kategorie u závodníka a následně vložte vybranou kategorii pomocí tlačítka s názvem kategorie.<BR>
<BR>
<INPUT TYPE="submit" value='Proveď změny'>
</FORM>
<?
}
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>
<?
HTML_Footer();
?>