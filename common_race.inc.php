<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

$g_zebricek [0]['id'] = 0x0001;
$g_zebricek [0]['nm'] = 'Celostátní';
$g_zebricek [1]['id'] = 0x0002;
$g_zebricek [1]['nm'] = 'Morava';
$g_zebricek [2]['id'] = 0x0004;
$g_zebricek [2]['nm'] = 'Èechy';
$g_zebricek [3]['id'] = 0x0008;
$g_zebricek [3]['nm'] = 'Oblastní';
$g_zebricek [4]['id'] = 0x0010;
$g_zebricek [4]['nm'] = 'Mistrovství';
$g_zebricek [5]['id'] = 0x0020;
$g_zebricek [5]['nm'] = 'Štafety';
$g_zebricek [6]['id'] = 0x0080;
$g_zebricek [6]['nm'] = 'Veøejný';

$g_zebricek_cnt = 7;

function GetRaceTypeName($value)
{
	switch ($value)
	{
	case 'ob' : return 'OB';
	case 'mtbo' : return 'MTBO';
	case 'lob' : return 'LOB';
	case 'jine': return 'jiné';
	default : return '-';
	}
}

function GetRaceTypeImg(&$value)
{
	$img = '';
	$alt = '';
	switch ($value)
	{
	case 'mtbo' : $img = 'mbo'; $alt = 'MTBO'; break;
	case 'lob' : $img = 'ski'; $alt = 'LOB'; break;
	case 'jine': $img = 'mcs'; $alt = 'jiné'; break;
	case 'ob' :
	default : $img = 'fot'; $alt = 'OB';
	}
	return '<img src="imgs/'.$img.'16.gif" width="16" height="16" alt='.$alt.'>';
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
		if($zebricek[$ii] == 1)
			$result += $g_zebricek [$ii]['id'];
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

$g_kategorie ['oblz'] = 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;';
$g_kategorie ['oblz_vetsi'] = 'D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;H10N;H12C;H14C;H16C;H18C;H21C;H21D;H35C;H45C;H55C;HDR;';
$g_kategorie ['becka'] = 'D12B;D14B;D16B;D18B;D20B;D21B;D21C;D35B;D40B;D45B;D50B;D55B;D60B;D65B;H12B;H14B;H16B;H18B;H20B;H21B;H21C;H35B;H40B;H45B;H50B;H55B;H60B;H65B;H70B;H75B;';
$g_kategorie ['acka'] = 'D16A;D18A;D20A;D21A;D21E;H16A;H18A;H20A;H21A;H21E;';
$g_kategorie ['stafety'] = 'D14;D18;D21;D105;D140;H14;H18;H21;H105;H140;H165;dorost;dospìlí;HD175;HD235;';
$g_kategorie ['MTBO'] = 'HE;DE;H19-39A;H19-39B;D;H14-18;D14-18;H40;';

require('./common_race2.inc.php');

function RaceInfoTable(&$zaznam,$add_row = '',$show_curr_term = false,$full_width=false)
//	$show_curr_term = 0 - nic, 1 - us,mng,smn, 2 - rg,ad
{
	global $g_enable_race_boss;

	$data_tbl = new html_table_nfo;
	$data_tbl->enable_row_select = false;
	if($full_width)
		$data_tbl->table_width = 100;
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";
	if($g_enable_race_boss)
	{
		$vedouci = '-';
		if($zaznam['vedouci'] != 0)
		{
			@$vysledekU=MySQL_Query("SELECT jmeno,prijmeni FROM ".TBL_USER." WHERE id = '".$zaznam['vedouci']."' LIMIT 1");
			@$zaznamU=MySQL_Fetch_Array($vysledekU);
			if($zaznamU != FALSE)
				$vedouci = $zaznamU['jmeno'].' '.$zaznamU['prijmeni'];
		}
	}

	if($zaznam['vicedenni'])
	{
		echo $data_tbl->get_new_row('Datum od',Date2String($zaznam['datum']));
		echo $data_tbl->get_new_row('Datum do',Date2String($zaznam['datum2']));
	}
	else
		echo $data_tbl->get_new_row('Datum',Date2String($zaznam['datum']));
	echo $data_tbl->get_new_row('Jméno',$zaznam['nazev']);
	echo $data_tbl->get_new_row('Místo',$zaznam['misto']);
	echo $data_tbl->get_new_row('Poøádající oddíl',$zaznam['oddil']);
	echo $data_tbl->get_new_row('Typ',GetRaceTypeName($zaznam['typ']));
	echo $data_tbl->get_new_row('Žebøíèek',GetZebricekName2($zaznam['zebricek']));
	echo $data_tbl->get_new_row('Ranking',($zaznam['ranking'] == 1) ? 'Ano' : 'Ne');
	echo $data_tbl->get_new_row('WWW stránky',GetRaceLinkHTML($zaznam['odkaz'],false));
	if($zaznam['vicedenni'])
	{
		echo $data_tbl->get_new_row('Poèet etap',$zaznam['etap']);
	}
	if($zaznam['prihlasky'] > 1)
	{
		echo $data_tbl->get_new_row('Termínù pøihlášek',$zaznam['prihlasky']);
		if($show_curr_term)
		{
			$prihlasky_curr = raceterms::GetActiveRegDateArr($zaznam);
			$tp = ($prihlasky_curr[0] != 0) ? Date2String($prihlasky_curr[0]).' - termím è.'.$prihlasky_curr[1] : 'není';
			echo $data_tbl->get_new_row('Aktivní termín',$tp);
		}
		echo $data_tbl->get_new_row('Termíny pøihlášek',raceterms::ListRegDates($zaznam));
	}
	else
		echo $data_tbl->get_new_row('Termín pøihlášek',Date2String($zaznam['prihlasky1']));
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

		echo $data_tbl->get_new_row('Pøihláška odeslána',$send);
	}
	if($g_enable_race_boss)
		echo $data_tbl->get_new_row('Vedoucí',$vedouci);
	if(is_array($add_row))
		echo $data_tbl->get_new_row($add_row[0],$add_row[1]);
	echo $data_tbl->get_footer()."\n";
}

