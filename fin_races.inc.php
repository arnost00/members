<? /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Přehled závodů pro finance');
?>
<CENTER>
<?
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');
require_once ('./ct_renderer_races.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 1;  // old races
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD);

$sort_order = ($g_finances_race_list_sort_old) ? "asc" : "desc";

$query = "SELECT id,datum,datum2,prihlasky,prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5,nazev,oddil,ranking,typ0,typ,vicedenni,odkaz,misto,cancelled FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum '.$sort_order.', datum2 '.$sort_order.', id '.$sort_order;
@$vysledek=query_db($query);

$query = "select id_zavod, sum(amount) amount from ".TBL_FINANCE." where storno is null group by id_zavod;";
@$result_amount=query_db($query);
while ($rec=mysqli_fetch_array($result_amount)) $race_amount[$rec["id_zavod"]]=$rec["amount"];
// print_r($race_amount);
?>

<script language="javascript">
	javascript:set_default_size(1000,800);
</script>

<?

// Fetch all rows into array
$zaznamy = [];
while ($zaznam = mysqli_fetch_array($vysledek, MYSQLI_ASSOC)) {
    $zaznamy[] = $zaznam;
}

$num_rows = mysqli_num_rows($vysledek);
if ($num_rows > 0)
{
	// Break between years
	class YearExpanderDetector implements IBreakRowDetector {
		public function needsBreak(array $prev, array $curr): bool {
			return Date2Year($prev['datum']) !== Date2Year($curr['datum']);
		}

		public function renderBreak(html_table_mc $tbl, array $record): string {
			$year = Date2Year($record['datum']);
			$odkaz = "<button onclick='toggle_display_by_group(\"$year\")'>Histore závodů pro rok $year</button>";
			return $tbl->get_info_row($odkaz)."\n";
		}
	}

	show_link_to_actual_race($num_rows);

	$curr_date = GetCurrentDate();
	$renderer_option['curr_date'] = $curr_date;

	// define table
	$tbl_renderer = RacesRendererFactory::createTable();
	$tbl_renderer->addColumns('datum','nazev','misto','oddil','typ0','typ');
	$tbl_renderer->addColumns(['moznosti',new FormatFieldRenderer ( 'id', function ( $id ) : string {
		return '<A HREF="javascript:open_win(\'./race_finance_view.php?race_id='.$id.'\',\'\')">Přehled</A>';
	})]);
	$tbl_renderer->addColumns([new DefaultHeaderRenderer('Platba',ALIGN_CENTER),
		new FormatFieldRenderer ( 'id', function ( $id ) use ($race_amount) : string {
        	return isset($race_amount[$id]) ? $race_amount[$id] : '';	
	})]);

	$tbl_renderer->setRowTextPainter ( new GreyOldPainter() );
	$tbl_renderer->setRowAttrsExt ( function ( array $record ) : array  {
		$year = Date2Year($record['datum']);
		$attrs = [ 'data-group' => $year ];
		if ($year < date("Y"))
		  $attrs['style'] = "display:none";
		return $attrs;
	});

	// TODO: breaks are necessary only by some filters
	$tbl_renderer->addBreak(new YearExpanderDetector());
	$tbl_renderer->addBreak(new FutureRaceBreakDetector());

	echo $tbl_renderer->render( new html_table_mc(), $zaznamy, $renderer_option );
}
?>

</CENTER>
