<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
function cp2iso($text) {
	$text=strtr($text, "\x8A\x8D\x8E\x9A\x9D\x9E","\xA9\xAB\xAE\xB9\xBB\xBE");
	return $text;
}

function iso2cp($text) {
	$text=strtr($text, "\xA9\xAB\xAE\xB9\xBB\xBE","\x8A\x8D\x8E\x9A\x9D\x9E");
	return $text;
}
/* pouziti :
mysql_query("INSERT INTO tab VALUES('".cp2iso($text."')");
> (...)
*/

//--------------------------

function LogToFile($file,$msg)
{
	$fp = fopen( $file, 'a');
	fputs( $fp, $msg);
	fclose($fp);
}

function formatDate($date)
{
	$date_arr = explode("-", $date);
	$text = $date_arr[2].".".$date_arr[1].".".$date_arr[0];
	return $text;
}

function Date2String($date, $long = false)
{
	$text = ($date == 0) ? "-" : @date((($long) ? "d.m.Y":"j.n.Y"),$date);
	return $text;
}

function Date2Arr($date)
{
	$d_arr[0] = 0;	//	d
	$d_arr[1] = 0;	//	m
	$d_arr[2] = 0;	//	y
	if($date != 0)
	{
		$d_arr[0] = @date('j',$date);
		$d_arr[1] = @date('n',$date);
		$d_arr[2] = @date('Y',$date);
	}
	return $d_arr;
}


function Date2Year($date)
{
	return ($date != 0) ? @date('Y',$date) : 0;
}

function Date2StringDM($date, $long = false)
{
	$text = ($date == 0) ? "" : @date((($long) ? "d.m":"j.n"),$date);
	return $text;
}

function Date2StringFT($dateFrom,$dateTo)
{
	if($dateFrom != 0 && $dateTo != 0)
	{
		if($dateFrom == $dateTo)
			$text = @date('j.n.Y',$dateFrom);
		else
			$text = @date('j.n',$dateFrom).' - '.@date('j.n.Y',$dateTo);
	}
	else if($dateFrom != 0)
		$text = @date('j.n.Y',$dateFrom).' - ?';
	else
		$text = '-';
	return $text;
}

function TimeStamp2String(&$ts)
{
	$text = ($ts == 0) ? '-' : @date('d.m.Y - H:i:s',$ts);
	return $text;
}


function String2DateYMD($text)
{	// yyyy.mm.dd
	$da = explode (".",$text);
	if (sizeof($da) == 3)
		$date = mktime (0,0,0,$da[1],$da[2],$da[0]);
	else
		$date = 0;
	return $date;
}

function CreateDate($d,$m,$y)
{	
	$date = mktime (0,0,0,$m,$d,$y);
	return $date;
}

function String2DateDMY($text)
{	// dd.mm.yyyy
	$da = explode (".",$text);
	if (sizeof($da) == 3)
		$date = mktime (0,0,0,$da[1],$da[0],$da[2]);
	else
		$date = 0;
	return $date;
}

function GetCurrentDateString($long = false)
{
	$text=date(($long) ? "d.m.Y":"j.n.Y");
	return $text;
}

function GetCurrentDate()
{
	global $g_current_date;
	if ($g_current_date != 0)
		return $g_current_date;
	else
	{
		$date=date("d.m.Y");
		$da = explode(".",$date);
		if (sizeof($da) == 3)
			$date = mktime (0,0,0,$da[1],$da[0],$da[2]);
		else
			$date = 0;
		$g_current_date = $date;
		return $date;
	}
}
/*
::	not used
function GetCurrentDateTime()
{
	$date=date("U");
	return $date;
}
*/
function SQLDate2String($date)
{
	$dat=explode("-",$date);
	if ($date != "0000-00-00" && sizeof($dat) == 3) // YYYY-MM-DD
		return $dat[2].".".$dat[1].".".$dat[0];
	else
		return "";
}

function String2SQLDateDMY($date)
{
	$dat=explode(".",$date);
	if (sizeof($dat) == 3) // DD.MM.YYYY
		return $dat[2]."-".$dat[1]."-".$dat[0];
	else
		return "0000-00-00";
}

function SQLDate2StringReg($date)
{
	$dat=explode('-',$date);
	if ($date != '0000-00-00' && sizeof($dat) == 3) // YYYY-MM-DD
		return substr($dat[0],2,2).$dat[1].$dat[2];	// yymmdd
	else
		return '000000';
}

//-----------------------------------------------------------
// Race function
//-----------------------------------------------------------

function _todays($value)
{	// 86400 = 60 * 60 *24
	return round($value / 86400);
}

/*
::	obsolete
function GetTimeDiff($t_z,$t_p)
//	Parameters:
//		t_z - datum zavodu
//		t_p - datum prihlasek
//		180 - cca pul roku
{
	return (($t_p > $t_z) || (_todays($t_z - $t_p) > 180)) ? false : true;
}
*/

function GetTimeToRace($t_z)
//	Parameters:
//		t_z - datum zavodu
{
	$diff = (int)($t_z - GetCurrentDate());
	if ($diff > 0)
		$diff = _todays($diff);
	else if ($diff < 0)
		$diff = -1;
	return $diff;
/*
::	old code
	$diff = _todays($t_z - GetCurrentDate());
	if ($diff < -1) $diff = -1;
	return $diff;
*/
}

function GetTimeToReg($t_p)
//	Parameters:
//		t_p - datum prihlasek
{
	$diff = (int)($t_p - GetCurrentDate());
	if ($diff > 0)
		$diff = _todays($diff);
	else if ($diff < 0)
		$diff = -1;
	return $diff;
/*
::	old code
	$diff = _todays($t_p - GetCurrentDate());
	if ($diff < -1) $diff = -1;
	return $diff;
*/
}

