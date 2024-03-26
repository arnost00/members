<? /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Přihlášky na závody');
?>
<CENTER>

<?
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD,'r.');

$query = 'SELECT r.id, r.datum, datum2, nazev, typ0, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, '.
		'prihlasky4, prihlasky5, vicedenni, misto, oddil, kat, termin, cancelled, if(vedouci=0, "-", concat(u.jmeno, " ", u.prijmeni)) as vedouci '.
		'FROM '.TBL_RACE.' r LEFT JOIN '.TBL_ZAVXUS.' zu ON r.id = zu.id_zavod AND zu.id_user='.$usr->user_id.' left join '.TBL_USER.' u on u.id = r.vedouci '.
		$sql_sub_query.' ORDER BY r.datum, datum2, r.id';
@$vysledek=query_db($query);

@$vysledek2=query_db("SELECT * FROM ".TBL_USER." where id=$usr->user_id");
$entry_lock = false;
if ($zaznam2=mysqli_fetch_array($vysledek2))
{
	$entry_lock = ($zaznam2['entry_locked'] != 0);
}

?>

<script language="javascript">
	/*	"status=yes,width=600,height=350"	*/

	function confirm_delete() {
		return confirm('Opravdu se chcete odhlasit?');
	}

	javascript:set_default_size(600,600);
</script>

<?
$curr_date = GetCurrentDate();

$num_rows = ($vysledek) ? mysqli_num_rows($vysledek) : 0;
if ($num_rows > 0)
{
	if ($entry_lock)
	{
		echo('<span class="WarningText">Máte zamknutou možnost se přihlašovat.</span>'."<br><br>\n");
	}

	show_link_to_actual_race($num_rows);

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
	$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ akce");
	$data_tbl->set_header_col_with_help($col++,'S',ALIGN_CENTER,"Sport");
	$data_tbl->set_header_col_with_help($col++,'W',ALIGN_CENTER,"Web závodu");
	$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Přihlášky',ALIGN_CENTER);
	if($g_enable_race_boss)
		$data_tbl->set_header_col($col++,'Vedoucí',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	$old_year = 0;
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$row = array();
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);
		$row[] = $datum;
		$row[] = '<A href="javascript:open_race_info('.$zaznam['id'].')" class="adr_name">'.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).'</A>';
		$row[] = GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']);
		$row[] = $zaznam['oddil'];
		$row[] = GetRaceType0($zaznam['typ0']);
		$row[] = GetRaceTypeImg($zaznam['typ']);
		$row[] = GetRaceLinkHTML($zaznam['odkaz']);
		
		$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
		$prihlasky_out_term = Date2String($prihlasky_curr[0]);
		if($zaznam['prihlasky'] > 1)
			$prihlasky_out_term .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
		$termin = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);

		$prihl_finish = ($time_to_reg == -1 && $prihlasky_curr[0] != 0) || ($prihlasky_curr[0] == 0 && $zaznam['datum'] <= $curr_date);
		$zbr = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";

		if($zaznam['kat'] == NULL)
		{	// neni prihlasen
			if (!$prihl_finish && !$entry_lock)
			{
				$row[] = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam["id"]."&id_us=".$usr->user_id."','')\">Přihl.</A> / ".$zbr;
			}
			else
			{
				$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_."&id=".$zaznam["id"]."&us=1','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
			}
		}
		else
		{	// je prihlasen
			$prihl_finish2 = $prihl_finish || ( $prihlasky_curr[0] != 0 && $prihlasky_curr[1] != $zaznam['termin']);
			if($prihl_finish2 != $prihl_finish)
			{
				$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$zaznam['id']."&us=1','')\"><span class=\"Highlight\">".$zaznam['kat'].'</span></A> / '.$zaznam['termin'];
			}
			else if (!$prihl_finish && !$entry_lock)
			{
				$row[] = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam['id']."&id_us=".$usr->user_id."','')\" class=\"Highlight\">".$zaznam['kat']."</A> / <A HREF=\"javascript:open_win('./us_race_regoff_exc.php?id_zav=".$zaznam['id']."&id_us=".$usr->user_id."','')\" onclick=\"return confirm_delete();\" class=\"Erase\">Od.</A>";
			}
			else
			{
				$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$zaznam['id']."&us=1','')\"><span class=\"Highlight\">".$zaznam['kat'].'</span></A>';
			}
		}

		$row[] = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);

		if($g_enable_race_boss)
		{
			$link_to_participation = "/<A HREF=\"javascript:open_win('./api_race_entry.view.php?race_id=".$zaznam['id']."','')\">Účast</A>";

			$boss = '-';
			if($zaznam['vedouci'] != 0)
			{
				@$vysledekU=query_db("SELECT jmeno,prijmeni FROM ".TBL_USER." WHERE id = '".$zaznam['vedouci']."' LIMIT 1");
				@$zaznamU=mysqli_fetch_array($vysledekU);
				if($zaznamU != FALSE)
					$boss = $zaznamU['jmeno'].' '.$zaznamU['prijmeni'].($zaznam['vedouci'] == $usr->user_id ? $link_to_participation : '');
			}
			$row[] = $boss;
		}
		
		if (!$brk_tbl && $zaznam['datum'] >= $curr_date)
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
		$old_year = Date2Year($zaznam['datum']);
		$i++;
	}
	echo $data_tbl->get_footer()."\n";
}
echo('<a href="race_reg_form_all.php" target="_blank">Vytvoření a export přihlášky pro prázdný závod</a><br>');
?>
<br>
Informace o závodu lze zobrazit kliknutím na název daného závodu.<br>
<?
if ($g_custom_entry_list_text != '')
{
	echo('<br><div style="border-top:1px solid '.$g_colors['body_hr_line'].'; padding:10px;">'.$g_custom_entry_list_text.'</div><br>');
}
?>
</CENTER>
