<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require_once ("./ctable.inc.php");
require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ("./common_user.inc.php");
require_once ('./url.inc.php');
require_once ('./ct_renderer_race.inc.php');

DrawPageTitle('Přiřazení SI čipů pro závod');

db_Connect();

$id_zav = (isset($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;

//$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.si_chip = 0 AND u.hidden = 0 ORDER BY z.id ASC';
$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.si_chip as t_si_chip FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' AND u.hidden = 0 ORDER BY z.id ASC';
@$vysledek=query_db($query);
// Fetch all rows into array
$zaznamy = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

@$vysledek_z=query_db("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);

$kapacita = $zaznam_z['kapacita'];
DrawPageRaceTitle('Vybraný závod',$kapacita,$num_rows);

RaceInfoTable($zaznam_z,'',false,false,true);
?>

<BR><BR><hr><BR>
<?
DrawPageSubTitle('Přihlášení závodníci bez trvalých SI čipů');

if (mysqli_num_rows($vysledek) > 0)
{
?>
<FORM METHOD="POST" ACTION="race_reg_chip_exc.php?id_zav=<? echo($id_zav); ?>">

<?
	// define table
	$tbl_renderer = RaceRendererFactory::createTable();
	$tbl_renderer->addColumns('id','jmeno','prijmeni','reg');
	$tbl_renderer->addColumns([new DefaultHeaderRenderer('SI čip'),
		new CallbackRenderer ( function ( RowData $row, array $options ) : string {
			if ($row->rec['si_chip'] != 0)
			{
				$si = ($row->rec['t_si_chip'] != 0) ? $row->rec['t_si_chip'] : $row->rec['si_chip'];
				return '<input type="text" name="chip['.$row->rec['id'].']" SIZE=9 MAXLENGTH=9 value="'.$si.'"> ('.$row->rec['si_chip'].')';
			}
			else
				return '<input type="text" name="chip['.$row->rec['id'].']" SIZE=9 MAXLENGTH=9 value="'.$row->rec['t_si_chip'].'">';		
		})]);
	$tbl_renderer->addColumns('kat','pozn','pozn_in');

	if ($g_enable_race_capacity && isSet ($zaznam_z['kapacita']) ) {
		$tbl_renderer->addBreak(new LimitBreakDetector($zaznam_z['kapacita']));
		$tbl_renderer->setRowTextPainter ( new GreyLastNPainter($zaznam_z['kapacita']) );	
	}
	echo $tbl_renderer->render( new html_table_mc(), $zaznamy, [] );

?>
<br>
<INPUT TYPE="submit" value='Zapsat čipy pro závod'>
</FORM>
<?
}
else
{
	echo('Nejsou přihlášení žádní závodníci.<br>');
}
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zavři</BUTTON>

<?
HTML_Footer();
?>