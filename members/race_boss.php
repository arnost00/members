<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

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

DrawPageTitle('Editace vedoucí na závod', false);

db_Connect();

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_RACE." where `id`='$id' LIMIT 1");
$zaznam=MySQL_Fetch_Array($vysledek);

@$vysledekU=MySQL_Query("SELECT id,prijmeni,jmeno,hidden FROM ".TBL_USER." ORDER BY sort_name ASC");

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam);

DrawPageSubTitle('Úprava');
?>

<FORM METHOD="POST" ACTION="./race_boss_exc.php?id=<?echo $id?>">

<TABLE width="90%">
<TR>
	<TD width="130" align="right" rowspan="4" valign="top">Vedoucí</TD>
	<TD width="5" rowspan="4"></TD>
	<TD>
		<select name='boss'>
<?
	echo("<option value='0'".(($zaznam['vedouci'] == 0)? ' SELECTED' : '').">- - - -</option>");
	while ($zaznamU=MySQL_Fetch_Array($vysledekU))
	{
		if ($zaznamU['hidden'] == 0)
		{
			$row = $zaznamU["prijmeni"].' '.$zaznamU["jmeno"];
			echo("<option value='".$zaznamU['id']."'".(($zaznam['vedouci'] == $zaznamU['id'])? ' SELECTED' : '').">".$row."</option>");
		}
	}
?>
		</select>
	</TD>
</TR>
<TR><TD></TD></TR>
<TR>
	<TD><INPUT TYPE="submit" VALUE="Odeslat údaje"></TD>
</TR>
<TR><TD></TD></TR>
</TABLE>

</FORM>

<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>

</BODY>
</HTML>