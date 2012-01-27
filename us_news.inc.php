<?php /* aktuality v prihlaskach */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Aktuální informace (Aktualitky)');
?>
<CENTER>

<A name="races"></A>
<?
DrawPageSubTitle('Nejbližší závody (do'.GC_SHOW_RACE_DAYS.' dní)');

include ('./common_race.inc.php');
include ('./url.inc.php');

if(SHOW_USER)
{
	@$vysledek1=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." where id_user=$usr->user_id");

	while ($zaznam1=MySQL_Fetch_Array($vysledek1))
	{
		$z=$zaznam1['id_zavod'];
		$zav[$z]=$zaznam1['kat'];
		$zaz[]=$zaznam1['id_zavod'];
	}
}

$curr_date = GetCurrentDate();

@$vysledek=MySQL_Query("SELECT id,datum,datum2,nazev,typ,ranking,odkaz,prihlasky, prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5, vicedenni,misto,oddil, vedouci FROM ".TBL_RACE." WHERE datum >= ".$curr_date." AND datum <= ".IncDate($curr_date,GC_SHOW_RACE_DAYS)." ORDER BY datum");

if (mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název'.AddPointerImg(),ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Místo',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'W',ALIGN_CENTER);
	if(SHOW_USER)
		$data_tbl->set_header_col($col++,'Pøihlášen',ALIGN_CENTER);
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

		$nazev = '<A href="javascript:open_race_info('.$zaznam['id'].')" class="adr_name">'.$zaznam['nazev'].'</A>';
		$oddil = $zaznam['oddil'];
		$typ = GetRaceTypeImg($zaznam['typ']);
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		if(SHOW_USER)
		{
			if (IsSet($zaz) && count($zaz) > 0 && in_array($zaznam['id'],$zaz))
			{
				$z=$zaznam['id'];
				$prihl = '<span class="Highlight">'.$zav[$z].'</span>';
			}
			else
				$prihl = '-';
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
				echo $data_tbl->get_new_row($datum,$nazev,$zaznam['misto'],$oddil,$typ,$odkaz,$prihl,$boss);
			else
				echo $data_tbl->get_new_row($datum,$nazev,$zaznam['misto'],$oddil,$typ,$odkaz,$boss);
		}
		else if(SHOW_USER)
			echo $data_tbl->get_new_row($datum,$nazev,$zaznam['misto'],$oddil,$typ,$odkaz,$prihl);
		else
			echo $data_tbl->get_new_row($datum,$nazev,$zaznam['misto'],$oddil,$typ,$odkaz);
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
DrawPageSubTitle('Nejbližší pøihlášky (do'.GC_SHOW_REG_DAYS.' dní)');

$d1 = $curr_date;
$d2 = IncDate($curr_date,GC_SHOW_REG_DAYS);
$query = 'SELECT id, datum, datum2, nazev, typ, ranking, odkaz, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5, vicedenni, misto, oddil, vedouci FROM '.TBL_RACE.' WHERE ((prihlasky1 >= '.$d1.' && prihlasky1 <= '.$d2.') || (prihlasky2 >= '.$d1.' && prihlasky2 <= '.$d2.') || (prihlasky3 >= '.$d1.' && prihlasky3 <= '.$d2.') || (prihlasky4 >= '.$d1.' && prihlasky4 <= '.$d2.') || (prihlasky5 >= '.$d1.' && prihlasky5 <= '.$d2.')) ORDER BY datum';
@$vysledek=MySQL_Query($query);

if (mysql_num_rows($vysledek) > 0)
{
	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Název',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Poø.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'W',ALIGN_CENTER);
	if(SHOW_USER)
		$data_tbl->set_header_col($col++,'Pøihlášen',ALIGN_CENTER);
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

		$nazev = "<A href=\"javascript:open_race_info(".$zaznam['id'].")\" class=\"adr_name\">".$zaznam['nazev']."</A>";
		$oddil = $zaznam['oddil'];
		$typ = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\">".GetRaceTypeImg($zaznam['typ']).'</A>';
		$odkaz = GetRaceLinkHTML($zaznam['odkaz']);
		if(SHOW_USER)
		{
			if (IsSet($zaz) && count($zaz) > 0 && in_array($zaznam['id'],$zaz))
			{
				$z=$zaznam["id"];
				$prihl = '<span class="Highlight">'.$zav[$z].'</span>';
			}
			else
				$prihl = '-';
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
				echo $data_tbl->get_new_row($datum,$nazev,$oddil,$typ,$odkaz,$prihl,$termin,$boss);
			else
				echo $data_tbl->get_new_row($datum,$nazev,$oddil,$typ,$odkaz,$termin,$boss);
		}
		else if(SHOW_USER)
			echo $data_tbl->get_new_row($datum,$nazev,$oddil,$typ,$odkaz,$prihl,$termin);
		else
			echo $data_tbl->get_new_row($datum,$nazev,$oddil,$typ,$odkaz,$termin);
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