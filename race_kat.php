<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
require ("./connect.inc.php");
require ("./sess.inc.php");
require("./cfg/_colors.php");

require ("./ctable.inc.php");
include ("./common.inc.php");
include ("./common_race.inc.php");
include ('./url.inc.php');

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Editace kategorií v závodu', false);

db_Connect();

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_RACE." where id=$id LIMIT 1");
$zaznam=MySQL_Fetch_Array($vysledek);
$kat_nf ='';
?>
<H3>Vybraný závod</H3>
<?
RaceInfoTable($zaznam);
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

<br><br><INPUT TYPE="submit" VALUE="Odeslat zmìny kategorií">
<H3>Kategorie v závodì</H3>

<?include "./race_kateg.inc.php"?>

<BR>

Nestandartni kategorie&nbsp;&nbsp;

<button onclick="javascript:zmen_kat_null(); return false;">Vyprázdni</button><BR>

<TEXTAREA name="kat_n" cols="90" rows="3"><?echo $kat_nf;?></TEXTAREA><BR>

<span class="WarningText">Zadavej jako text bez uvozovek, kazdou kategorii ukonci strednikem, vse bez mezer</span>

<BR><BR>Pøedefinované kategorie :
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['oblz']?>'); return false;">Oblž</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['oblz_vetsi']?>'); return false;">Oblž vìtší</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['becka']?>'); return false;">žeb.B.</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['acka']?>'); return false;">žeb.A.</button>&nbsp;
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['stafety']?>'); return false;">Štafety</button>
<button onclick="javascript:zmen_kat_n('<? echo $g_kategorie ['MTBO']?>'); return false;">MTBO</button>
<BR>
<BR>
<span class="kategory_small_list">
Oblž = (<? echo $g_kategorie ['oblz']; ?>)
<BR>
Oblž vìtší = (<? echo $g_kategorie ['oblz_vetsi']; ?>)
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

</FORM>

<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>

<br><br>Aktuální kategorie:<br>
<span class="kategory_small_list"><? echo $zaznam['kategorie'];?></span><br>

</BODY>
</HTML>