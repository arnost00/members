<?php if (!defined('__HIDE_TEST__')) exit; /* zamezeni samostatneho vykonani */ ?>
<?

function private_SaveItemToModifyLog(&$action,&$table,&$description)
{	// don't call this function directly !
	global $usr;
	if($action == '' || $table == '' || $description == '')
		return false;
	$tstamp = time();
	$author = $usr->account_id;
	$sql_query = 'INSERT INTO '.TBL_MODLOG." (`timestamp`,`action`,`table`,`description`,`author`) VALUES ('$tstamp','$action','$table','$description',$author)";
	$vysledek=mysqli_query($db_conn, $sql_query);
	return ($vysledek != FALSE);
}

function SaveItemToModifyLog_Add($table,$description)
{
	$action = 'add';
	return private_SaveItemToModifyLog($action,$table,$description);
}

function SaveItemToModifyLog_Edit($table,$description)
{
	$action = 'edit';
	return private_SaveItemToModifyLog($action,$table,$description);
}

function SaveItemToModifyLog_Delete($table,$description)
{
	$action = 'delete';
	return private_SaveItemToModifyLog($action,$table,$description);
}

?>