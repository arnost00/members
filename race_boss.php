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
$query = "SELECT * FROM ".TBL_RACE." where `id`='$id' LIMIT 1";
@$vysledek=query_db($query);
$zaznam=mysqli_fetch_array($vysledek);
$query = "SELECT id,prijmeni,jmeno,hidden FROM ".TBL_USER." ORDER BY sort_name ASC";
@$vysledekU=query_db($query);

DrawPageSubTitle('Vybraný závod');

RaceInfoTable($zaznam,'',false,false,true);

DrawPageSubTitle('Úprava');
?>

<FORM METHOD="POST" ACTION="./race_boss_exc.php?id=<?echo $id?>">

<TABLE>
<TR>
	<TD width="130" align="right" valign="top">Vedoucí</TD>
	<TD width="5"></TD>
	<TD>
		<select name='boss' size="15">
<?
	echo("<option value='0'".(($zaznam['vedouci'] == 0)? ' SELECTED' : '').">- - - -</option>");
	while ($zaznamU=mysqli_fetch_array($vysledekU))
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
	<TD width="5"></TD>
	<TD align="left" valign="top"><INPUT TYPE="submit" VALUE="Odeslat údaje" valign="top"></TD>
</TR>
</TABLE>

</FORM>

<BUTTON onclick="javascript:close_popup();">Zpět</BUTTON>

<?
HTML_Footer();
?>