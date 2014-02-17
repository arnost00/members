<?php /* aktuality v prihlaskach */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Aktuální informace (Aktualitky)');
?>
<CENTER>
<script language="javascript">
<!-- 
/*	"menubar=yes,status=yes,width=600,height=600"	*/

	javascript:set_default_size(800,600);
//-->
</script>

<A name="races"></A>
<?
DrawPageSubTitle('Nejbližší závody (do '.GC_SHOW_RACE_DAYS.' dní)');

include ('./common_race.inc.php');
include ('./url.inc.php');

if(SHOW_USER)
{
	@$vysledek1=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." where id_user=$usr->user_id");

	while ($zaznam1=MySQL_Fetch_Array($vysledek1))
	{
		$z=$zaznam1['id_zavod'];
		$zav[$z]=$zaznam1['kat'];
		$zav_t[$z]=$zaznam1['termin'];
		$zaz[]=$zaznam1['id_zavod'];
	}
?>
<script language="javascript">
<!-- 
	function confirm_delete() {
		return confirm('Opravdu se chcete odhlasit?');
	}

	javascript:set_default_size(600,600);
//-->
</script>

<?	
}

$curr_date = GetCurrentDate();

@$vysledek=MySQL_Query("SELECT id,datum,datum2,nazev,typ,ranking,odkaz,prihlasky, prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, vicedenni,misto,oddil, vedouci, cancelled FROM ".TBL_RACE." WHERE datum >= ".$curr_date." AND datum <= ".IncDate($curr_date,GC_SHOW_RACE_DAYS)." ORDER BY datum, datum2, id");

if (mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Poø.',ALIGN_CENTER,"Poøadatel");
	$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ závodu");
	$data_tbl->set_header_col_with_help($col++,'W',ALIGN_CENTER,"Web závodu");
	if(SHOW_USER)
		$data_tbl->set_header_col($col++,'Pøihlášen',ALIGN_CENTER);
	else
		$data_tbl->set_header_col_with_help($col++,'Pø',ALIGN_CENTER,"Zobrazit pøihlášené");
	if($g_enable_race_boss)
		$data_tbl->set_header_col($col++,'Vedoucí',ALIGN_CENTER);
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		$nazev = '<A href="javascript:open_race_info('.$zaznam['id'].')" class="adr_name">'.GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']).'</A>';
		$misto = GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']);
		$oddil = $zaznam['oddil'];
		$typ = GetRaceTypeImg($zaznam['typ']);
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		$prihl = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
		if(SHOW_USER)
		{
			if (IsSet($zaz) && count($zaz) > 0 && in_array($zaznam['id'],$zaz))
			{
				$z=$zaznam['id'];
				$prihl = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$z."&us=1','')\"><span class=\"Highlight\">".$zav[$z].'</span></A>';
			}
		}

		if($g_enable_race_boss)
		{
			$boss = '-';
			if($zaznam['vedouci'] != 0)
			{
				@$vysledekU=MySQL_Query("SELECT jmeno,prijmeni FROM ".TBL_USER." WHERE id = '".$zaznam['vedouci']."' LIMIT 1");
				@$zaznamU=MySQL_Fetch_Array($vysledekU);
				if($zaznamU != FALSE)
					$boss = $zaznamU['jmeno'].' '.$zaznamU['prijmeni'];
			}
			echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ,$odkaz,$prihl,$boss);
		}
		else
			echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ,$odkaz,$prihl);
	}
	echo $data_tbl->get_footer()."\n";
}
else
{
	echo "V nejbližších ".GC_SHOW_RACE_DAYS." dnech není žádný závod.<BR>";
}
?>
<BR>

<A name="regs"></A>
<?
DrawPageSubTitle('Nejbližší pøihlášky (do '.GC_SHOW_REG_DAYS.' dní)');

$d1 = $curr_date;
$d2 = IncDate($curr_date,GC_SHOW_REG_DAYS);
$query = 'SELECT id, datum, datum2, nazev, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, vicedenni, misto, oddil, vedouci, cancelled FROM '.TBL_RACE.' WHERE ((prihlasky1 >= '.$d1.' && prihlasky1 <= '.$d2.') || (prihlasky2 >= '.$d1.' && prihlasky2 <= '.$d2.') || (prihlasky3 >= '.$d1.' && prihlasky3 <= '.$d2.') || (prihlasky4 >= '.$d1.' && prihlasky4 <= '.$d2.') || (prihlasky5 >= '.$d1.' && prihlasky5 <= '.$d2.')) ORDER BY datum';
@$vysledek=MySQL_Query($query);

