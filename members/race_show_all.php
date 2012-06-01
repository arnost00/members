<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

require ("./timestamp.inc.php");
_set_global_RT_Start();
require("./cfg/_colors.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
require ("./common.inc.php");
require ("./ctable.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

include "./header.inc.php"; // header obsahuje uvod html a konci <BODY>

DrawPageTitle('Køížový pøehled pøihlášek', false);

db_Connect();

$old = (isset($old) && is_numeric($old)) ? $old : 0;
?>
Po nájezdu na datum závodu se zobrazí podrobnìjší informace.<BR>
<?
if($old == 0)
{
?>
Zobrazení pøehledu pro starší závody (již probìhlé) je možno <A HREF="./race_show_all.php?old=1">zde</A>.<br>
<?
}
@$vysledek=MySQL_Query("SELECT id,hidden,prijmeni,jmeno FROM ".TBL_USER." ORDER BY sort_name ASC")
	or die("Chyba pøi provádìní dotazu do databáze.");

$sql_query = "SELECT id,datum,datum2,nazev,misto,vicedenni,oddil FROM ".TBL_RACE;
if($old == 0)
{
	$curr_date = GetCurrentDate();
	$sql_query .= " WHERE datum >= ".$curr_date;
}
$sql_query .= " ORDER BY datum, datum2, id";

@$races=MySQL_Query($sql_query)
	or die("Chyba pøi provádìní dotazu do databáze.");

$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$i=0;
$races_arr = array();
$race_msg = array();
while ($race=MySQL_Fetch_Array($races))
{
	$datum= Date2StringDM($race["datum"]);
	$tip_code = 'onMouseOut="hideTip();" onMouseOver="doTooltip(event,'.++$i.');"';
	$races_arr[] = $race["id"];
	if($race['vicedenni'])
		$race_msg[$i] = Date2StringFT($race['datum'],$race['datum2']).'<BR>'.$race['nazev'].'<BR>'.$race['misto'].'<BR>'.$race['oddil'];
	else
		$race_msg[$i] = Date2String($race['datum']).'<BR>'.$race['nazev'].'<BR>'.$race['misto'].'<BR>'.$race['oddil'];
	$race_dta[$i-1] = 0;
	$data_tbl->set_header_col($col++,$datum,ALIGN_CENTER,0,$tip_code);
}

$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT,140);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i=1;
while ($row=MySQL_Fetch_Array($vysledek))
{
	if (!$row["hidden"])
	{
		if (($i % 25) == 0)
			echo $data_tbl->get_header_row()."\n";
		$tbl_row = array();
		$tbl_row[] = $row["prijmeni"]."&nbsp;".$row["jmeno"];
		
		$rc=(($i++ % 2) == 0) ? $g_colors["table_row1"] : $g_colors["table_row2"];
		$user_id = $row["id"];
		@$vysledek1=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." where id_user='$user_id'");
		unset($zav);
		while ($zaznam1=MySQL_Fetch_Array($vysledek1))
		{
			$zav[] = $zaznam1["id_zavod"];
		}
		if (IsSet($zav) && sizeof($zav) > 0)
			for ($j = 0; $j < sizeof($races_arr);$j++)
			{
				if (in_array($races_arr[$j],$zav))
				{
					$tbl_row[] = '<B>X</B>';
					$race_dta[$j]++;
				}
				else
					$tbl_row[] = '';;
			}
		else
			for ($j = 0; $j < sizeof($races_arr);$j++)
			{
				$tbl_row[] = '';
			}

		$tbl_row[] = $row["prijmeni"]."&nbsp;".$row["jmeno"];

		echo $data_tbl->get_new_row_arr($tbl_row)."\n";
	}
}
echo $data_tbl->get_header_row()."\n";

$tbl_row = array();
$tbl_row[] = 'Celkem pøihlášeno';
for($ii=0; $ii<sizeof($races_arr); $ii++)
{
	$tbl_row[] = $race_dta[$ii];
}
echo $data_tbl->get_new_row_arr($tbl_row)."\n";

echo $data_tbl->get_footer()."\n";

if (sizeof($race_msg) > 0)
{
?>

<script src="tooltip.js" type="text/javascript"></script>

<script language="javascript" type="text/javascript">
<!--
<?
	for ($i = 0; $i< sizeof($race_msg);$i++)
	{
		echo "messages[".($i+1)."] = new Array('".$race_msg[$i+1]."');";//,'#CCCCCC','#000000');";
	}
?>
//-->
</script>
<div id="tipDiv" style="position:absolute; visibility:hidden; z-index:100"></div>
<?
}

_set_global_RT_End();
if (!$g_is_release || IsLoggedAdmin())
{
	echo '<p align="right"><span class ="MiniHelpText">';
	_print_global_RT_difference_TS();
	echo "</span><BR>\n";
}
?>
</BODY>
</HTML>