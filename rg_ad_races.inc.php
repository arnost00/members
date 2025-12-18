<?php /* adminova stranka - editace zavodu - Menu přihlašovatele*/
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Kalendář závodů - Přihlášky na závody');
?>
<CENTER>
<script language="javascript">
<!-- 
/*	"menubar=yes,status=yes,width=600,height=600"	*/

	javascript:set_default_size(800,600);
//-->
</script>
<?
require_once ('./common_race.inc.php');
require_once ('./url.inc.php');
require_once ('./ct_renderer_races.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races
$fD = (IsSet($fD) && is_numeric($fD)) ? (int)$fD : 0;  // type 0
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC,$fD);

//when show all races reverse order
$order = ($fC == 1) ? "desc" : "";

@$vysledek=query_db("SELECT id, datum, typ0, typ, datum2, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, nazev, vicedenni, odkaz, vedouci, oddil, kapacita, send, misto, cancelled, ext_id FROM ".TBL_RACE.$sql_sub_query." ORDER BY datum $order, datum2 $order, id $order");

$ext_id_active_oris = ($g_external_is_connector === 'OrisCZConnector');

$renderer_option['curr_date'] = GetCurrentDate();

// Fetch all rows into array
$zaznamy  = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

if ($g_enable_race_capacity)
	$renderer_option['count_registered'] = GetCountRegistered ($zaznamy);

if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	// define table
	$tbl_renderer = RacesRendererFactory::createTable();
	$tbl_renderer->addColumns('datum','nazev','misto','oddil');
	if ($ext_id_active_oris)
		$tbl_renderer->addColumns('ext_id');
	$tbl_renderer->addColumns('typ0','typ','odkaz');
	if ($g_enable_race_capacity)
	 	$tbl_renderer->addColumns('ucast');
	$tbl_renderer->addColumns(['moznosti', new CallbackRenderer ( function ( RowData $row, array $options ) : string {
			$race_is_old = (GetTimeToRace($row->rec['datum']) == -1);
			$ucast = " / <A HREF=\"javascript:open_win('./api_race_entry.view.php?race_id=".$row->rec['id']."','')\">Účast</A>";
			if(!$race_is_old || IsLoggedAdmin())
			{
				$s1 = "<A HREF=\"javascript:open_win2('./race_reg_form.php?id_zav=".$row->rec['id']."','')\">Vý.</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win2('./race_reg_chip.php?id_zav=".$row->rec['id']."','')\">SI</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_1.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$row->rec['id']."&show_ed=1','')\">P.1</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_all.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$row->rec['id']."','')\">P.V</A>&nbsp;/&nbsp;";
				$s2 = "<A HREF=\"javascript:open_win_ex('./race_reg_view.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$row->rec['id']."','',600,600)\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
				$s3 = (GetTimeToRace($row->rec['datum']) <= 0) ? $ucast :'';
				return $s1.$s2.$s3;
			}
			else
			{
				return "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$row->rec['id']."','',600,600)\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>".$ucast;
			}
		})]);
	$tbl_renderer->addColumns(['prihlasky', new CallbackRenderer ( function ( RowData $row, array $options ) : string {
			$race_is_old = (GetTimeToRace($row->rec['datum']) == -1);
			$prihlasky_curr = raceterms::GetActiveRegDateArr($row->rec);
			$prihlasky=Date2String($prihlasky_curr[0]);
			if($row->rec['prihlasky'] > 1)
				$prihlasky .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];

			if ($race_is_old)
				$prihlasky_out = '<span class="TextAlertExpLight">'.$prihlasky.'</span>';
			else if ($prihlasky_curr != 0 && GetTimeToReg($prihlasky_curr[0]) == -1)
				$prihlasky_out = '<span class="TextAlert">'.$prihlasky.'</span>';
			else
				$prihlasky_out = $prihlasky;

			if($row->rec['prihlasky'] > 1 && !$race_is_old)
			{	// insert before - previous term.
				$prihlasky_prev = raceterms::GetActiveRegDateArrPrev($row->rec);

				if ($prihlasky_prev[0] != 0)
					$prihlasky_out = '<span class="TextAlert">'.Date2String($prihlasky_prev[0]).'&nbsp;/&nbsp;'.$prihlasky_prev[1].'</span><br>'.$prihlasky_out;
			}		
			return $prihlasky_out;
		} )]);
	if($g_enable_race_boss)
		$tbl_renderer->addColumns(['vedouci', new CallbackRenderer ( function ( RowData $row, array $options ) : string {
			return (($row->rec['vedouci'] != 0) ? 'A&nbsp;/&nbsp;': '')."<A HREF=\"javascript:open_win('./race_boss.php?id=".$row->rec['id']."','')\">Edit</A>";
		})]);
	$tbl_renderer->addColumns([
		new HelpHeaderRenderer ( 'OP',ALIGN_CENTER,"Stav odeslání přihlášky" ),
		new CallbackRenderer ( function ( RowData $row, array $options ) : string {
			if($row->rec['send'] > 0)
				return ($row->rec['prihlasky'] > 1) ? $row->rec['send'].'.t.' : 'Ano';
			else
				return 'Ne';
		} )
	]);

	$tbl_renderer->setRowTextPainter ( new GreyOldPainter() );

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
//obsolete - echo('<a href="race_reg_form_exc.php" target="_blank">Výpis všech členů pro centrální registraci</a><br>');
echo('<a href="race_reg_form_all.php" target="_blank">Vytvoření a export přihlášky pro prázdný závod</a><br>');
?>
<BR><hr><BR>
<p>
Vý. = Export přihlášky ve formátu ČSOB.<BR>
SI = Editace (Doplnění) SI čipů pro vybraný závod.<BR>
P.1 = přihlašování po jednom členu.<BR>
P.V = přihlašování všech členů naráz.<BR>
Zbr = zobrazení přihlášených členů.<BR>
OP = Odeslána přihláška.<BR>
</p>
</CENTER>
