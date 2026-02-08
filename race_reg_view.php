<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./common_user.inc.php");
require_once ('./url.inc.php');
require_once ('./ct_renderer_race.inc.php');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;
$us = (int)((IsSet($us) && is_numeric($us)) ? (($us > 0) ? 1 : 0) : 0);
$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$select = (int)((IsSet($select) && is_numeric($select)) ? (($select > 0) ? 1 : 0) : 0);

DrawPageTitle('Seznam závodníků přihlášených na závod');

db_Connect();

$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.termin, z.si_chip as t_si_chip, z.id_user, z.transport transport, z.sedadel, z.ubytovani ubytovani FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id.' ORDER BY z.termin ASC, z.id ASC';
@$vysledek=query_db($query);
// Fetch all rows into array
$zaznamy = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

@$vysledek_z=query_db('SELECT * FROM '.TBL_RACE." WHERE `id`='$id' LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);

$kapacita = $zaznam_z['kapacita'];
DrawPageRaceTitle('Vybraný závod',$kapacita,$num_rows);

RaceInfoTable($zaznam_z,'',$gr_id != _REGISTRATOR_GROUP_ID_,false,true);
?>
<TABLE class= "Zav" cellpadding="0" cellspacing="2" border="0">
<BR>
<BUTTON onclick="javascript:close_popup();">Zavři</BUTTON>
<BR><BR><hr><BR>
<?
DrawPageSubTitle('Přihlášení závodníci');

$is_spol_dopr_on = ($zaznam_z["transport"]==1) && $g_enable_race_transport;
$is_sdil_dopr_on = ($zaznam_z["transport"]==3) && $g_enable_race_transport;
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1) && $g_enable_race_accommodation;

// define table
$tbl_renderer = RaceRendererFactory::createTable();
$tbl_renderer->addColumns('id','jmeno','prijmeni');
if ($us == 0) 
	$tbl_renderer->addColumns('reg','si_chip');
$tbl_renderer->addColumns('kat');
if($is_spol_dopr_on||$is_sdil_dopr_on)
	$tbl_renderer->addColumns('transport');
if($is_sdil_dopr_on)
	$tbl_renderer->addColumns('sedadel');
if($is_spol_ubyt_on)
	$tbl_renderer->addColumns('ubytovani');
if($zaznam_z['prihlasky'] > 1)
	$tbl_renderer->addColumns('termin');
if (IsLogged())
	$tbl_renderer->addColumns('pozn','pozn_in');

if ($g_enable_race_capacity && isSet ($zaznam_z['kapacita']) ) {
	$tbl_renderer->addBreak(new LimitBreakDetector($zaznam_z['kapacita']));
	$tbl_renderer->setRowTextPainter ( new GreyLastNPainter($zaznam_z['kapacita']) );	
}

$tbl_renderer->setRowFilter ( function ( RowData $row ) use ( $select, $usr ) : bool  {
	return (($select == 0 || $row->rec['chief_id'] == $usr->user_id || $row->rec['id_user'] == $usr->user_id) && $row->rec['hidden'] == 0);
});

echo $tbl_renderer->render( new html_table_mc(), $zaznamy, [] );

if ($select == 0)
{	// SD pouze pro vypis vsech prihlasek
	$stats = countRaceStats($zaznamy, $is_sdil_dopr_on);
	RenderRaceStats(
		$stats,
		$is_spol_dopr_on,
		$is_sdil_dopr_on,
		$is_spol_ubyt_on
	);
}
?>

<BR>

<?
HTML_Footer();
?>
