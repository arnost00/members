<? /* zavody - zobrazeni zavodu - Členské menu*/
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Přihlášky na závody');
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
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD,'r.');

//when show all races reverse order
$order = ($fC == 1) ? "desc" : "";

$query = 'SELECT r.id, r.datum, datum2, nazev, typ0, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, '.
		'prihlasky4, prihlasky5, vicedenni, misto, oddil,  kapacita, prihlasenych, kat, termin, cancelled, r.vedouci, if(vedouci=0, "-", concat(u.jmeno, " ", u.prijmeni)) as vedouci_jmeno '.
		'FROM '.TBL_RACE.' r LEFT JOIN '.TBL_ZAVXUS.' zu ON r.id = zu.id_zavod AND zu.id_user='.$usr->user_id.' left join '.TBL_USER.' u on u.id = r.vedouci '.
		$sql_sub_query." ORDER BY r.datum $order, datum2 $order, r.id $order";

@$vysledek=query_db($query);

// Fetch all rows into array
$zaznamy  = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

@$vysledek2=query_db("SELECT * FROM ".TBL_USER." where id=$usr->user_id");
$entry_lock = false;
if ($zaznam2=mysqli_fetch_array($vysledek2))
{
	$entry_lock = ($zaznam2['entry_locked'] != 0);
}
$renderer_option['entry_lock'] = $entry_lock;

?>

<script language="javascript">
	/*	"status=yes,width=600,height=350"	*/

	function confirm_delete() {
		return confirm('Opravdu se chcete odhlásit?');
	}

	javascript:set_default_size(600,600);
</script>

<?

$renderer_option['curr_date'] = GetCurrentDate();

if ($num_rows > 0)
{
	if ($entry_lock)
	{
		echo('<span class="WarningText">Máte zamknutou možnost se přihlašovat.</span>'."<br><br>\n");
	}

	show_link_to_actual_race($num_rows);

	// define table
	$tbl_renderer = RacesRendererFactory::createTable();
	$tbl_renderer->addColumns('datum','nazev','misto','oddil','typ0','typ','odkaz');
	if ($g_enable_race_capacity)
		$tbl_renderer->addColumns('ucast');
	$tbl_renderer->addColumns('moznosti','prihlasky');
	if($g_enable_race_boss)
		$tbl_renderer->addColumns('vedouci');
	if ($fC == 1) {
		// old races - add breaks
		$tbl_renderer->addBreak(new YearExpanderDetector());
		$tbl_renderer->setRowAttrsExt ( YearExpanderDetector::yearGroupRowAttrsExtender(...));
	}
	else {
		$tbl_renderer->addBreak(new YearBreakDetector());
		$tbl_renderer->addBreak(new FutureRaceBreakDetector());
	}

	echo $tbl_renderer->render( new html_table_mc(), $zaznamy, $renderer_option );
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
