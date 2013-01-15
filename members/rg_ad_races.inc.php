<?php /* adminova stranka - editace zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Kalend�� z�vod� - P�ihl�ky na z�vody', false);
?>
<CENTER>
<script language="javascript">
<!-- 
/*	"menubar=yes,status=yes,width=600,height=600"	*/

	javascript:set_default_size(800,600);
//-->
</script>
<?
include ('./common_race.inc.php');
include ('./url.inc.php');

$fA = (IsSet($fA) && is_numeric($fA)) ? (int)$fA : 0;
$fB = (IsSet($fB) && is_numeric($fB)) ? (int)$fB : 0;
$fC = (IsSet($fC) && is_numeric($fC)) ? (int)$fC : 0;  // old races
$sql_sub_query = form_filter_racelist('index.php?id='.$id.(($subid != 0) ? '&subid='.$subid : ''),$fA,$fB,$fC);

@$vysledek=MySQL_Query("SELECT id, datum, typ, datum2, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, nazev, vicedenni, odkaz, vedouci, oddil, send, misto FROM ".TBL_RACE.$sql_sub_query.' ORDER BY datum, datum2, id');

$num_rows = mysql_num_rows($vysledek);
if ($num_rows > 0)
{
	show_link_to_actual_race($num_rows);

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER,0);
	$data_tbl->set_header_col($col++,'N�zev',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'M�sto',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Po�.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'W',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Mo�nosti',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'P�ihl�ky',ALIGN_CENTER);
	if($g_enable_race_boss)
		$data_tbl->set_header_col($col++,'Vedouc�',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'OP',ALIGN_CENTER);

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";

	$i = 1;
	$brk_tbl = false;
	$old_year = 0;
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		$row = array();
		
		$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

		$prefix = ($race_is_old) ? '<span class="TextAlertExpLight">' : '';
		$suffix = ($race_is_old) ? '</span>' : '';

		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		//----------------------------
		$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
		$prihlasky=Date2String($prihlasky_curr[0]);
		if($zaznam['prihlasky'] > 1)
			$prihlasky .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];

		if ($race_is_old)
			$prihlasky_out = '<span class="TextAlertExpLight">'.$prihlasky.'</span>';
		else if ($prihlasky_curr != 0 && GetTimeToReg($prihlasky_curr[0]) == -1)
			$prihlasky_out = '<span class="TextAlert">'.$prihlasky.'</span>';
		else
			$prihlasky_out = $prihlasky;

		if($zaznam['prihlasky'] > 1 && !$race_is_old)
		{	// insert before - previous term.
			$prihlasky_prev = raceterms::GetActiveRegDateArrPrev($zaznam);

			if ($prihlasky_prev[0] != 0)
				$prihlasky_out = '<span class="TextAlert">'.Date2String($prihlasky_prev[0]).'&nbsp;/&nbsp;'.$prihlasky_prev[1].'</span><br>'.$prihlasky_out;
		}
		//----------------------------
		$row[] = $prefix.$datum.$suffix;
		$row[] = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".$prefix.$zaznam['nazev'].$suffix."</A>";
		$row[] = $prefix.$zaznam['misto'].$suffix;
		$row[] = $prefix.$zaznam['oddil'].$suffix;
		$row[] = GetRaceTypeImg($zaznam['typ']).'</A>';
		$row[] = GetRaceLinkHTML($zaznam['odkaz']);
		if(!$race_is_old || IsLoggedAdmin())
		{
			$s1 = "<A HREF=\"javascript:open_win2('./race_reg_form.php?id_zav=".$zaznam['id']."','')\">V�.</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win2('./race_reg_chip.php?id_zav=".$zaznam['id']."','')\">SI</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_1.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$zaznam['id']."','')\">P.1</A>&nbsp;/&nbsp;<A HREF=\"javascript:open_win('./race_regs_all.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$zaznam['id']."','')\">P.V</A>&nbsp;/&nbsp;";
			$s2 = "<A HREF=\"javascript:open_win_ex('./race_reg_view.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$zaznam['id']."','',600,600)\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
			$row[] = $s1.$s2;
		}
		else
		{
			$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._REGISTRATOR_GROUP_ID_."&id=".$zaznam['id']."','',600,600)\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
		}

		$row[] = $prihlasky_out;
		
		if($g_enable_race_boss)
		{
			$row[] = (($zaznam['vedouci'] != 0) ? 'A&nbsp;/&nbsp;': '')."<A HREF=\"javascript:open_win('./race_boss.php?id=".$zaznam['id']."','')\">Edit</A>";
		}

		if($zaznam['send'] > 0)
			$row[] = ($zaznam['prihlasky'] > 1) ? $zaznam['send'].'.t.' : 'Ano';
		else
			$row[] = 'Ne';

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

	echo $data_tbl->get_footer()."\n";
}
echo('<a href="race_reg_form_exc.php" target="_blank">V�pis v�ech �len� pro centr�ln� registraci</a><br>');
echo('<a href="race_reg_form_all.php" target="_blank">Zjednodu�en� vytvo�en� a export p�ihl�ky</a><br>');
?>
<BR><hr><BR>
<p>
V�. = Export p�ihl�ky ve form�tu �SOB.<BR>
SI = Editace (Dopln�n�) SI �ip� pro vybran� z�vod.<BR>
P.1 = p�ihla�ov�n� po jednom �lenu.<BR>
P.V = p�ihla�ov�n� v�ech �len� nar�z.<BR>
Zbr = zobrazen� p�ihl�en�ch �len�.<BR>
OP = Odesl�na p�ihl�ka.<BR>
</p>
</CENTER>