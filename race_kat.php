<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once("./cfg/_colors.php");
require_once('./cfg/_globals.php');

require_once ("./ctable.inc.php");
require_once ("./common.inc.php");
require_once ("./common_race.inc.php");
require_once ('./url.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./connectors.php");

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Editace kategorií v závodu');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

@$vysledek=query_db("SELECT * FROM ".TBL_RACE." where id=$id LIMIT 1");
$zaznam=mysqli_fetch_array($vysledek);
$kat_nf ='';
$curr_kateg = $zaznam['kategorie'];
$ext_id = $zaznam['ext_id'];

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam,'',false,false,true);
?>

<SCRIPT LANGUAGE="JavaScript">
function zmen_kat_n($str)
{
	let field = document.form2.kat_n;
	let sep = field.value === '' || field.value.endsWith(';') ? '' : ';';
	field.value += sep + $str;
}

function reset_kat_n($str)
{
	document.form2.querySelectorAll('input[type="checkbox"]').forEach(checkbox => checkbox.checked = false);

	document.form2.kat_n.value=$str;
}

function zmen_kat_null()
{
	document.form2.kat_n.value="";
}
</SCRIPT>

<FORM METHOD=POST ACTION="./race_kat_exc.php?id=<?echo $id?>" name="form2">

<? DrawPageSubTitle('Kategorie v závodě'); ?>

<?require_once "./race_kateg.inc.php"?>

<BR>

Nestandartní kategorie&nbsp;&nbsp;

<button onclick="javascript:zmen_kat_null(); return false;">Vyprázdni</button><BR>

<TEXTAREA name="kat_n" cols="90" rows="3"><?echo $kat_nf;?></TEXTAREA><BR>

<span class="WarningText">Zadávej jako text bez uvozovek, každou kategorii ukonči středníkem, vše bez mezer</span>

<BR><BR>Předdefinované kategorie :

<?
@$vysledek=query_db("SELECT * FROM ".TBL_CATEGORIES_PREDEF." order by ID");
$cl = '';
while ($zaznam=mysqli_fetch_array($vysledek))
{
	echo('<button onclick="javascript:zmen_kat_n(\''.$zaznam['cat_list'].'\'); return false;">'.$zaznam['name'].'</button>&nbsp;');
	$cl .= $zaznam['name'].' = ('.$zaznam['cat_list'].')';
	$cl .= "<BR>\n";
}
if ( isset ( $ext_id ) ) {

    $connector = ConnectorFactory::create();

    // Get race info by race ID
    $raceInfo = $connector->getRaceInfo($ext_id);
    
    if ( isset ( $raceInfo->kategorie ) ) {
		echo('<button onclick="javascript:zmen_kat_n(\''.$raceInfo->kategorie.'\'); return false;">'.$connector->getSystemName().'</button>&nbsp;');
		$cl .= $connector->getSystemName().' = ('.$raceInfo->kategorie.')';
		$cl .= "<BR>\n";
		
		// check if the external and internal lists are the same
		$internal = explode(';', $curr_kateg);
		$external = explode(';', $raceInfo->kategorie);

		sort($internal);
		sort($external);

		if ( $internal !== $external ) {
			echo('<button title="Nastav kategorie podle systému '.$connector->getSystemName().'"onclick="javascript:reset_kat_n(\''.$raceInfo->kategorie.'\'); return false;">' . "\u{1F528} ".$connector->getSystemName().'</button>&nbsp;');
		}
	}
	
}
?>
<BR>
<BR>
<span class="kategory_small_list">
<?
echo($cl);
?>
</span>
<BR>

<br><INPUT TYPE="submit" VALUE="Odeslat změny kategorií">
</FORM><br />

<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>

<br><br>Aktuální kategorie:<br>
<span class="kategory_small_list"><? echo $curr_kateg;?></span><br>

<?
HTML_Footer();
?>
