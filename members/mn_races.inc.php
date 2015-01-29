<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Hromadné přihlášky na závody');
?>
<CENTER>
<?
include ("./common_race.inc.php");
include ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC);

@$vysledek=MySQL_Query("SELECT id,datum,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, nazev,oddil,ranking,typ,vicedenni,odkaz,misto,cancelled FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum, datum2, id');

?>

<script language="javascript">
<!-- 
	/* "status=yes,width=600,height=350" */

	function confirm_delete() {
		return confirm('Opravdu se chcete odhlásit?');
	}

	javascript:set_default_size(800,600);
//-->
</script>

<?
$num_rows = ($vysledek != FALSE) ? mysql_num_rows($vysledek) : 0;
if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
	$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ závodu");
	$data_tbl->set_header_col_with_help($col++,'W',ALIGN_CENTER,"Web závodu");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Přihlášky',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	$old_year = 0;
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

		$prefix = ($zaznam['datum'] < GetCurrentDate()) ? '<span class="TextAlertExp">' : '';
		$suffix = ($zaznam['datum'] < GetCurrentDate()) ? '</span>' : '';
		$row = array();
		//----------------------------
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
		$prihlasky=Date2String($prihlasky_curr[0]);
		if($zaznam['prihlasky'] > 1)
			$prihlasky .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
		$prihlasky_out = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky);
		$prihl_finish = (($time_to_reg == -1 && $prihlasky_curr[0] != 0) || $race_is_old);
		//----------------------------
		$row[] = $prefix.$datum.$suffix;
		$row[] = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".$prefix.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).$suffix."</A>";
		$row[] = $prefix.GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']).$suffix;
		$row[] = $prefix.$zaznam['oddil'].$suffix;
		$row[] = GetRaceTypeImg($zaznam['typ']);
		$row[] = GetRaceLinkHTML($zaznam['odkaz']);
		if (!$prihl_finish)
		{
			$row[] = "<A HREF=\"javascript:open_win('./race_regs_1.php?gr_id="._MANAGER_GROUP_ID_."&id=".$zaznam['id']."','')\">Př-1</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_all.php?gr_id="._MANAGER_GROUP_ID_."&id=".$zaznam['id']."','')\">Př-V</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._MANAGER_GROUP_ID_."&id=".$zaznam['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
		}
		else
		{
			$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._MANAGER_GROUP_ID_."&id=".$zaznam['id']."','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
		}
		$row[] = $prihlasky_out;

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
	echo $data_tbl->get_footer()."\n";
}
?>
<p>
Př-1 = přihlašování po jednom členu.<BR>
Př-V = přihlašování všech členů naráz.<BR>
Zbr = zobrazení přihlášených členů.<BR>
</p>
</CENTER>
