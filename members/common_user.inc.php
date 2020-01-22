<?php if (!defined('__HIDE_TEST__')) exit; /* zamezeni samostatneho vykonani */ ?>
<?
function GetUserAccountId_Users($users_id)	// get id_accounts from "usxus" for id_users == x
{
	global $db_conn;
	
	$query = "SELECT id_accounts FROM ".TBL_USXUS." WHERE id_users = '$users_id' LIMIT 1";
	$vysl=query_db($query);
	$zazn=mysqli_fetch_array($vysl);
	if ($zazn != FALSE)
		return $zazn['id_accounts'];
	else
		return 0;
}
/*
function GetUserAccountId_Id($id)	// get id_accounts from "usxus" for id == x
{
	$vysl=query_db("SELECT id_accounts FROM ".TBL_USXUS." WHERE id = '$id' LIMIT 1");
	$zazn=mysqli_fetch_array($vysl);
	if ($zazn != FALSE)
	{
		return $zazn['id_accounts'];
	}
	else
		return 0;
}

function GetUserId_Account($id)	// get id_users from "usxus" for id_accounts == x
{
	$vysl=query_db("SELECT id_users FROM ".TBL_USXUS." WHERE id_accounts = '$id' LIMIT 1");
	$zazn=mysqli_fetch_array($vysl);
	if ($zazn != FALSE)
	{
		return $zazn['id_users'];
	}
	else
		return 0;
}
*/

function CheckIfLoginIsValid($new_login,$curr_id)
{
	global $db_conn;

	$query = "SELECT id,login FROM ".TBL_ACCOUNT." WHERE login = '$new_login' LIMIT 1";
	@$vysl=query_db($query);
	if (mysqli_num_rows ($vysl) == 0)
	{
		return true;
	}
	else
	{
		$zazn=mysqli_fetch_array($vysl);
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