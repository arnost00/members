<?php if (!defined('__HIDE_TEST__')) exit; /* zamezeni samostatneho vykonani */ ?>
<?
function CheckIfLoginIsValid($new_login,$curr_id)
{
	@$vysl=query_db("SELECT id,login FROM ".TBL_ACCOUNT." WHERE login = '$new_login' LIMIT 1");
	if (mysqli_num_rows ($vysl) == 0)
	{
		return true;
	}
	else
	{
		$zazn=MySQLi_Fetch_Array($vysl);
		if ($zazn["id"] == $curr_id)
			return true;
		else
			return false;
	}
}

function RegNumToStr($reg_num)
{
	$rg = (string)$reg_num;
	while (strlen($rg) < 4 )
	{
		$rg = '0'.$rg;
	}
	return $rg;
}

function RegNumToStrEx($reg_num,$reg_num2,$is_mtbo)
{
	$rn = ($is_mtbo && $reg_num2 != 0) ? $reg_num2 : $reg_num;
	$rg = (string)$rn;
	while (strlen($rg) < 4 )
	{
		$rg = '0'.$rg;
	}
	return $rg;
}

function SINumToStr($si_num)
{
	$si = (string)$si_num;
	if ($si_num != 0)
	{
		while (strlen($si) < 5 )
		{
			$si = '0'.$si;
		}
	}
	return $si;
}



?>