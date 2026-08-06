<?

function _DateDiffInDays(&$date1,&$date2)
{
	$diff = (int)($date1 - $date2);
	$diff = _todays($diff);
	$diff = abs($diff);
	return $diff;
}

function _GetOldReg($zaznam,$curr_date)
{
	if($zaznam['prihlasky'] == 1)
	{
		return ($zaznam['prihlasky1'] < $curr_date ) ?  array($zaznam['prihlasky1'],0) : array(0,0);
	}
	else
	{
		if ($zaznam['prihlasky'] >= 5 && $zaznam['prihlasky5'] < $curr_date )
			return array($zaznam['prihlasky5'],5);
		if ($zaznam['prihlasky'] >= 4 && $zaznam['prihlasky4'] < $curr_date )
			return array($zaznam['prihlasky4'],4);
		if ($zaznam['prihlasky'] >= 3 && $zaznam['prihlasky3'] < $curr_date )
			return array($zaznam['prihlasky3'],3);
		if ($zaznam['prihlasky'] >= 2 && $zaznam['prihlasky2'] < $curr_date )
			return array($zaznam['prihlasky2'],2);
		if ($zaznam['prihlasky'] >= 1 && $zaznam['prihlasky1'] < $curr_date )
			return array($zaznam['prihlasky1'],1);
		return array(0,0);
	}
	return array(0,0);
}

function _GetNewReg(&$zaznam,$curr_date)
{
	if($zaznam['prihlasky'] == 1)
	{
		return ($zaznam['prihlasky1'] >= $curr_date ) ? array($zaznam['prihlasky1'],0) : array(0,0);
	}
	else
	{
		if ($zaznam['prihlasky'] >= 1 && $zaznam['prihlasky1'] >= $curr_date )
			return array($zaznam['prihlasky1'],1);
		if ($zaznam['prihlasky'] >= 2 && $zaznam['prihlasky2'] >= $curr_date )
			return array($zaznam['prihlasky2'],2);
		if ($zaznam['prihlasky'] >= 3 && $zaznam['prihlasky3'] >= $curr_date )
			return array($zaznam['prihlasky3'],3);
		if ($zaznam['prihlasky'] >= 4 && $zaznam['prihlasky4'] >= $curr_date )
			return array($zaznam['prihlasky4'],4);
		if ($zaznam['prihlasky'] >= 5 && $zaznam['prihlasky5'] >= $curr_date )
			return array($zaznam['prihlasky5'],5);
		return array(0,0);
	}
	return array(0,0);
}

function _GetRegsColors($reg,$curr_date)
{
	$class = 'center';
	if($reg != 0)
	{
		$diff = _DateDiffInDays($reg,$curr_date);
		if ($diff < 3)
			$class = 'center_alert2';
		else if ($diff < 8)
			$class = 'center_alert7';
		else if ($diff < 22)
			$class = 'center_alert21';
	
	}
	else
	{
		$class = 'center_gray';
	}
	return $class;
}

function _GetOldRegClass($zaznam,$curr_date)
{
	$reg = _GetOldReg($zaznam,$curr_date);
	$rg = (is_array($reg)) ? $reg[0] : $reg;
	return _GetRegsColors($rg,$curr_date);
}

function _GetNewRegClass(&$zaznam,$curr_date)
{
	$reg = _GetNewReg($zaznam,$curr_date);
	$rg = (is_array($reg)) ? $reg[0] : $reg;
	return _GetRegsColors($rg,$curr_date);
}

function _Reg2Str($reg)
{
	if(is_array($reg))
	{
		if ($reg[0] != 0)
			return ($reg[1] != 0) ? Date2String($reg[0]).' / '.$reg[1] : Date2String($reg[0]);
		else
			return '-'; 
	}
	else
		return Date2String($reg);
}

function Term_list(&$zaznam)
{
	if($zaznam['prihlasky'] == 0 || $zaznam['prihlasky1'] == 0)
	{
		return '-';
	}
	else if($zaznam['prihlasky'] == 1)
	{
		return _Reg2Str($zaznam['prihlasky1']);
	}
	else
	{
		$export = _Reg2Str(array($zaznam['prihlasky1'],1));
		if ($zaznam['prihlasky'] >= 2 )
			$export .= ' | '._Reg2Str(array($zaznam['prihlasky2'],2));
		if ($zaznam['prihlasky'] >= 3 )
			$export .= ' | '._Reg2Str(array($zaznam['prihlasky3'],3));
		if ($zaznam['prihlasky'] >= 4 )
			$export .= ' | '._Reg2Str(array($zaznam['prihlasky4'],4));
		if ($zaznam['prihlasky'] >= 5 )
			$export .= ' | '._Reg2Str(array($zaznam['prihlasky5'],5));

		return $export;
	}
}


?>