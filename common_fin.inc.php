<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

$g_fin_mail_flag [0]['id'] = 0x0001;
$g_fin_mail_flag [0]['nm'] = 'Učet pod hranicí';
$g_fin_mail_flag [1]['id'] = 0x0002;
$g_fin_mail_flag [1]['nm'] = 'Účet v mínusu';

$g_fin_mail_flag_cnt = 2;

function CreateFinMailFlag(&$mflags)
{
	global $g_fin_mail_flag_cnt;
	global $g_fin_mail_flag;

	$result = 0;
	for($ii=0; $ii<$g_fin_mail_flag_cnt; $ii++)
	{
		if(isset($mflags[$ii]) && $mflags[$ii] == 1)
			$result += $g_fin_mail_flag [$ii]['id'];
	}
	return $result;
}

function GetFinMailFlagDesc(&$mflags)
{
	global $g_fin_mail_flag_cnt;
	global $g_fin_mail_flag;

	$result = '';
	for($ii=0; $ii<$g_fin_mail_flag_cnt; $ii++)
	{
		if(($mflags & $g_fin_mail_flag[$ii]['id']) != 0)
			$result .= (($result != '')?', ':'').$g_fin_mail_flag [$ii]['nm'];
	}
	return $result;
}

function IsFinanceTypeTblFilled()
{
	global $db_conn;
	
	@$vysledek=query_db("SELECT id FROM ".TBL_FINANCE_TYPES.' ORDER BY id');
	if ($vysledek === FALSE || $vysledek == null)
	{
		return 0;
	}
	else
	{
		return mysqli_num_rows($vysledek);
	}
}

?>