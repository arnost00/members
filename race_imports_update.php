<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST, EXTR_SKIP);

require_once ("connect.inc.php");
require_once ("sess.inc.php");
require_once("cfg/_globals.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("ctable.inc.php");
require_once ("./connectors.php");
require_once ("./common_race.inc.php");

$connector = ConnectorFactory::create();

if ($connector === null )
{
	echo('Chyba v nastavení, nenalezen žádny connector, kontaktuje administrátora.<br>');
	exit;
}

db_Connect();

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ('./url.inc.php');
require_once ("./ct_renderer_races.inc.php");

DrawPageTitle('Aktualizace závodů ze systému '.$connector->getSystemName());
DrawPageSubTitle('Budoucí závody uložené v přihláškovém systému');

$curr_date = GetCurrentDate();
$query = "SELECT id, datum, datum2, nazev, oddil, typ0, typ, vicedenni, odkaz, misto, cancelled, kapacita, kategorie, ext_id, prihlasenych FROM ".TBL_RACE
	." WHERE ((datum >= '".$curr_date."') OR (datum2 >= '".$curr_date."'))"
	." ORDER BY datum, datum2, id";
@$vysledek = query_db($query);
$zavody = ($vysledek !== FALSE) ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
?>
<script language="javascript">
<!--
	javascript:set_default_size(800,600);

	function toggleAllFutureRaces(source)
	{
		var checkboxes = document.querySelectorAll('.js-race-selector');
		for (var i = 0; i < checkboxes.length; i++) {
			if (!checkboxes[i].disabled) {
				checkboxes[i].checked = source.checked;
			}
		}
	}
//-->
</script>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?
if (empty($zavody)) {
	echo('<BR />Žádné budoucí závody nebyly nalezeny.<BR /><BR />');
}
else {
?>
<FORM METHOD="POST" ACTION="./race_imports_update_exc.php">
<?

	// define table
	$tbl_renderer = RacesRendererFactory::createTable();
	$tbl_renderer->addColumns([new DefaultHeaderRenderer('<input type="checkbox" checked onclick="toggleAllFutureRaces(this)">',ALIGN_CENTER),
	 	new CallbackRenderer ( function ( RowData $row, array $options ) : string {
		$has_ext_id = !empty($row->rec['ext_id']);
		$checkbox_attrs = $has_ext_id ? ' checked' : ' disabled';
		return '<input type="checkbox" class="js-race-selector" name="race_ids[]" value="'.$row->rec['id'].'"'.$checkbox_attrs.'>';
	 })]);

	$tbl_renderer->addColumns('datum','nazev','misto','oddil');
	$tbl_renderer->addColumns('ext_id');
	$tbl_renderer->addColumns('typ0','typ','odkaz', ['kategorie', new FormatFieldRenderer ('kategorie', function ($kategorie) {
			return (strlen($kategorie) > 0) ? 'A' :'<span class="TextAlertBold">N</span>';	
		})]);
	if ($g_enable_race_capacity)
		$tbl_renderer->addColumns('ucast');

	echo $tbl_renderer->render( new html_table_mc(), $zavody, [] );
?>
<BR>
<INPUT TYPE="submit" VALUE="Aktualizovat">
</FORM>
<?
}
?>
<BR><hr><BR>
<A HREF="index.php?id=<? echo _REGISTRATOR_GROUP_ID_;?>&subid=4">Zpět</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
 <?require_once ("footer.inc.php");?>
<!-- Footer End -->
</TD></TR>
</TABLE>

<?
HTML_Footer();
?>
