<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

$g_zebricek [0]['id'] = 0x0001;
$g_zebricek [0]['nm'] = 'Celostátní';
$g_zebricek [1]['id'] = 0x0002;
$g_zebricek [1]['nm'] = 'Morava';
$g_zebricek [2]['id'] = 0x0004;
$g_zebricek [2]['nm'] = 'Čechy';
$g_zebricek [3]['id'] = 0x0008;
$g_zebricek [3]['nm'] = 'Oblastní';
$g_zebricek [4]['id'] = 0x0010;
$g_zebricek [4]['nm'] = 'Mistrovství';
$g_zebricek [5]['id'] = 0x0020;
$g_zebricek [5]['nm'] = 'Štafety';
$g_zebricek [6]['id'] = 0x0080;
$g_zebricek [6]['nm'] = 'Veřejný';

$g_zebricek_cnt = 7;

$g_racetype [0]['id'] = 0x0001;
$g_racetype [0]['nm'] = 'OB';
$g_racetype [0]['enum'] = 'ob';
$g_racetype [0]['img'] = 'fot';
$g_racetype [1]['id'] = 0x0002;
$g_racetype [1]['nm'] = 'MTBO';
$g_racetype [1]['enum'] = 'mtbo';
$g_racetype [1]['img'] = 'mbo';
$g_racetype [2]['id'] = 0x0004;
$g_racetype [2]['nm'] = 'LOB';
$g_racetype [2]['enum'] = 'lob';
$g_racetype [2]['img'] = 'ski';
$g_racetype [3]['id'] = 0x0008;
$g_racetype [3]['nm'] = 'O-Trail';
$g_racetype [3]['enum'] = 'trail';
$g_racetype [3]['img'] = 'trl';
$g_racetype [4]['id'] = 0x0010;
$g_racetype [4]['nm'] = 'Jiné';
$g_racetype [4]['enum'] = 'jine';
$g_racetype [4]['img'] = 'mcs';

$g_racetype_cnt = 5;

$g_modify_flag [0]['id'] = 0x0001;
$g_modify_flag [0]['nm'] = 'Termín přihlášek';
$g_modify_flag [1]['id'] = 0x0002;
$g_modify_flag [1]['nm'] = 'Závod přidán';
$g_modify_flag [2]['id'] = 0x0004;
$g_modify_flag [2]['nm'] = 'Termin závodu';

$g_modify_flag_cnt = 3;

$g_racetype0 = array(
	'Z' => 'Závod',
	'T' => 'Trénink',
	'S' => 'Soustředění',
	'V' => 'Sportovní vyšetření',
	'N' => 'Nákup oblečení',
	'J' => 'Jiné'
);

$g_racetype0_idx[0] = 'Z';
$g_racetype0_idx[1] = 'T';
$g_racetype0_idx[2] = 'S';
$g_racetype0_idx[3] = 'V';
$g_racetype0_idx[4] = 'N';
$g_racetype0_idx[5] = 'J';

$g_racetype0_cnt = 6; 

function GetRaceTypeName($value)
{
	global $g_racetype_cnt;
	global $g_racetype;

	for($ii=0; $ii<$g_racetype_cnt; $ii++)
	{
		if($value == $g_racetype [$ii]['enum'])
			return $g_racetype [$ii]['nm'];
	}
	return '-';
}

function GetRaceTypeNameSpec($value)
{
	global $g_racetype_cnt;
	global $g_racetype;

	$result = '';
	for($ii=0; $ii<$g_racetype_cnt; $ii++)
	{
		if(($value & $g_racetype [$ii]['id']) != 0)
			$result .= $g_racetype [$ii]['nm'].', ';
	}
	if(strlen($result) > 0)
		return substr($result,0,strlen($result)-2);
	else
		return '-';
}

function GetRaceTypeImg(&$value)
{
	global $g_racetype_cnt;
	global $g_racetype;

	for($ii=0; $ii<$g_racetype_cnt; $ii++)
	{
		if($value == $g_racetype [$ii]['enum'])
		{
			return '<img src="imgs/'.$g_racetype [$ii]['img'].'16.gif" width="16" height="16" alt='.$g_racetype [$ii]['nm'].'>';
		}
	}
	return '?';
}


function GetRaceType0($value)
{
	global $g_racetype0;
	$v2 = $g_racetype0[$value];
	if ($v2 == NULL)
		$v2 = $value;

//	return '<span class="type0" style="cursor:help" title="'.$v2.'">'.$value.'</span>';
	return '<span class="type0_'.$value.'" style="cursor:help" title="'.$v2.'">'.$value.'</span>';
}

