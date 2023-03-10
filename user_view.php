<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once("./cfg/_globals.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once ("./ctable.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

DrawPageTitle('Přehled přihlášek na závody');
?>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<?

// id je z tabulky "users"
@$vysledekU=query_db("SELECT id,prijmeni,jmeno FROM ".TBL_USER." WHERE id=".$id." LIMIT 1");
$zaznamU=mysqli_fetch_array($vysledekU);

DrawPageSubTitle('Vybraný člen : '.$zaznamU["jmeno"].' '.$zaznamU["prijmeni"]);
?>
<CENTER>

<?
require_once ("./common_race.inc.php");

// show all races
//$query = 'SELECT r.id, datum, datum2, nazev, oddil, typ, vicedenni, misto, kat FROM '.TBL_RACE.' as r LEFT JOIN '.TBL_ZAVXUS.' as z ON r.id = z.id_zavod AND z.id_user='.$id.' ORDER BY r.datum, r.datum2, r.id';

// show only races with registration
$query = 'SELECT r.id, datum, datum2, nazev, oddil, typ0, typ, vicedenni, misto, kat FROM '.TBL_RACE.' as r JOIN '.TBL_ZAVXUS.' as z ON r.id = z.id_zavod AND z.id_user='.$id.' ORDER BY r.datum, r.datum2, r.id';

@$vysledek=query_db($query);

$num_rows = mysqli_num_rows($vysledek);
if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
	$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ akce");
	$data_tbl->set_header_col_with_help($col++,'S',ALIGN_CENTER,"Sport");
	$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	$old_year = 0;
	if($vysledek != FALSE)
	{
		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			$row = array();
			$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

			$prefix = ($race_is_old) ? '<span class="TextAlertExpLight">' : '';
			$suffix = ($race_is_old) ? '</span>' : '';

			//----------------------------
			if($zaznam['vicedenni'])
				$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
			else
				$datum=Date2String($zaznam['datum']);
			//----------------------------
			$row[] = $prefix.$datum.$suffix;
			$row[] = $prefix.$zaznam['nazev'].$suffix;
			$row[] = $prefix.$zaznam['misto'].$suffix;
			$row[] = $prefix.$zaznam['oddil'].$suffix;
			$row[] = GetRaceType0($zaznam['typ0']);
			$row[] = GetRaceTypeImg($zaznam['typ']);
			if($zaznam['kat'] != NULL)
				$row[] = '<span class="Highlight">'.$zaznam['kat'].'</span>';
			else
				$row[] = '';
			if (!$brk_tbl && $zaznam['datum'] >= GetCurrentDate())
			{
				if($i != 1)
					echo $data_tbl->get_break_row()."\n";
				$brk_tbl = true;
			}
			else if($i != 1 && Date2Year($zaznam['datum']) != $old_year)
			{
					echo $data_tbl->get_break_row(true)."\n";
			}

			echo $data_tbl->get_new_row_arr($row)."\n";
			$i++;
			$old_year = Date2Year($zaznam['datum']);
		}
	}
	echo $data_tbl->get_footer()."\n";
}
else
{
	echo('Vybraný člen není nikam přihlášen.<br>');
}
?>

<BR>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>
<BR>
<BR>

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

<?
HTML_Footer();
?>