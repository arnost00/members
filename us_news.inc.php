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

<?
if(SHOW_USER)
{
	$date_limit = GetCurrentDate();
	$date_limit -= CG_INTERNAL_NEWS_DAYS_LIMIT *60 * 60 *24;
	
	$sql_query = 'SELECT '.TBL_NEWS.'.*, '.TBL_ACCOUNT.'.podpis FROM '.TBL_NEWS.' LEFT JOIN '.TBL_ACCOUNT.' ON '.TBL_NEWS.'.id_user = '.TBL_ACCOUNT.'.id WHERE '.TBL_NEWS.'.internal = 1 ORDER BY datum > '.$date_limit.' DESC,id DESC LIMIT '.GC_INTERNAL_NEWS_CNT_LIMIT;

	@$vysledek=query_db($sql_query);
	$cnt= ($vysledek != FALSE) ? mysqli_num_rows($vysledek) : 0;
	if($cnt > 0)
	{
		DrawPageSubTitle('Poslední interní novinky');
		include ('common_news.inc.php');
		echo('<TABLE width="100%">');
		while ($zaznam=mysqli_fetch_array($vysledek))
		{
			PrintNewsItem($zaznam,IsLoggedAdmin(),$usr,true);
		}
		echo('</TABLE>');
	}
}
DrawPageSubTitle('Nejbližší závody a přihlášky (do '.GC_SHOW_RACE_AND_REG_DAYS.' dní)');

require_once ('./common_race.inc.php');
require_once ('./url.inc.php');

if(SHOW_USER)
{
	@$vysledek1=query_db("SELECT id_zavod, kat, termin FROM ".TBL_ZAVXUS." where id_user=$usr->user_id");

	while ($zaznam1=mysqli_fetch_array($vysledek1))
	{
		$z=$zaznam1['id_zavod'];
		$zav[$z]=$zaznam1['kat'];
		$zav_t[$z]=$zaznam1['termin'];
		$zaz[]=$zaznam1['id_zavod'];
	}
	
	@$vysledek2=query_db("SELECT * FROM ".TBL_USER." where id=$usr->user_id");
	$entry_lock = false;
	if ($zaznam2=mysqli_fetch_array($vysledek2))
	{
		$entry_lock = ($zaznam2['entry_locked'] != 0);
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

$d1 = $curr_date;
$d2 = IncDate($curr_date,GC_SHOW_REG_DAYS);
$query = 'SELECT r.id, r.datum, datum2, nazev, typ0, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, vicedenni, misto, oddil, vedouci, cancelled, concat(u.jmeno, \' \', u.prijmeni) as boss '
	.' FROM '.TBL_RACE.' r left join '.TBL_USER.' u on r.vedouci = u.id '
	.' WHERE (((prihlasky1 >= '.$d1.' && prihlasky1 <= '.$d2.') || (prihlasky2 >= '.$d1.' && prihlasky2 <= '.$d2.') || (prihlasky3 >= '.$d1.' && prihlasky3 <= '.$d2.') || (prihlasky4 >= '.$d1.' && prihlasky4 <= '.$d2.') || (prihlasky5 >= '.$d1.' && prihlasky5 <= '.$d2.')) || ( r.datum >= '.$d1.' AND r.datum <= '.$d2.')) ORDER BY datum, datum2, r.id';

@$vysledek=query_db($query);

if (mysqli_num_rows($vysledek) > 0)
{
	if (SHOW_USER && $entry_lock)
	{
		echo('<span class="WarningText">Máte zamknutou možnost se přihlašovat.</span>'."<br>\n");
	}
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Poř.',ALIGN_CENTER,"Pořadatel");
	$data_tbl->set_header_col_with_help($col++,'T',ALIGN_CENTER,"Typ akce");
	$data_tbl->set_header_col_with_help($col++,'S',ALIGN_CENTER,"Sport");
	$data_tbl->set_header_col_with_help($col++,'W',ALIGN_CENTER,"Web závodu");
	if(SHOW_USER)
		$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
	else
		$data_tbl->set_header_col_with_help($col++,'Př',ALIGN_CENTER,"Zobrazit přihlášené");
	$data_tbl->set_header_col($col++,'Přihlášky',ALIGN_CENTER);
	if($g_enable_race_boss)
		$data_tbl->set_header_col($col++,'Vedoucí',ALIGN_CENTER);
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	echo $data_tbl->get_header_row()."\n";
	while ($zaznam=mysqli_fetch_array($vysledek))
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
		$typ0 = GetRaceType0($zaznam['typ0']);
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
				else if (!$prihl_finish && !$entry_lock)
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
				if (!$prihl_finish && !$entry_lock)
				{
					$prihl = "<A HREF=\"javascript:open_win('./us_race_regon.php?id_zav=".$zaznam["id"]."&id_us=".$usr->user_id."','')\">Přihl.</A> / ".$zbr;
				}
				else
				{
					$prihl = "<A HREF=\"javascript:open_win('./race_reg_view.php?gr_id="._USER_GROUP_ID_."&id=".$zaznam["id"]."&us=1','')\"><span class=\"TextAlertExpLight\">Zobrazit</span></A>";
				}
			}
		}
		if($g_enable_race_boss)
		{
			$boss = ($zaznam['vedouci'] == 0)? '-': $zaznam['boss'];
			if(SHOW_USER)
				echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ0,$typ,$odkaz,$prihl,$termin,$boss);
			else
				echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ0,$typ,$odkaz,$prihl2,$termin,$boss);
		}
		else if(SHOW_USER)
			echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ0,$typ,$odkaz,$prihl,$termin);
		else
			echo $data_tbl->get_new_row($datum,$nazev,$misto,$oddil,$typ0,$typ,$odkaz,$prihl2,$termin);
	}
	echo $data_tbl->get_footer()."\n";
}
else
{
	echo "V nejbližších ".GC_SHOW_REG_DAYS." dnech není žádná přihláška na závod.<BR>";
}
?>
<BR>

</CENTER>