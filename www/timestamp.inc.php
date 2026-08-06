<?

$global_RT_TS_1 = 0.0;
$global_RT_TS_2 = 0.0;

function _getmicrotime_TS()
{
	return array_sum(explode(' ', microtime()));
}

function _set_global_RT_Start()
{
	global $global_RT_TS_1;
	$global_RT_TS_1 = _getmicrotime_TS();
}

function _set_global_RT_End()
{
	global $global_RT_TS_2;
	$global_RT_TS_2 = _getmicrotime_TS();
}


function _get_global_RT_difference_TS()
{
	global $global_RT_TS_2;
	global $global_RT_TS_1;
	return $global_RT_TS_2-$global_RT_TS_1;
}

function _print_global_RT_difference_TS()
{
	echo ("Generation time is "._get_global_RT_difference_TS()." seconds.");
}

?>