if (mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Poø.',ALIGN_CENTER,"Poøadatel");
	$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ závodu");
	$data_tbl->set_header_col_with_help($col++,'W',ALIGN_CENTER,"Web závodu");
	if(SHOW_USER)
		$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	else
		$data_tbl->set_header_col_with_help($col++,'Pø',ALIGN_CENTER,"Zobrazit pøihlášené");
	$data_tbl->set_header_col($col++,'Pøihlášky',ALIGN_CENTER);
	if($g_enable_race_boss)
		$data_tbl->set_header_col($col++,'Vedoucí',ALIGN_CENTER);
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";
	while ($zaznam=MySQL_Fetch_Array($vysledek))
	{
		if($zaznam['vicedenni'])
			$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
		else
			$datum=Date2String($zaznam['datum']);

		$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
		$prihlasky_out_term = Date2String($prihlasky_curr[0]);
		if($zaznam['prihlasky'] > 1)
			$prihlasky_out_term .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
		$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
		$termin = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);

		$nazev = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled'])."</A>";
		$misto = GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']);
		
		$oddil = $zaznam['oddil'];
		$typ = GetRaceTypeImg($zaznam['typ']);
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		$prihl2 = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
		if(SHOW_USER)
		{
			$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
			$prihlasky_out_term = Date2String($prihlasky_curr[0]);
			if($zaznam['prihlasky'] > 1)
				$prihlasky_out_term .= '&nbsp;/&nbsp;'.$prihlasky_curr[1];
			$time_to_reg = GetTimeToReg($prihlasky_curr[0]);
			$termin = raceterms::ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_out_term);

			$prihl_finish = ($time_to_reg == -1 && $prihlasky_curr[0] != 0) || ($prihlasky_curr[0] == 0 && $zaznam['datum'] <= $curr_date);
			$zbr = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\"><span class=\"TextAlertExpLight\">Zbr</span></A>";
			
			if (IsSet($zaz) && count($zaz) > 0 && in_array($zaznam['id'],$zaz))
			{
				$prihl_finish2 = $prihl_finish || ( $prihlasky_curr[0] != 0 && $prihlasky_curr[1] != $zav_t[$zaznam["id"]]);
				if($prihl_finish2 != $prihl_finish)
				{
					$prihl = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$zaznam['id']."&us=1','')\"><span class=\"Highlight\">".$zav[$zaznam["id"]].'</span></A> / '.$zav_t[$zaznam["id"]];
				}
				else if (!$prihl_finish)
				{
					$prihl = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam['id']."&id_us=".$usr->user_id."','')\" class=\"Highlight\">".$zav[$zaznam["id"]]."</A> / <A HREF=\"javascript:open_win('./us_race_regoff_exc.php?id_zav=".$zaznam['id']."&id_us=".$usr->user_id."','')\" onclick=\"return confirm_delete();\" class=\"Erase\">Od.</A>";
				}
				else
				{
					$prihl = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_.'&id='.$zaznam['id']."&us=1','')\"><span class=\"Highlight\">".$zav[$zaznam["id"]].'</span></A>';
				}
			}
			else
			{
				if (!$prihl_finish)
				{
					$prihl = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam["id"]."&id_us=".$usr->user_id."','')\">Pøihl.</A> / ".$zbr;
				}
				else
				{
					$prihl = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_."&id=".$zaznam["id"]."&us=1','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
				}			
			}
		}
		if($g_enable_race_boss)
		{
			$boss = '-';
			if($zaznam['vedouci'] != 0)
			{
				@$vysledekU=MySQL_Query("SELECT jmeno,prijmeni FROM ".TBL_USER." WHERE id = '".$zaznam['vedouci']."' LIMIT 1");
				@$zaznamU=MySQL_Fetch_Array($vysledekU);
				if($zaznamU != FALSE)
					$boss = $zaznamU['jmeno'].' '.$zaznamU['prijmeni'];
			}
			if(SHOW_USER)
				echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ,$odkaz,$prihl,$termin,$boss);
			else
				echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ,$odkaz,$prihl2,$termin,$boss);
		}
		else if(SHOW_USER)
			echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ,$odkaz,$prihl,$termin);
		else
			echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ,$odkaz,$prihl2,$termin);
	}
	echo $data_tbl->get_footer()."\n";
}
else
{
	echo "V nejbližších ".GC_SHOW_REG_DAYS." dnech není žádná pøihláška na závod.<BR>";
}
?>
<BR>

</CENTER>