function IncDate($t_b,$t_i)
//	Parameters:
//		t_b - zakladni datum
//		t_i - prirustek ve dnech
{
	$suma = $t_b + ($t_i * 86400);
	return $suma;
}

function CountManAge($sqldate)
{	// $date = sql date
	global $g_curr_year;
	if (!isset($g_curr_year))
		$g_curr_year=(int)date('Y');
	$da0=explode('-',$sqldate);
	if ($sqldate != '0000-00-00' && sizeof($da0) == 3) // YYYY-MM-DD
	{
		$born_year = (int)$da0[0];
		$dt = $g_curr_year - $born_year;
		return ($dt >= 0) ? $dt : -1;
	}
	else
	{
		return -1;
	}
}

//-----------------------------------------------------------
// Link function
//-----------------------------------------------------------

function ParseEmails($emails)
{
	if ($emails != '')
	{
		$mail_row = '';
		$line = trim($emails);
		$cnt = substr_count($line,'@');	// one @ per email
		if ($cnt > 1)
		{
			$pieces = explode (' ', $line);
			for($u = 0; $u < $cnt; $u++)
			{
				$mail_row[]=str_replace(array(',',';'),'',$pieces[$u]);
			}
		}
		else
		{
			$mail_row[]=str_replace(array(',',';'),'',$line);
		}
	}
	else
		return 0;
	return $mail_row;
}

function GetEmailHTML($emails)
{
	if (is_array($emails))
	{
		$mail_row = '';
		for($u = 0; $u < sizeof($emails); $u++)
		{
			if ($u != 0)
				$mail_row.='<BR>';
			$mail_row.='<A href="mailto:'.$emails[$u].'">'.$emails[$u].'</A>';
		}
		return $mail_row;
	}
	else
		return '';
}

function EncodeString2Bytes($string)
{
	$result = '';
	for($i = 0;$i < strlen($string); $i++)
	{
		$chn = ord($string[$i]);
		$n = ($chn < 128) ? rand(0,6) : 0;
		switch($n)
		{
			case 6:
			case 5:
			case 4:
				$result .= '&#x'.sprintf("%X",$chn).';';
				break;
			case 3:
			case 2:
			case 1:
				$result .= '&#'.$chn.';';
				break;
			case 0:
			default :
				$result .= $string[$i];
				break;
		}
	}
	return $result;
}

function GetEmailSecuredHTML($emails)
{
	if (is_array($emails))
	{
		$mail_row = '';
		for($u = 0; $u < sizeof($emails); $u++)
		{
			if ($u != 0)
				$mail_row.='<BR>';
			$mail_row.= EncodeString2Bytes(str_replace(array('@','.'),array(' (zavináè) ',' (teèka) '),$emails[$u]));
//			$mail_row.= EncodeString2Bytes($emails[$u]);
		}
		return $mail_row;
	}
	else
		return '';
}


function GetRaceLinkHTML($link,$img=true)
{
	if ($link != '')
	{
		$odkaz = '<A href="'.cononize_url($link,1).'" target="_blank">';
		$odkaz .= ($img) ? '<img src="imgs/web.gif" border="0" valign="middle">' : 'zde';
		$odkaz .= '</A>';
	}
	else
		$odkaz = ($img) ? '' : '-';
	return $odkaz;
/*
::	old code
		if ($link != '' && $link != 'www..cz')
			$odkaz = '<A href="'.cononize_url($link,1).'" target="_blank"><img src="imgs/web.gif" border="0" align="middle"></A>';
		else
			$odkaz = "";
	return $odkaz;
*/
}

function ShowRefreshInfo($race_edit = false)
{
	if($race_edit)
		$text = '(editace, kategorie)';
	else
		$text = '(pøihlášení, odhlášení)';
	echo('<span class="refresh_warn">Provedení zmìny '.$text.' se ihned nezobrazí,<br> pro zobrazení je nutné znovu naèíst tuto stránku (REFRESH nebo F5) !</span><br>');
}

function GetPhpVersion()
{
	global $g_php_version;
	if ($g_php_version != 0)
		return $g_php_version;
	else
	{
		$expl = explode('.',phpversion(),3);
		if (sizeof($expl) == 3)
		{
			$expl[2] = ((int)($expl[2]));	// trim RC3, -dev ...
			$pv = (int)sprintf('%d%02d%02d', $expl[0], $expl[1], $expl[2]);
		}
		else
			$pv = 0;
		$g_php_version = $pv;
		return $pv;
	}
}

function Print_Action_Result(&$text)
{
	if($text != '')
	{
		echo '<BR><hr><BR>';
		echo '<span class="ResultText">Výsledek poslední provedené úpravy :<BR>'.$text.'</span><BR>';
		echo '<BR><hr><BR>';
	}
}

function AddPointerImg()
{
	return '&nbsp;<img src="imgs/arrow.gif" width="8" height="14" align="top">';
}

function DrawPageTitle($title, $show_date = true)
{
	if($show_date)
	{
		echo('<div class="HdrDate">Dnes je : '.GetCurrentDateString().'</div>');
	}
	echo('<H2>'.$title.'</H2>');
}

function DrawPageSubTitle($title)
{
	echo('<H3>'.$title.'</H3>');
}



function HTML_Header($title)
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1250">
<meta http-equiv="Content-language" content="cs">
<title><? echo($title); ?></title>
</head>
<body>
<?
}

function HTML_Footer()
{
?>
</body>
</html>
<?
}

function TXT_Header()
{
	header('Content-Type: text/plain; charset=windows-1250');
}
//-----------------------------------------------------------
//	Global variables
//-----------------------------------------------------------

$g_current_date = 0;
$g_php_version = 0;
?>