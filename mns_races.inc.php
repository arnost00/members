<?php /* zavody - zobrazeni zavodu - Menu malého trenéra */
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

$query = "SELECT id,datum,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, nazev,misto,ranking,typ0,typ,vicedenni,odkaz,oddil,cancelled FROM ".TBL_RACE.$sql_sub_query." ORDER BY datum $order, datum2 $order, id $order";
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

$num_rows = mysqli_num_rows($vysledek);

$zaznamy = [];
while ($zaznam = mysqli_fetch_array($vysledek, MYSQLI_ASSOC)) {
    $zaznamy[] = $zaznam;
}

$curr_date = GetCurrentDate();
$renderer_option['curr_date'] = $curr_date;

if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	// define table
	$tbl_renderer = new RacesRenderedTable();
	$tbl_renderer->addColumns('datum','nazev','misto','oddil','typ0','typ','odkaz');
	// if ($g_enable_race_capacity)
	// 	$tbl_renderer->addColumns('ucast');
	$tbl_renderer->addColumns(['moznosti', new CallbackRenderer ( function ( array $record, array $options ) : string {
		$race_is_old = (GetTimeToRace($record['datum']) == -1);
		$prihlasky_curr = raceterms::GetActiveRegDateArr($record);
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
		$prihl_finish = (($time_to_reg == -1 && $prihlasky_curr[0] != 0) || $race_is_old);

		if (!$prihl_finish)
		{
			return "<A HREF=\"javascript:open_win('./race_regs_1.php?gr_id="._SMALL_MANAGER_GROUP_ID_."&id=".$record['id']."&show_ed=1','')\">Př-1</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_all.php?gr_id="._SMALL_MANAGER_GROUP_ID_."&id=".$record['id']."','')\">Př-V</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._SMALL_MANAGER_GROUP_ID_."&id=".$record['id']."&select=1','')\"><span class=\"TextAlertExpLight\">Zč</span></A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._SMALL_MANAGER_GROUP_ID_."&id=".$record['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
		}
		else
		{
			return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._SMALL_MANAGER_GROUP_ID_."&id=".$record['id']."&select=1','')\"><span class=\"TextAlertExpLight\">Zbr.čl.</span></A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._SMALL_MANAGER_GROUP_ID_."&id=".$record['id']."','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
		}
	})]);
	$tbl_renderer->addColumns('prihlasky');

	$tbl_renderer->setRowTextPainter ( new GreyOldPainter() );

	// TODO: breaks are necessary only by some filters
	$tbl_renderer->addBreak(new YearBreakDetector());
	$tbl_renderer->addBreak(new FutureRaceBreakDetector());

	echo $tbl_renderer->render( new html_table_mc(), $zaznamy, $renderer_option );
}
?>
<p>
Př-1 = přihlašování po jednom členu.<BR>
Př-V = přihlašování všech členů naráz.<BR>
Zč = zobrazení přiřazených přihlášených členů.<BR>
Zbr = zobrazení všech přihlášených členů.<BR>
</p>
</CENTER>
