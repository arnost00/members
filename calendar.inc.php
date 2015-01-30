<?

$g_daylist = array();
//	Example :
//	$g_daylist[1900][12][31] = true;

function _GetIsLeapYear($year)
{
	return (bool)(	($year % 4 == 0 && $year % 100 != 0) || $year % 400 == 0);
}

function _GetDaysInMonth($month,$year)
{
	if($month == 2)
		return (_GetIsLeapYear($year)) ? 29 : 28;
	else if($month == 4 or $month == 6 or $month == 9 or $month == 11)
		return 30;
	else
		return 31;
}

function _GetDayOfWeek($day,$month,$year)
{	// Returns day of week for given date, 0=Sunday
	if($month > 2)
		$month -= 2;
	else
	{
		$month += 10;
		$year--;
	}

	$day = ( floor((13 * $month - 1) / 5) + 
	$day + ($year % 100) +
	floor(($year % 100) / 4) +
	floor(($year / 100) / 4) - 2 *
	floor($year / 100) + 77);

	$weekday_number = (($day - 7 * floor($day / 7)));

	return $weekday_number;
}

function _GetMonthName($m)
{
	$arr_months = array(1=>'leden','únor','březen','duben','květen','červen','červenec','srpen','září','říjen','listopad','prosinec');
	return $arr_months[$m];
}

function GetMonthCalendar($month,$year)
{
	if($month < 1 || $month > 12)
		return '';

	global $g_daylist;
	$arr_days = array('Po','Út','St','Čt','Pá','So','Ne');

	$curr = false;
	$curr_day = 0;
	$da = explode (".",GetCurrentDateString(true));
	if (sizeof($da) == 3)
	{
		$curr = ($year == $da[2]) && ($month == $da[1]);
		$curr_day = $da[0];
	}

	$start_cell = _GetDayOfWeek(1,$month,$year);
	$start_cell = ($start_cell == 0) ? $start_cell = 6 : $start_cell-1;
	$days = _GetDaysInMonth($month,$year);
//	echo('<TABLE cellpadding="0" cellspacing="0" border="0" class="calendar">');
	echo('<TABLE class="calendar">');
	echo('<TR><TD colspan="7" class="header">'._GetMonthName($month).' '.$year.'</TD></TR>');
	echo('<TR>');
	for($i=0;$i<7;$i++)
		echo('<TD class="days">'.$arr_days[$i].'</TD>');
	echo('</TR>');

	$cday = 0;
	$col = $start_cell;
	echo('<TR>');
	if($start_cell > 0)
		echo('<TD colspan="'.$start_cell.'" class="empty">&nbsp;</TD>');
	while($cday < $days)
	{
		$cday++;
		$cd_zav = @$g_daylist[$year][$month][$cday];
		if($cd_zav == true)
			echo('<TD class="race">');
		else if($curr && $cday == $curr_day)
			echo('<TD class="today">');
		else if($col > 4)
			echo('<TD class="weekend">');
		else
			echo('<TD>');
		echo($cday);
		echo('</TD>');
		if($col == 6)
		{
			$col = 0;
			if($cday != $days)
				echo('</TR><TR>');
		}
		else
			$col++;
	}
	if($col != 6 && $col != 0)
		echo('<TD colspan="'.(7-$col).'" class="empty">&nbsp;</TD>');
	echo('</TR>');
	echo('</TABLE>');
}

?>