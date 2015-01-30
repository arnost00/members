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

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

DrawPageTitle('Editace vedoucí na závod');

db_Connect();

@$vysledek=MySQL_Query("SELECT * FROM ".TBL_RACE." where `id`='$id' LIMIT 1");
$zaznam=MySQL_Fetch_Array($vysledek);

@$vysledekU=MySQL_Query("SELECT id,prijmeni,jmeno,hidden FROM ".TBL_USER." ORDER BY sort_name ASC");

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam,'',false,false,true);

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

<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>

<?
HTML_Footer();
?>