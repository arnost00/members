<?php /* adminova stranka - editace zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
require_once ("./connectors.php");
$connector = ConnectorFactory::create();

DrawPageTitle('Kalendář závodů - Editace závodů');
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

    // Function to toggle the button state based on extID field
    function toggleButtonState() {
        if (extID.value.trim() === "") {
            loadRaceByIdButton.disabled = true; // Disable button if extID is empty
        } else {
            loadRaceByIdButton.disabled = false; // Enable button if extID has value
        }
    }

</script>
<?

require_once ('./common_race.inc.php');
require_once ('./url.inc.php');
require_once ('./ct_renderer_races.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races - default is OFF
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD);

if (!$g_is_release)
{	// pri debug zobrazit
	@$vysledek=query_db("SELECT id,datum,typ,typ0,datum2,odkaz,nazev,vicedenni,kategorie,oddil,misto,modify_flag,cancelled,ext_id FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum , datum2, id');
}
else
{
	@$vysledek=query_db("SELECT id,datum,typ,typ0,datum2,odkaz,nazev,vicedenni,kategorie,oddil,misto,cancelled,ext_id FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum, datum2, id');
}

$ext_id_active_oris = ($g_external_is_connector === 'OrisCZConnector');

// Fetch all rows into array
$zaznamy = [];
while ($zaznam = mysqli_fetch_array($vysledek, MYSQLI_ASSOC)) {
	$zaznamy[] = $zaznam;
}

$renderer_option['curr_date'] = GetCurrentDate();

// define table
$tbl_renderer = RacesRendererFactory::createTable();
$tbl_renderer->addColumns('datum','nazev','misto','oddil');
if ($ext_id_active_oris)
	$tbl_renderer->addColumns('ext_id');
$tbl_renderer->addColumns('typ0','typ','odkaz', ['kategorie', new FormatFieldRenderer ('kategorie', function ($kategorie) {
		return (strlen($kategorie) > 0) ? 'A' :'<span class="TextAlertBold">N</span>';	
	})]);
// // if ($g_enable_race_capacity)
// 	$tbl_renderer->addColumns('ucast');
$tbl_renderer->addColumns(['moznosti', new FormatFieldRenderer ( 'id', function ( $id ) : string {
	return "<A HREF=\"javascript:open_win('./race_edit.php?id=".$id."','')\">Edit</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_kat.php?id=".$id."','')\">Kategorie</A>&nbsp;/&nbsp;<A HREF=\"./race_del_exc.php?id=".$id."\" onclick=\"return confirm_delete();\" class=\"Erase\">Smazat</A>";
	})]);
if (!$g_is_release)
{	// pri debug zobrazit
	$tbl_renderer->addColumns([
		new DefaultHeaderRenderer ( 'Změny',ALIGN_CENTER ),
		new FormatFieldRenderer ( 'modify_flag', 'GetModifyFlagDesc' )
	]);
}

$tbl_renderer->setRowTextPainter ( new GreyOldPainter() );

$tbl_renderer->addBreak(new YearBreakDetector());
$tbl_renderer->addBreak(new FutureRaceBreakDetector());

echo $tbl_renderer->render( new html_table_mc(), $zaznamy, $renderer_option );

echo '<BR /><hr><BR />';
DrawPageSubTitleCenter('Vytváření nových závodů');

echo("<A HREF=\"javascript:open_win('./race_new.php?type=0','')\">Vytvořit nový závod</A><br>");
echo("<A HREF=\"javascript:open_win('./race_new.php?type=1','')\">Vytvořit nový vícedenní závod</A>");
echo("<BR /><BR />\n");

if ( $connector !== null ) {

	DrawPageSubTitleCenter('Import závodu ze systému '.$connector->getSystemName());

	echo("Načtení seznamu závodů ze zdroje " .  $connector->getSystemName() . ' od ');
	// Get the current date
	$today = new DateTime();
	echo("<input type='date' id='dateFrom' value='" . $today->format('Y-m-d') . "'> do <input type='date' id='dateTo' value='" . $today->modify("+3 months")->format('Y-m-d') . "'>");
	echo(' <button id="loadRacesButton" onclick="javascript:open_url(\'./race_imports.php?from=\'+dateFrom.value+\'&to=\'+dateTo.value)">Zobrazit</button><br>');
	echo('<BR />');
	echo('Rychlé načtení závodu ze zdroje ' .  $connector->getSystemName() . ' ');
	echo("<input type='text' id='extID' onKeyup='toggleButtonState()' placeholder='ID závodu'>");
	echo(' <button id="loadRaceByIdButton" disabled onclick="javascript:open_win(\'./race_new.php?ext_id=\'+extID.value, \'\')">Načíst</button>');
	echo("<BR /><BR />\n");
}

DrawPageSubTitleCenter('Ostatní editace');
echo("<A HREF=\"categ_predef.php\">Editovat předdefinované seznamy kategorií</A>");
echo("<BR /><BR />\n");

?>
<BR/>
</CENTER>
