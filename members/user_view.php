<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require("./cfg/_colors.php");
require("./cfg/_globals.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
require ("./common.inc.php");
require ("./ctable.inc.php");

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>

DrawPageTitle('Pøehled pøihlášek na závody');
?>
<script language="javascript">
<!-- 
	javascript:set_default_size(600,600);
//-->
</script>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<?

// id je z tabulky "users"
@$vysledekU=MySQL_Query("SELECT id,prijmeni,jmeno FROM ".TBL_USER." WHERE id=".$id." LIMIT 1");
$zaznamU=MySQL_Fetch_Array($vysledekU)

?>
<H3>Vybraný èlen : <? echo $zaznamU["jmeno"]." ".$zaznamU["prijmeni"]; ?></H3>
<CENTER>

<?
include ("./common_race.inc.php");

$query = 'SELECT r.id, datum, datum2, nazev, oddil, typ, vicedenni, misto, kat FROM '.TBL_RACE.' as r LEFT JOIN '.TBL_ZAVXUS.' as z ON r.id = z.id_zavod AND z.id_user='.$id.' ORDER BY datum';

@$vysledek=MySQL_Query($query);

$num_rows = mysql_num_rows($vysledek);
if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	$old_year = 0;
	if($vysledek != FALSE)
	{
		while ($zaznam=MySQL_Fetch_Array($vysledek))
		{
			$row = array();
			$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

			$prefix = ($race_is_old) ? '<span class="TextAlertExpLight">' : '';
			$suffix = ($race_is_old) ? '</span>' : '';

			//----------------------------
			if($zaznam['vicedenni'])
				$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
			else
				$datum=Date2String($zaznam['datum']);
			//----------------------------
			$row[] = $prefix.$datum.$suffix;
			$row[] = $prefix.$zaznam['nazev'].$suffix;
			$row[] = $prefix.$zaznam['misto'].$suffix;
			$row[] = $prefix.$zaznam['oddil'].$suffix;
			$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\">".GetRaceTypeImg($zaznam['typ']).'</A>';
			if($zaznam['kat'] != NULL)
				$row[] = '<span class="Highlight">'.$zaznam['kat'].'</span>';
			else
				$row[] = '';
			if (!$brk_tbl && $zaznam['datum'] >= GetCurrentDate())
			{
				if($i != 1)
					echo $data_tbl->get_break_row()."\n";
				$brk_tbl = true;
			}
			else if($i != 1 && Date2Year($zaznam['datum']) != $old_year)
			{
					echo $data_tbl->get_break_row(true)."\n";
			}

			echo $data_tbl->get_new_row_arr($row)."\n";
			$i++;
			$old_year = Date2Year($zaznam['datum']);
		}
	}
	echo $data_tbl->get_footer()."\n";
}
?>

<BR>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON>
<BR>
<BR>

</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?include "./footer.inc.php"?>
<!-- Footer End -->
</TD></TR>
</TABLE>

</BODY>
</HTML>