function GetRaceType0Name($value)
{
	global $g_racetype0;
	$v2 = $g_racetype0[$value];
	if ($v2 == NULL)
		$v2 = $value;

	return $v2;
}

function GetZebricekName2($value)
{
	global $g_zebricek_cnt;
	global $g_zebricek;

	$result = '';
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		if(($value & $g_zebricek [$ii]['id']) != 0)
			$result .= $g_zebricek [$ii]['nm'].', ';
	}
	if(strlen($result) > 0)
		return substr($result,0,strlen($result)-2);
	else
		return '-';
}

function CreateZebricekNumber(&$zebricek)
{
	global $g_zebricek_cnt;
	global $g_zebricek;

	$result = 0;
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		if(isset($zebricek[$ii]) && $zebricek[$ii] == 1)
			$result += $g_zebricek [$ii]['id'];
	}
	return $result;
}

function CreateRaceTypeNumber(&$racetype)
{
	global $g_racetype_cnt;
	global $g_racetype;

	$result = 0;
	for($ii=0; $ii<$g_racetype_cnt; $ii++)
	{
		if(isset($racetype[$ii]) && $racetype[$ii] == 1)
			$result += $g_racetype [$ii]['id'];
	}
	return $result;
}

function CreateModifyFlag(&$mflags)
{
	global $g_modify_flag_cnt;
	global $g_modify_flag;

	$result = 0;
	for($ii=0; $ii<$g_modify_flag_cnt; $ii++)
	{
		if(isset($mflags[$ii]) && $mflags[$ii] == 1)
			$result += $g_modify_flag [$ii]['id'];
	}
	return $result;
}

function GetModifyFlagDesc(&$mflags)
{
	global $g_modify_flag_cnt;
	global $g_modify_flag;

	$result = '';
	for($ii=0; $ii<$g_modify_flag_cnt; $ii++)
	{
		if(($mflags & $g_modify_flag[$ii]['id']) != 0)
			$result .= (($result != '')?', ':'').$g_modify_flag [$ii]['nm'];
	}
	return $result;
}

function GetLicence($licF,$licM,$licL,$type)
{
	switch($type)	// 2 - MTBO, 1 - LOB, 0 - OB
	{
		case 2:
			return $licM;
			break;
		case 1:
			return $licL;
			break;
		case 0:
		default:
			return $licF;
	}
}

require_once('./common_race2.inc.php');

