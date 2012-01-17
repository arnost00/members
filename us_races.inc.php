<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Pøihlášky na závody');
?>
<CENTER>

<?
include ("./common_race.inc.php");
include ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB);

$query = 'SELECT '.TBL_RACE.'.id, datum, datum2, nazev, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, vicedenni, misto, oddil, kat, termin FROM '.TBL_RACE.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_RACE.'.id = '.TBL_ZAVXUS.'.id_zavod AND '.TBL_ZAVXUS.'.id_user='.$usr->user_id.$sql_sub_query.' ORDER BY datum';

@$vysledek=MySQL_Query($query);

?>

<script language="javascript">
<!-- 
	/*	"status=yes,width=600,height=350"	*/

	function confirm_delete() {
		return confirm('Opravdu se chcete odhlasit?');
	}

	javascript:set_default_size(600,600);
//-->
</script>

<?
ShowRefreshInfo();

$num_rows = mysql_num_rows($vysledek);
if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název'.AddPointerImg(),ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Poø.',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'W',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Termín p.',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$row = array();
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);
		$row[] = $datum;
		$row[] = '<A href="javascript:open_race_info('.$zaznam['id'].')" class="adr_name">'.$zaznam['nazev'].'</A>';
		$row[] = $zaznam['misto'];
		$row[] = $zaznam['oddil'];
		$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\">".GetRaceTypeImg($zaznam['typ']).'</A>';
		$row[] = GetRaceLinkHTML($zaznam['odkaz']);
		
		$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
		$prihlasky_out_term = Date2String($prihlasky_curr[0]);
		if($zaznam['prihlasky'] > 1)
			$prihlasky_out_term .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
		$termin = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);

		$prihl_finish = ($time_to_reg == -1 && $prihlasky_curr[0] != 0) || ($prihlasky_curr[0] == 0 && $zaznam['datum'] <= GetCurrentDate());

		if($zaznam['kat'] == NULL)
		{	// neni prihlasen
			if (!$prihl_finish)
			{
				$row[] = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam["id"]."&id_us=".$usr->user_id."','')\">Pøihl.</A>";
			}
			else
			{
				$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_."&id=".$zaznam["id"]."&us=1','')\"><span class=\"TextAlertExp\">Zobrazit</span></A>";
			}
		}
		else
		{	// je prihlasen
			$prihl_finish2 = $prihl_finish || ( $prihlasky_curr[0] != 0 && $prihlasky_curr[1] != $zaznam['termin']);
			if($prihl_finish2 != $prihl_finish)
			{
				$row[] = "ERROR";
				exit;
			}
			if (!$prihl_finish)
			{
				$row[] = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam['id']."&id_us=".$usr->user_id."','')\" class=\"Highlight\">".$zaznam['kat']."</A> / <A HREF=\"javascript:open_win('./us_race_regoff_exc.php?id_zav=".$zaznam['id']."&id_us=".$usr->user_id."','')\" onclick=\"return confirm_delete();\" class=\"Erase\">Od.</A>";
			}
			else
			{
				$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$zaznam['id']."&us=1','')\"><span class=\"Highlight\">".$zaznam['kat'].'</span></A>';
			}
		}

		$row[] = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);

		if (!$brk_tbl && $zaznam['datum'] >= GetCurrentDate())
		{
			if($i != 1)
				echo $data_tbl->get_break_row()."\n";
			$brk_tbl = true;
		}

		echo $data_tbl->get_new_row_arr($row)."\n";
		$i++;
	}
	echo $data_tbl->get_footer()."\n";
}
?>
<br>
Informace o závodu lze zobrazit kliknutím na název daného závodu.<br>
</CENTER>
