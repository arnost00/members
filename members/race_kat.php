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

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Editace kategorií v závodu');

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

@$vysledek=query_db("SELECT * FROM ".TBL_RACE." where id=$id LIMIT 1");
$zaznam=mysqli_fetch_array($vysledek);
$kat_nf ='';
$curr_kateg = $zaznam['kategorie'];

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam,'',false,false,true);
?>

<SCRIPT LANGUAGE="JavaScript">
<!--
function zmen_kat_n($str)
{
	
	document.form2.kat_n.value+=$str;
}

function zmen_kat_null()
{
	document.form2.kat_n.value="";
}

//-->
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