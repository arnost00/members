<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
@extract($_REQUEST);

require_once('./cfg/_colors.php');
require_once('./connect.inc.php');
require_once('./sess.inc.php');

if (!IsLogged())
{
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}
require_once('./ctable.inc.php');
require_once('./header.inc.php'); // header obsahuje uvod html a konci <BODY>
require_once('./common.inc.php');
require_once('./common_race.inc.php');
require_once('./url.inc.php');
require_once("./xss_prevent.php");
require_once('./ct_renderer_race.inc.php');

db_Connect();


$id_zav = (IsSet($id_zav) && is_numeric($id_zav)) ? (int)$id_zav : 0;
$id_us = (IsSet($id_us) && is_numeric($id_us)) ? (int)$id_us : 0;

DrawPageTitle('Přihláška na závod');

$query = 'SELECT u.*, z.kat, z.pozn, z.pozn_in, z.termin, z.id_user, z.transport, z.sedadel, z.ubytovani FROM '.TBL_ZAVXUS.' as z, '.TBL_USER.' as u WHERE z.id_user = u.id AND z.id_zavod='.$id_zav.' ORDER BY z.id ASC';
@$vysledek=query_db($query);
// Fetch all rows into array
$zaznamy = $vysledek ? mysqli_fetch_all($vysledek, MYSQLI_ASSOC) : [];
$num_rows = count ($zaznamy);

@$vysledek_z=query_db("SELECT * FROM ".TBL_RACE." WHERE id=$id_zav");
$zaznam_z = mysqli_fetch_array($vysledek_z);

@$vysledek_rg=query_db("SELECT * FROM ".TBL_ZAVXUS." WHERE id_zavod=$id_zav and id_user=$id_us");
$zaznam_rg=mysqli_fetch_array($vysledek_rg);

@$vysledek_u=query_db("SELECT * FROM ".TBL_USER." WHERE id=$id_us");
$zaznam_u = mysqli_fetch_array($vysledek_u);

$new = ($zaznam_rg && $zaznam_rg['kat'] != '') ? 0 : 1;

?>

<SCRIPT LANGUAGE="JavaScript">
function zmen_kat(kateg)
{
	document.form1.kat.value=kateg;
}

function check_reg(vstup)
{
	if (vstup.kat.value == "")
	{
		alert("Musíš zadat kategorii pro přihlášení do závodu.");
		return false;
	}
	else
		return true;
}

function submit_off()
{
	if (confirm('Opravdu se chcete odhlásit?'))
	{
		window.location = 'us_race_regoff_exc.php?id_zav=<? echo($id_zav) ?>&id_us=<? echo($id_us) ?>';
	}
	return false;
}
</SCRIPT>

<?
$kapacita = $zaznam_z['kapacita'];
DrawPageRaceTitle('Vybraný závod',$kapacita,$num_rows);

if (!$new)
{
	$add_r[0] ='Kategorie';
	$add_r[1] ='<B>'.xss_prevent($zaznam_rg['kat']).'</B>';
	RaceInfoTable($zaznam_z,$add_r,true,false,true);
}
else
{
	RaceInfoTable($zaznam_z,'',true,false,true);
	$zaznam_rg['kat'] = '';
	$zaznam_rg['pozn'] = '';
	$zaznam_rg['pozn_in'] = '';
	$zaznam_rg['transport'] = null;
	$zaznam_rg['sedadel'] = null;
	$zaznam_rg['ubytovani'] = null;
}
?>
<BR>
<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>
<BR><BR>
<hr><BR>

<?

