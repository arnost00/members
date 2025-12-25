<?php /* zavody - zobrazeni zavodu - Menu trenéra */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Hromadné přihlášky na závody');
?>
<CENTER>
<?
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');
require_once ('./ct_renderer_races.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD);

//when show all races reverse order
$order = ($fC == 1) ? "desc" : "";

$query = "SELECT id,datum,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, nazev,oddil,ranking,typ0,typ,vicedenni,odkaz,misto,kapacita,prihlasenych,cancelled FROM ".TBL_RACE.$sql_sub_query." ORDER BY datum $order, datum2 $order, id $order";
@$vysledek=query_db($query);

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
// Fetch all rows into array
$zaznamy  = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

$renderer_option['curr_date'] = GetCurrentDate();

if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	// define table
	$tbl_renderer = RacesRendererFactory::createTable();
	$tbl_renderer->addColumns('datum','nazev','misto','oddil','typ0','typ','odkaz');
	if ($g_enable_race_capacity)
	 	$tbl_renderer->addColumns('ucast');
	$tbl_renderer->addColumns(['moznosti', new CallbackRenderer ( function ( RowData $row, array $options ) : string {
		$race_is_old = (GetTimeToRace($row->rec['datum']) == -1);
		$prihlasky_curr = raceterms::GetActiveRegDateArr($row->rec);

		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
		$prihl_finish = (($time_to_reg == -1 && $prihlasky_curr[0] != 0) || $race_is_old);
		$ucast = (GetTimeToRace($row->rec['datum']) <= 0) ? " / <A HREF=\"javascript:open_win('./api_race_entry.view.php?race_id=".$row->rec['id']."','')\">Účast</A>" : '';
		if (!$prihl_finish)
		{
			return "<A HREF=\"javascript:open_win('./race_regs_1.php?gr_id="._MANAGER_GROUP_ID_."&id=".$row->rec['id']."&show_ed=1','')\">Př-1</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_all.php?gr_id="._MANAGER_GROUP_ID_."&id=".$row->rec['id']."','')\">Př-V</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._MANAGER_GROUP_ID_."&id=".$row->rec['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>".$ucast;
		}
		else
		{
			return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._MANAGER_GROUP_ID_."&id=".$row->rec['id']."','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>".$ucast;
		}
 
	})]);
	$tbl_renderer->addColumns('prihlasky');

	$tbl_renderer->setRowTextPainter ( new GreyOldPainter() );

	if ($fC == 1) {
		// old races - add breaks
		$tbl_renderer->addBreak(new YearExpanderDetector());
		$tbl_renderer->setRowAttrsExt ( YearExpanderDetector::yearGroupRowAttrsExtender(...));
	}
	else {
		// TODO: breaks are necessary only by some filters
		$tbl_renderer->addBreak(new YearBreakDetector());
		$tbl_renderer->addBreak(new FutureRaceBreakDetector());
	}

	echo $tbl_renderer->render( new html_table_mc(), $zaznamy, $renderer_option );
}
?>
<p>
Př-1 = přihlašování po jednom členu.<BR>
Př-V = přihlašování všech členů naráz.<BR>
Zbr = zobrazení přihlášených členů.<BR>
</p>
</CENTER>
