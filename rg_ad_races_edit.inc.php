<?php /* adminova stranka - editace zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Kalendáø závodù - Editace závodù', false);
?>
<CENTER>
<script language="javascript">
<!-- 
/*	"menubar=yes,status=yes,width=600,height=600"	*/

	function confirm_delete()
	{
		return confirm('Opravdu chcete smazat tento zavod?');
	}

	javascript:set_default_size(800,600);
//-->
</script>
<?

include ('./common_race.inc.php');
include ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 1;  // old races - default is ON 
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC);

if (!$g_is_release)
{	// pri debug zobrazit
	@$vysledek=MySQL_Query("SELECT id,datum,typ,datum2,odkaz,nazev,vicedenni,kategorie,oddil,misto,modify_flag FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum , datum2, id');
}
else
{
	@$vysledek=MySQL_Query("SELECT id,datum,typ,datum2,odkaz,nazev,vicedenni,kategorie,oddil,misto FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum, datum2, id');
}

ShowRefreshInfo(true);

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'W',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Kat',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
if (!$g_is_release)
{	// pri debug zobrazit
	$data_tbl->set_header_col($col++,'Zmìny',ALIGN_CENTER);
}
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i = 1;
$brk_tbl = false;
$old_year = 0;
if($vysledek && ($num_rows = mysql_num_rows($vysledek)) > 0)
{
	show_link_to_actual_race($num_rows);

	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$row = array();
		
		$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

		$prefix = ($race_is_old) ? '<span class="TextAlertExpLight">' : '';
		$suffix = ($race_is_old) ? '</span>' : '';

		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		$row[] = $prefix.$datum.$suffix;
		$row[] = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".$prefix.$zaznam['nazev'].$suffix."</A>";
		$row[] = $prefix.$zaznam['misto'].$suffix;
		$row[] = $prefix.$zaznam['oddil'].$suffix;
		$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\">".GetRaceTypeImg($zaznam['typ']).'</A>';
		$row[] = GetRaceLinkHTML($zaznam['odkaz']);
		$row[] = (strlen($zaznam['kategorie']) > 0) ? 'A' :'<span class="TextAlertBold">N</span>';
		$row [] = "<A HREF=\"javascript:open_win('./race_edit.php?id=".$zaznam['id']."','')\">Edit</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_kat.php?id=".$zaznam['id']."','')\">Kategorie</A>&nbsp;/&nbsp;<A HREF=\"./race_del_exc.php?id=".$zaznam["id"]."\" onclick=\"return confirm_delete();\" class=\"Erase\">Smazat</A>";
		if (!$g_is_release)
		{	// pri debug zobrazit
			$row[] = GetModifyFlagDesc($zaznam['modify_flag']);
		}
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

echo '<BR><hr><BR>';

echo("<A HREF=\"javascript:open_win('./race_new.php?type=0','')\">Vytvoøit nový závod</A><br>");
echo("<A HREF=\"javascript:open_win('./race_new.php?type=1','')\">Vytvoøit nový vícedenní závod</A><br>");

?>
</CENTER>
