<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Přehled závodů pro finance');
?>
<CENTER>
<?
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 1;  // old races
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD);

@$vysledek=mysqli_query($db_conn,"SELECT id,datum,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5,nazev,oddil,ranking,typ0,typ,vicedenni,odkaz,misto,cancelled FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum, datum2, id');

@$result_amount=mysqli_query($db_conn,"select id_zavod, sum(amount) amount from ".TBL_FINANCE." where storno is null group by id_zavod;");
while ($rec=mysqli_fetch_array($result_amount)) $race_amount[$rec["id_zavod"]]=$rec["amount"];
// print_r($race_amount);
?>

<script language="javascript">
<!-- 
	javascript:set_default_size(1000,800);
//-->
</script>

<?
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
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Platba',ALIGN_CENTER);
	
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	$old_year = 0;
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$prefix = ($zaznam['datum'] < GetCurrentDate()) ? '<span class="TextAlertExp">' : '';
		$suffix = ($zaznam['datum'] < GetCurrentDate()) ? '</span>' : '';
		$row = array();
		//----------------------------
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);
		//----------------------------
		$row[] = $prefix.$datum.$suffix;
		$row[] = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".$prefix.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).$suffix."</A>";
		$row[] = $prefix.GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']).$suffix;
		$row[] = $prefix.$zaznam['oddil'].$suffix;
		$row[] = GetRaceType0($zaznam['typ0']);
		$row[] = GetRaceTypeImg($zaznam['typ']).'</A>';
		$row[] = '<A HREF="javascript:open_win(\'./race_finance_view.php?race_id='.$zaznam['id'].'\',\'\')">Přehled</A>';
		$row[] = isset($race_amount[$zaznam['id']])?$race_amount[$zaznam['id']]:"";
		
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

</CENTER>
