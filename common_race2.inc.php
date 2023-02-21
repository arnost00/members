<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

class raceterms
{
/////////////////////////////////////////////////////////////////////////

public static function GetCurr4RegTerm(&$zaznam)
// For reg/unreg
// 1 .. 5 - active term
// 0 - any active term / cannot process
{	
	if(GetTimeToRace($zaznam['datum']) <= 0)
		return 0;
	if($zaznam['prihlasky'] == 0)
		return 1;
	if (GetTimeToReg($zaznam['prihlasky1']) != -1 )
		return 1;
	if ($zaznam['prihlasky'] > 1 && GetTimeToReg($zaznam['prihlasky2']) != -1 )
		return 2;
	if ($zaznam['prihlasky'] > 2 && GetTimeToReg($zaznam['prihlasky3']) != -1 )
		return 3;
	if ($zaznam['prihlasky'] > 3 && GetTimeToReg($zaznam['prihlasky4']) != -1 )
		return 4;
	if ($zaznam['prihlasky'] > 4 && GetTimeToReg($zaznam['prihlasky5']) != -1 )
		return 5;
	return 0;
}
/////////////////////////////////////////////////////////////////////////

public static function GetMngCurr4RegIdx(&$zaznam)
{	//?
	return 0;
}

public static function GetRegCurr4RegIdx(&$zaznam)
{	//?
	return 0;
}
/////////////////////////////////////////////////////////////////////////

public static function GetCurr4InfoIdx(&$zaznam)
{	//?
	return 0;
}
/////////////////////////////////////////////////////////////////////////

public static function GetActiveRegDate(&$zaznam)
{
	if ($zaznam['prihlasky'] == 0)
		return 0;
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
	return 0;	// safety return.
}
/////////////////////////////////////////////////////////////////////////

public static function GetActiveRegDateArr(&$zaznam)
//	0. - reg. date
//	1. - termin
{
	if ($zaznam['prihlasky'] == 0)
		return array(0,0);
	if ($zaznam['prihlasky'] == 1 || GetTimeToReg($zaznam['prihlasky1']) != -1 )
		return array($zaznam['prihlasky1'],1);
	if ($zaznam['prihlasky'] == 2 || GetTimeToReg($zaznam['prihlasky2']) != -1 )
		return array($zaznam['prihlasky2'],2);
	if ($zaznam['prihlasky'] == 3 || GetTimeToReg($zaznam['prihlasky3']) != -1 )
		return array($zaznam['prihlasky3'],3);
	if ($zaznam['prihlasky'] == 4 || GetTimeToReg($zaznam['prihlasky4']) != -1 )
		return array($zaznam['prihlasky4'],4);
	if ($zaznam['prihlasky'] == 5 || GetTimeToReg($zaznam['prihlasky5']) != -1 )
		return array($zaznam['prihlasky5'],5);
	return array(0,0);	// safety return.
}

/////////////////////////////////////////////////////////////////////////

public static function GetActiveRegDateArrPrev(&$zaznam)
//	0. - reg. date
//	1. - termin
{
	if (($zaznam['prihlasky'] == 0) || ($zaznam['prihlasky'] == 1 || GetTimeToReg($zaznam['prihlasky1']) != -1 ))
		return array(0,0);
	if ($zaznam['prihlasky'] == 2 || GetTimeToReg($zaznam['prihlasky2']) != -1 )
		return array($zaznam['prihlasky1'],1);
	if ($zaznam['prihlasky'] == 3 || GetTimeToReg($zaznam['prihlasky3']) != -1 )
		return array($zaznam['prihlasky2'],2);
	if ($zaznam['prihlasky'] == 4 || GetTimeToReg($zaznam['prihlasky4']) != -1 )
		return array($zaznam['prihlasky3'],3);
	if ($zaznam['prihlasky'] == 5 || GetTimeToReg($zaznam['prihlasky5']) != -1 )
		return array($zaznam['prihlasky4'],4);
	return array(0,0);	// safety return.
}
/////////////////////////////////////////////////////////////////////////
public static function ListRegDates(&$zaznam)
{
	if($zaznam['prihlasky'] == 0)
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
		if ($result != null)
			return implode(' | ',$result);
		else
			return '';
	}
	else
		return Date2String($zaznam['prihlasky1']);
	return '';	// safety return.
}

/////////////////////////////////////////////////////////////////////////

public static function ColorizeTermUser($time_to_reg,$prihlasky_curr,$prihlasky_text)
{
	if ($prihlasky_curr != 0 && $time_to_reg < 22)
	{
		if ($time_to_reg < 0)
			$termin = '<span class="TextAlertExp">';
		else if ($time_to_reg < 3)
			$termin = '<span class="TextAlert2">';
		else if ($time_to_reg < 8)
			$termin = '<span class="TextAlert7">';
		else
			$termin = '<span class="TextAlert21">';
		$termin .= $prihlasky_text;
		$termin .= "</span>";
	}
	else if($prihlasky_curr[0] != 0)
		$termin = $prihlasky_text;
	else
		$termin = '-';
	return $termin;
}
/////////////////////////////////////////////////////////////////////////


}

?>