/*
function GetActiveRaceRegDate(&$zaznam)
{
	if(GetTimeToRace($zaznam['datum']) <= 0 || $zaznam['prihlasky'] == 0)
		return 0;
	else if($zaznam['vicedenni'])
	{
		if ($zaznam['prihlasky'] == 1 || GetTimeToReg($zaznam['prihlasky1']) != -1 )
			return $zaznam['prihlasky1'];
		if ($zaznam['prihlasky'] == 2 || GetTimeToReg($zaznam['prihlasky2']) != -1 )
			return $zaznam['prihlasky2'];
		if ($zaznam['prihlasky'] == 3 || GetTimeToReg($zaznam['prihlasky3']) != -1 )
			return $zaznam['prihlasky3'];
		if ($zaznam['prihlasky'] == 4 || GetTimeToReg($zaznam['prihlasky4']) != -1 )
			return $zaznam['prihlasky4'];
		if ($zaznam['prihlasky'] == 5 || GetTimeToReg($zaznam['prihlasky5']) != -1 )
			return $zaznam['prihlasky5'];
	}
	else
		return $zaznam['prihlasky1'];
	return -1;	// safety return.
}

function GetActiveRaceRegDateArr(&$zaznam)
{
	if(GetTimeToRace($zaznam['datum']) <= 0 || $zaznam['prihlasky'] == 0)
		return array(0,0,0);
	else if($zaznam['vicedenni'])
	{
		if ($zaznam['prihlasky'] == 1 || GetTimeToReg($zaznam['prihlasky1']) != -1 )
			return array($zaznam['prihlasky1'],1,($zaznam['prihlasky'] == 1) ? 1 : 0);
		if ($zaznam['prihlasky'] == 2 || GetTimeToReg($zaznam['prihlasky2']) != -1 )
			return array($zaznam['prihlasky2'],2,0);
		if ($zaznam['prihlasky'] == 3 || GetTimeToReg($zaznam['prihlasky3']) != -1 )
			return array($zaznam['prihlasky3'],3,0);
		if ($zaznam['prihlasky'] == 4 || GetTimeToReg($zaznam['prihlasky4']) != -1 )
			return array($zaznam['prihlasky4'],4,0);
		if ($zaznam['prihlasky'] == 5 || GetTimeToReg($zaznam['prihlasky5']) != -1 )
			return array($zaznam['prihlasky5'],5,0);
	}
	else
		return array($zaznam['prihlasky1'],1,1);
	return array(0,0,0);	// safety return.
}

function GetActiveRaceRegDateArrPrev(&$zaznam)
{
	if(GetTimeToRace($zaznam['datum']) <= 0 || $zaznam['prihlasky'] == 0)
		return array(0,0);
	else if($zaznam['vicedenni'])
	{
		if ($zaznam['prihlasky'] == 1 || GetTimeToReg($zaznam['prihlasky1']) != -1 )
			return array(0,0);
		if ($zaznam['prihlasky'] == 2 || GetTimeToReg($zaznam['prihlasky2']) != -1 )
			return array($zaznam['prihlasky1'],1);
		if ($zaznam['prihlasky'] == 3 || GetTimeToReg($zaznam['prihlasky3']) != -1 )
			return array($zaznam['prihlasky2'],2);
		if ($zaznam['prihlasky'] == 4 || GetTimeToReg($zaznam['prihlasky4']) != -1 )
			return array($zaznam['prihlasky3'],3);
		if ($zaznam['prihlasky'] == 5 || GetTimeToReg($zaznam['prihlasky5']) != -1 )
			return array($zaznam['prihlasky4'],4);
	}
	else
		return array(0,0);
	return array(0,0);	// safety return.
}


function GetActiveRaceRegTerm(&$zaznam)
{
	if(GetTimeToRace($zaznam['datum']) <= 0 || $zaznam['prihlasky'] == 0)
		return 0;
	else if($zaznam['vicedenni'])
	{
		if ($zaznam['prihlasky'] == 1 || GetTimeToReg($zaznam['prihlasky1']) != -1 )
			return 1;
		if ($zaznam['prihlasky'] == 2 || GetTimeToReg($zaznam['prihlasky2']) != -1 )
			return 2;
		if ($zaznam['prihlasky'] == 3 || GetTimeToReg($zaznam['prihlasky3']) != -1 )
			return 3;
		if ($zaznam['prihlasky'] == 4 || GetTimeToReg($zaznam['prihlasky4']) != -1 )
			return 4;
		if ($zaznam['prihlasky'] == 5 || GetTimeToReg($zaznam['prihlasky5']) != -1 )
			return 5;
	}
	else
		return 1;
	return 0;	// safety return.
}

function ListRaceRegDates(&$zaznam)
{
	if(GetTimeToRace($zaznam['datum']) <= 0 || $zaznam['prihlasky'] == 0)
		return '';
	else if($zaznam['prihlasky'] > 1)
	{
		if ($zaznam['prihlasky1'] != 0)
			$result[] = Date2String($zaznam['prihlasky1']);
		if ($zaznam['prihlasky2'] != 0)
			$result[] = Date2String($zaznam['prihlasky2']);
		if ($zaznam['prihlasky3'] != 0)
			$result[] = Date2String($zaznam['prihlasky3']);
		if ($zaznam['prihlasky4'] != 0)
			$result[] = Date2String($zaznam['prihlasky4']);
		if ($zaznam['prihlasky5'] != 0)
			$result[] = Date2String($zaznam['prihlasky5']);
		return implode(' | ',$result);
	}
	else
		return Date2String($zaznam['prihlasky1']);
	return '';	// safety return.
}
*/
function form_filter_racelist($page,&$filterA,&$filterB)
{
	global $g_zebricek_cnt;
	global $g_zebricek;

	$urlA = "'./".$page.'&fB='.$filterB.'&fA=\'';
	$urlB = "'./".$page.'&fA='.$filterA.'&fB=\'';
	$filter_arr_niceA = array(0=>'všechny',1=>'jen OB',2=>'jen MTBO',3=>'jen LOB',4=>'jen nezaøazené');
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
?>
<table><tr><td>
<form>
Typ zobrazených závodù&nbsp;
<?
	echo('<select name="fA" onchange="javascript:window.open('.$urlA.'+this.options[this.selectedIndex].value,\'_top\')">'."\n");
	for($ii=0; $ii<count($filter_arr_niceA); $ii++)
	{
		echo('<option value="'.$ii.'"'.(($filterA == $ii)? ' selected' : '').'>'.$filter_arr_niceA[$ii].'</option>'."\n");
	}
?>
</select>
</form>
</td><td>&nbsp;&nbsp;</td><td>
<form>
Zaøazení zobrazených závodù&nbsp;
<?
	echo('<select name="fB" onchange="javascript:window.open('.$urlB.'+this.options[this.selectedIndex].value,\'_top\')">'."\n");
	echo('<option value="0"'.(($filterB == 0)? ' selected' : '').'>všechny</option>'."\n");
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		echo('<option value="'.($ii+1).'"'.(($filterB == $ii+1)? ' selected' : '').'>'.$g_zebricek[$ii]['nm'].'</option>'."\n");
	}
?>
</select>
</form>
</td></tr>
</table>
<?
	return $result;
}

function show_link_to_actual_race(&$num_rows)
{
	if($num_rows > GC_MIN_RACES_2_SHOW_LINK)
		echo('<a href="#actual_races">Jdi na aktuální závody</a><br>');
}

?>