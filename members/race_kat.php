<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once("./cfg/_colors.php");

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

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_RACE." where id=$id LIMIT 1");
$zaznam=MySQL_Fetch_Array($vysledek);
$kat_nf ='';

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

Nestandartni kategorie&nbsp;&nbsp;

<button onclick="javascript:zmen_kat_null(); return false;">Vyprázdni</button><BR>

<TEXTAREA name="kat_n" cols="90" rows="3"><?echo $kat_nf;?></TEXTAREA><BR>

<span class="WarningText">Zadavej jako text bez uvozovek, kazdou kategorii ukonci strednikem, vse bez mezer</span>

<BR><BR>Předefinované kategorie :
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['oblz']?>'); return false;">Oblž</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['oblz_vetsi']?>'); return false;">Oblž větší</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['becka']?>'); return false;">žeb.B.</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['acka']?>'); return false;">žeb.A.</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['stafety']?>'); return false;">Štafety</button>
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['MTBO']?>'); return false;">MTBO</button>
<BR>
<BR>
<span class="kategory_small_list">
Oblž = (<? echo $g_kategorie ['oblz']; ?>)
<BR>
Oblž větší = (<? echo $g_kategorie ['oblz_vetsi']; ?>)
<BR>
žeb.B. = (<? echo $g_kategorie ['becka']; ?>)
<BR>
žeb.A. = (<? echo $g_kategorie ['acka']; ?>)
<BR>
Štafety = (<? echo $g_kategorie ['stafety']; ?>)
<BR>
MTBO = (<? echo $g_kategorie ['MTBO']; ?>)
</span>
<BR>

<br><INPUT TYPE="submit" VALUE="Odeslat změny kategorií">&nbsp;&nbsp;
</FORM>

<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>

<br><br>Aktuální kategorie:<br>
<span class="kategory_small_list"><? echo $zaznam['kategorie'];?></span><br>

<?
HTML_Footer();
?>