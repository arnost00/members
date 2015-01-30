<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once("./cfg/_colors.php");
require_once ("./connect.inc.php");
require_once ("./sess.inc.php");

if (!IsLoggedManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$reg = (isset($reg) && is_numeric($reg)) ? (int)$reg : 0;
$year = (isset($year) && is_numeric($year)) ? (int)$year : 0;

require_once ("./ctable.inc.php");

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");

DrawPageTitle('Hledání volných registračních čísel');
?>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<form method=post action="find_reg.php">

<?
$data_tbl = new html_table_form();
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Registrační číslo', $g_shortcut.'&nbsp;&nbsp;<input type="text" name="reg" value="'.(($reg != 0)? RegNumToStr($reg) : '').'"> (9999)');
echo $data_tbl->get_new_row('Rok narození', '<input type="text" name="year" value="'.(($year != 0)? $year : '').'"> (9999)');
echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<INPUT TYPE="submit" VALUE="Vyhledat"> <BUTTON onclick="javascript:close_popup();">Zavřít</BUTTON>');
echo $data_tbl->get_footer()."\n";
?>
</form>
<?
if($reg != 0 || $year != 0)
{
	db_Connect();
	if($reg != 0)
	{
		DrawPageSubTitle('Dle registračního čísla - '.$g_shortcut.RegNumToStr($reg));
		@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE reg = ".$reg." ORDER BY reg ASC");
		$cnt= ($vysledek != FALSE) ? mysql_num_rows($vysledek) : 0;
		if($cnt > 0)
		{
			$data_tbl = new html_table_mc();
			$col = 0;
			$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_LEFT,"Registrační číslo");
			$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
			$data_tbl->set_header_col($col++,'Aktivní',ALIGN_CENTER);

			echo $data_tbl->get_css()."\n";
			echo $data_tbl->get_header()."\n";
			echo $data_tbl->get_header_row()."\n";
		
			while ($zaznam=MySQL_Fetch_Array($vysledek))
			{
				$row = array();
				$row[] = $g_shortcut.RegNumToStr($zaznam['reg']);
				$row[] = $zaznam['prijmeni'].' '.$zaznam['jmeno'];
				$row[] = ($zaznam['hidden'] == 1)? 'Ne':'Ano';
				echo $data_tbl->get_new_row_arr($row)."\n";
			}
			echo $data_tbl->get_footer()."\n";
		}
		else
			echo('Registrační číslo '.$g_shortcut.RegNumToStr($reg).' nebylo nalezeno.');
	}
	else if($year != 0)
	{
		DrawPageSubTitle('Dle roku narození - '.$year);
		
		if($year > 100)
			$year = $year % 100;
		$year *= 100;
		$y1 = $year;
		$y2 = $year + 99;

		@$vysledek=MySQL_Query("SELECT * FROM ".TBL_USER." WHERE reg >= ".$y1." AND reg <= ".$y2." ORDER BY reg ASC");
		$cnt= ($vysledek != FALSE) ? mysql_num_rows($vysledek) : 0;
		if($cnt > 0)
		{
			$data_tbl = new html_table_mc();
			$col = 0;
			$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_LEFT,"Registrační číslo");
			$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
			$data_tbl->set_header_col($col++,'Aktivní',ALIGN_CENTER);

			echo $data_tbl->get_css()."\n";
			echo $data_tbl->get_header()."\n";
			echo $data_tbl->get_header_row()."\n";
		
			while ($zaznam=MySQL_Fetch_Array($vysledek))
			{
				$row = array();
				$row[] = $g_shortcut.RegNumToStr($zaznam['reg']);
				$row[] = $zaznam['prijmeni'].' '.$zaznam['jmeno'];
				$row[] = ($zaznam['hidden'] == 1)? 'Ne':'Ano';
				echo $data_tbl->get_new_row_arr($row)."\n";
			}
			echo $data_tbl->get_footer()."\n";
		}
		else
			echo('Nebyl nalezen žádný člen s rokem narození '.$year);
	}
}
?>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
</TABLE>

<?
HTML_Footer();
?>