if ($zaznam_u['entry_locked'] != 0)
{
	echo('<span class="WarningText">Máte zamknutou možnost se přihlašovat.</span>'."<br>\n");

	if ($zaznam_rg['kat'] != '')
	{
		echo('<BR><BR>Vybraná kategorie:&nbsp;'.$zaznam_rg['kat']);
		echo "<BR>";
		if (($zaznam_z["transport"]==1||$zaznam_z["transport"]==3) && $g_enable_race_transport)
		{
			echo "<BR>";
			$trans=$zaznam_rg["transport"]?"Ano":"Ne";
			if ($zaznam_z["transport"]==1) {
				echo 'Chci využít společnou dopravu:&nbsp;'.$trans;
			}
			else {
				echo "<BR>";
				echo ('Ve sdílené dopravě&nbsp;'. $g_sedadel_cnt[$zaznam_z["sedadel"]]);
			}
			echo "<BR>";
		}
		if ($zaznam_z["ubytovani"]==1 && $g_enable_race_accommodation)
		{
			echo "<BR>";
			$ubyt=$zaznam_rg["ubytovani"]?"Ano":"Ne";
			echo 'Chci využít společné ubytování:&nbsp;'.$ubyt;
			echo "<BR>";
		}
		echo "<BR>";
		echo 'Poznámka:&nbsp;'.$zaznam_rg['pozn'].'&nbsp;(do&nbsp;přihlášky)';
		echo "<BR><BR>";
		echo 'Poznámka:&nbsp;'.$zaznam_rg['pozn_in'].'&nbsp;(interní)';
		echo "<BR><BR>";
	}
}
else
{	// zacatek - povoleno prihlasovani
?>
<FORM METHOD=POST ACTION="us_race_regon_exc.php" name="form1" onsubmit="return check_reg(this);">

Do které kategorie chcete přihlásit:&nbsp;
<?
echo'<br>';
$kategorie=explode(';',$zaznam_z['kategorie']);
for ($i=0; $i<count($kategorie); $i++)
{
	if ($kategorie[$i] != '')
		echo "<button onclick=\"javascript:zmen_kat('".xss_prevent($kategorie[$i])."');return false;\">".xss_prevent($kategorie[$i])."</button>";
}

echo('<BR><BR>Vybraná kategorie:&nbsp;');
echo('<INPUT TYPE="text" NAME="kat" size=4 value="'.xss_prevent($zaznam_rg['kat']).'">');
echo("<BR>\n");

if ($g_enable_race_transport || $g_enable_race_accommodation)
	echo "<BR>\n";

if ($g_enable_race_transport)
{
	if ($zaznam_z["transport"]==1)
	{
		$trans=$zaznam_rg["transport"]?"CHECKED":"";
		echo '<label for="transport">Chci využít společnou dopravu</label>&nbsp;<input type="checkbox" name="transport" id="transport" '.$trans.'>';
	}
	else if ($zaznam_z["transport"]==2)
	{
		echo 'Společná doprava je zadána automaticky.';
	}
	else if ($zaznam_z["transport"]==3)
	{
		echo '<label for="transport">Ve sdílené dopravě</label>&nbsp;';
		RenderSharedTransportInput( "sedadel", $zaznam_rg["transport"], $zaznam_rg["sedadel"] );
	}
	echo("<BR>\n");
}
if ($g_enable_race_accommodation)
{
	if ($zaznam_z["ubytovani"]==1)
	{
		$trans=$zaznam_rg["ubytovani"]?"CHECKED":"";
		echo '<label for="ubytovani">Chci využít společné ubytování</label>&nbsp;<input type="checkbox" name="ubytovani" id="ubytovani" '.$trans.'>';
	}
	else if ($zaznam_z["ubytovani"]==2)
	{
		echo 'Společné ubytování je zadáno automaticky.';
	}
}
?>
<BR><BR>
Poznámka&nbsp;<INPUT TYPE="text" name="pozn" size="50" maxlength="250" value="<?echo xss_prevent($zaznam_rg['pozn']) ?>">&nbsp;(do&nbsp;přihlášky)
<BR><BR>
Poznámka&nbsp;<INPUT TYPE="text" name="pozn2" size="50" maxlength="250" value="<?echo xss_prevent($zaznam_rg['pozn_in'])?>">&nbsp;(interní)
<BR><BR>

<INPUT TYPE="hidden" name="id_us" value="<?echo xss_prevent($id_us)?>">
<INPUT TYPE="hidden" name="id_zav" value="<?echo xss_prevent($id_zav)?>">
<?
if ($new)
{
	echo ('<INPUT TYPE="hidden" name="novy" value="'.xss_prevent($new).'">'."\n");
	echo ('<INPUT TYPE="submit" value="Přihlásit na závod">'."\n");
}
else
{
	echo ('<INPUT TYPE="hidden" name="id_z" value="'.xss_prevent($zaznam_rg['id']).'">'."\n");
?>
<INPUT TYPE="submit" value="Změnit údaje">
&nbsp;&nbsp;&nbsp;&nbsp;<BUTTON onclick="return submit_off();">Odhlásit ze závodu</BUTTON>
<?
}
?>
</FORM>
<?
} // konec - povoleno prihlasovani

if(strlen($zaznam_z['poznamka']) > 0)
{
?>
<p><b>Doplňující informace o závodě (interní)</b> :<br>
<?
	echo('&nbsp;&nbsp;&nbsp;'.xss_prevent($zaznam_z['poznamka']).'</p>');
}
?>

<BR><hr><BR>
<?
DrawPageSubTitle('Přihlášení závodníci');

$is_spol_dopr_on = ($zaznam_z["transport"]==1) && $g_enable_race_transport;
$is_sdil_dopr_on = ($zaznam_z["transport"]==3) && $g_enable_race_transport;
$is_spol_ubyt_on = ($zaznam_z["ubytovani"]==1) && $g_enable_race_accommodation;

// define table
$tbl_renderer = RaceRendererFactory::createTable();
$tbl_renderer->addColumns('id','jmeno','prijmeni','kat');
if($is_spol_dopr_on||$is_sdil_dopr_on)
	$tbl_renderer->addColumns('transport');
if($is_sdil_dopr_on)
	$tbl_renderer->addColumns('sedadel');
if($is_spol_ubyt_on)
	$tbl_renderer->addColumns('ubytovani');
if($zaznam_z['prihlasky'] > 1)
	$tbl_renderer->addColumns('termin');
$tbl_renderer->addColumns('pozn','pozn_in');

if ($g_enable_race_capacity && isSet ($zaznam_z['kapacita']) ) {
	$tbl_renderer->addBreak(new LimitBreakDetector($zaznam_z['kapacita']));
	$tbl_renderer->setRowTextPainter ( new GreyLastNPainter($zaznam_z['kapacita']) );	
}

echo $tbl_renderer->render( new html_table_mc(), $zaznamy, [] );

$stats = countRaceStats($zaznamy, $is_sdil_dopr_on);
RenderRaceStats(
    $stats,
    $is_spol_dopr_on,
    $is_sdil_dopr_on,
    $is_spol_ubyt_on
);

?>

<BR>

<?
HTML_Footer();
?>