function RaceInfoTable(&$zaznam,$add_row = '',$show_curr_term = false, $full_width=false, $expandable=false)
//	$show_curr_term = 0 - nic, 1 - us,mng,smn, 2 - rg,ad
{
	global $g_enable_race_boss, $g_enable_race_accommodation, $g_enable_race_transport;

	if ($expandable)
	{
?>
<script language="JavaScript">
function RIT_SH(divId1, divId2)
{
	if(document.getElementById(divId1).style.display == 'none')
		document.getElementById(divId1).style.display='block';
	else
		document.getElementById(divId1).style.display = 'none';

	if(document.getElementById(divId2).style.display == 'none')
		document.getElementById(divId2).style.display='block';
	else
		document.getElementById(divId2).style.display = 'none';
}
</script>
<div id="RIT_min" style="display: block" >
<?
		$data_tbl = new html_table_nfo;
		if($full_width)
			$data_tbl->table_width = 100;
		echo $data_tbl->get_css()."\n";
		echo $data_tbl->get_header()."\n";
		$odkaz = '<a onclick ="javascript:RIT_SH(\'RIT_min\',\'RIT_normal\')" href="javascript:;" ><code>[+]</code></a>'; //Zobrazit více
		if($zaznam['vicedenni'])
			echo $data_tbl->get_new_row_extend('Datum',Date2StringFT($zaznam['datum'],$zaznam['datum2']),$odkaz);
		else
			echo $data_tbl->get_new_row_extend('Datum',Date2String($zaznam['datum']),$odkaz);
		echo $data_tbl->get_new_row('Jméno',GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']));
		echo $data_tbl->get_footer()."\n";
		echo ('</div><div id="RIT_normal" style="display: none">');
		$odkaz2 = '<a onclick ="javascript:RIT_SH(\'RIT_normal\',\'RIT_min\')" href="javascript:;" ><code>[-]</code></a>'; // Skrýt podrobnosti
	}
	else
		$odkaz2 = '';
	$data_tbl = new html_table_nfo;
	if($full_width)
		$data_tbl->table_width = 100;
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	if($g_enable_race_boss)
	{
		$vedouci = '-';
		if($zaznam['vedouci'] != 0)
		{
			@$vysledekU=query_db("SELECT jmeno,prijmeni FROM ".TBL_USER." WHERE id = '".$zaznam['vedouci']."' LIMIT 1");
			@$zaznamU=mysqli_fetch_array($vysledekU);
			if($zaznamU != FALSE)
				$vedouci = $zaznamU['jmeno'].' '.$zaznamU['prijmeni'];
		}
	}
	$datum = ($zaznam['vicedenni']) ? Date2StringFT($zaznam['datum'],$zaznam['datum2']) : Date2String($zaznam['datum']);
	if($odkaz2)
		echo $data_tbl->get_new_row_extend('Datum',$datum,$odkaz2);
	else
		echo $data_tbl->get_new_row('Datum',$datum);
	echo $data_tbl->get_new_row('Jméno',GetFormatedTextDel($zaznam['nazev'], $zaznam['cancelled']));
	echo $data_tbl->get_new_row('Místo',GetFormatedTextDel($zaznam['misto'], $zaznam['cancelled']));
	echo $data_tbl->get_new_row('Pořádající oddíl',$zaznam['oddil']);
	echo $data_tbl->get_new_row('Typ akce',GetRaceType0Name($zaznam['typ0']));
	echo $data_tbl->get_new_row('Sport',GetRaceTypeName($zaznam['typ']));
	echo $data_tbl->get_new_row('Žebříček',GetZebricekName2($zaznam['zebricek']));
	echo $data_tbl->get_new_row('Ranking',($zaznam['ranking'] == 1) ? 'Ano' : 'Ne');
	echo $data_tbl->get_new_row('WWW stránky',GetRaceLinkHTML($zaznam['odkaz'],false));
	if($zaznam['vicedenni'])
	{
		echo $data_tbl->get_new_row('Počet etap',$zaznam['etap']);
	}
	if($zaznam['prihlasky'] > 1)
	{
		echo $data_tbl->get_new_row('Termínů přihlášek',$zaznam['prihlasky']);
		if($show_curr_term)
		{
			$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
			$tp = ($prihlasky_curr[0] != 0) ? Date2String($prihlasky_curr[0]).' - termím č.'.$prihlasky_curr[1] : 'není';
			echo $data_tbl->get_new_row('Aktivní termín',$tp);
		}
		echo $data_tbl->get_new_row('Termíny přihlášek',raceterms::ListRegDates($zaznam));
	}
	else
		echo $data_tbl->get_new_row('Termín přihlášek',Date2String($zaznam['prihlasky1']));
	if(IsLoggedRegistrator())
	{
		if($zaznam['send'] > 0)
		{
			if($zaznam['prihlasky'] > 1)
				$send = $zaznam['send'].'.termín';
			else
				$send = 'Ano';
		}
		else
			$send = 'Ne';

		echo $data_tbl->get_new_row('Přihláška odeslána',$send);
	}
	if($g_enable_race_boss)
		echo $data_tbl->get_new_row('Vedoucí',$vedouci);
	if(is_array($add_row))
		echo $data_tbl->get_new_row($add_row[0],$add_row[1]);
	if ($g_enable_race_transport)
	{
		switch($zaznam['transport'])
		{
		case 0: 
			$transport = 'Ne';
			break;
		case 1: 
			$transport = 'Ano';
			break;
		case 2: 
			$transport = 'Ano - automaticky';
			break;
		}
		echo $data_tbl->get_new_row('Společná doprava',$transport);
	}
	if ($g_enable_race_accommodation)
	{
		switch($zaznam['ubytovani'])
		{
		case 0: 
			$ubytovani = 'Ne';
			break;
		case 1: 
			$ubytovani = 'Ano';
			break;
		case 2: 
			$ubytovani = 'Ano - automaticky';
			break;
		}
		echo $data_tbl->get_new_row('Společné ubytování',$ubytovani);
	}

	echo $data_tbl->get_footer()."\n";
	if ($expandable)
	{
		echo ('</div>');
	}
}

function form_filter_racelist($page,&$filterA,&$filterB,&$filterC,&$filterD)
{
	global $g_zebricek_cnt;
	global $g_zebricek;
	global $g_racetype0_cnt;
	global $g_racetype0;
	global $g_racetype0_idx;

	$urlA = "'./".$page.'&fB='.$filterB.'&fC='.$filterC.'&fD='.$filterD.'&fA=\'';
	$urlB = "'./".$page.'&fA='.$filterA.'&fC='.$filterC.'&fD='.$filterD.'&fB=\'';
	$urlC = "'./".$page.'&fA='.$filterA.'&fB='.$filterB.'&fD='.$filterD.'&fC=\'';
	$urlD = "'./".$page.'&fA='.$filterA.'&fB='.$filterB.'&fC='.$filterC.'&fD=\'';
	$filter_arr_niceA = array(0=>'všechny',1=>'jen OB',2=>'jen MTBO',3=>'jen LOB',4=>'jen nezařazené');
	$filter_arr_sqlA = array(1=>'ob',2=>'mtbo',3=>'lob',4=>'jine');
	if($filterA > 0 && $filterA < 5)
		$result = ' WHERE `typ`=\''.$filter_arr_sqlA[$filterA]."'";
	else
		$result = '';
	if($filterB > 0 && $filterB <= $g_zebricek_cnt)
	{
		if($result == '')
			$result = ' WHERE (';
		else
			$result .= ' AND (';
		$code = $g_zebricek[$filterB-1]['id'];
		$result .= '`zebricek` & \''.$code."')";
	}
	if($filterC == 0)
	{
		if($result == '')
			$result = ' WHERE (';
		else
			$result .= ' AND (';
		$result .= '`datum` >= \''.GetCurrentDate()."')";
	}
	else if ($filterC == 2)
	{
		if($result == '')
			$result = ' WHERE (';
		else
			$result .= ' AND (';
		$result .= '`datum` >= \''.DecDate(GetCurrentDate(),31)."')";
	}
	if($filterD > 0 && $filterD <= $g_racetype0_cnt)
	{
		if($result == '')
			$result = ' WHERE (';
		else
			$result .= ' AND (';
		$code = $g_racetype0_idx[$filterD-1];
		$result .= '`typ0` = \''.$code."')";
	}
?>
<table><tr><td>
<form>
Typ akcí&nbsp;
<?
	echo('<select name="fD" onchange="javascript:location.replace('.$urlD.'+this.options[this.selectedIndex].value,\'_top\')">'."\n");
	echo('<option value="0"'.(($filterD == 0)? ' selected' : '').'>všechny</option>'."\n");
	$ii = 0;
	foreach ( $g_racetype0 as $key => &$value )
	{
		$ii++; 
		echo("<option value='".$ii."'".(($filterD==$ii)?' selected':'').">".$value."</option>\n");
	}
?>
</select>
</form>
</td><td>&nbsp;&nbsp;</td><td>
<form>
Typ sportů&nbsp;
<?
	echo('<select name="fA" onchange="javascript:location.replace('.$urlA.'+this.options[this.selectedIndex].value,\'_top\')">'."\n");
	for($ii=0; $ii<count($filter_arr_niceA); $ii++)
	{
		echo('<option value="'.$ii.'"'.(($filterA == $ii)? ' selected' : '').'>'.$filter_arr_niceA[$ii].'</option>'."\n");
	}
?>
</select>
</form>
</td><td>&nbsp;&nbsp;</td><td>
<form>
Zařazení závodů&nbsp;
<?
	echo('<select name="fB" onchange="javascript:location.replace('.$urlB.'+this.options[this.selectedIndex].value,\'_top\')">'."\n");
	echo('<option value="0"'.(($filterB == 0)? ' selected' : '').'>všechny</option>'."\n");
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		echo('<option value="'.($ii+1).'"'.(($filterB == $ii+1)? ' selected' : '').'>'.$g_zebricek[$ii]['nm'].'</option>'."\n");
	}
?>
</select>
</form>
</td><td>&nbsp;&nbsp;</td><td valign="top">
<INPUT TYPE="checkbox" NAME="fC" onClick="javascript:location.replace(<? echo($urlC);?>+Number(this.checked),'_top')" id="fC" value="1"<? if ($filterC == 1) echo(' checked');?>><label for="fC">Zobrazit staré závody</label>
<INPUT TYPE="checkbox" NAME="fC2" onClick="javascript:location.replace(<? echo($urlC);?>+Number(this.checked*2),'_top')" id="fC2" value="2"<? if ($filterC == 2) echo(' checked');?>><label for="fC2">jen cca měsíc zpět</label>
</td></tr>
</table>
<br />
<?
	return $result;
}

function show_link_to_actual_race(&$num_rows)
{
	if($num_rows > GC_MIN_RACES_2_SHOW_LINK)
		echo('<a href="#actual_races">Jdi na aktuální závody</a><br>');
}

?>