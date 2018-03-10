<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
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

function SQLDate2String($date)
{
	$dat=explode("-",$date);
	if ($date != "0000-00-00" && sizeof($dat) == 3) // YYYY-MM-DD
		return ltrim($dat[2],'0').".".ltrim($dat[1],'0').".".ltrim($dat[0],'0');
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
	return floor($value / 86400);
}

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
}

function GetTimeToReg($t_p)
//	Parameters:
//		t_p - datum prihlasek
{
	$t_p += (25*60*60); //+(25*60*60) - posun terminu prihlasek o 2 hodiny navic, kvuli time() ktery vraci UTC je tam +25 hodin
//	$diff = (int)(($t_p) - GetCurrentDate());  // puvodni pred pridanim 2 hodin navic
	$diff = (int)(($t_p) - time());
//	echo('['.$diff.']');
	if ($diff > 0)
		$diff = _todays($diff);
	else if ($diff < 0)
		$diff = -1;
	return $diff;
}

function GetTimeToReg_old($t_p)
//	Parameters:
//		t_p - datum prihlasek
{
	$diff = (int)(($t_p) - GetCurrentDate());
//	echo('['.$diff.']');
	if ($diff > 0)
		$diff = _todays($diff);
	else if ($diff < 0)
		$diff = -1;
	return $diff;
}


function IncDate($t_b,$t_i)
//	Parameters:
//		t_b - zakladni datum
//		t_i - prirustek ve dnech
{
	$suma = $t_b + ($t_i * 86400);
	return $suma;
}

function DecDate($t_b,$t_i)
//	Parameters:
//		t_b - zakladni datum
//		t_i - kolik odecist ve dnech
{
	$suma = $t_b - ($t_i * 86400);
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
			$mail_row.= EncodeString2Bytes(str_replace(array('@','.'),array(' (zavináč) ',' (tečka) '),$emails[$u]));
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

function DrawPageTitle($title)
{
	echo('<H2>'.$title.'</H2>');
}

function DrawPageSubTitle($title)
{
	echo('<H3>'.$title.'</H3>');
}



function HTML_Header($title,$style_file = '', $body_addons = '',$head_addons = '')
{
	global $g_www_meta_description, $g_www_meta_keyword;

	require_once ('./version.inc.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="Content-language" content="cs">
	<link rel="alternate" type="application/rss+xml" title="RSS export" href="rss.php" />
	<link rel="icon" href="favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	
	<link rel="StyleSheet" href="tiny-date-picker.css" type="text/css">
	
	<title><? echo($title); ?></title>
<?
echo("\t".'<meta name="generator" content="'.SYSTEM_NAME.' '.GetCodeVersion().'">'."\n");
echo("\t".'<meta name="description" content="'.$g_www_meta_description.'">'."\n");
echo("\t".'<meta name="keywords" content="ČSOB, Orientacni beh, Orientační běh, Orienteering, beh, běh, run, running, IOF, orientační, OB, '.$g_www_meta_keyword.'">'."\n");
echo("\t".'<meta name="copyright" content="(C) '.GetDevelopYears().' '.SYSTEM_AUTORS.', All rights&nbsp;reserved.">'."\n");
echo("\t".'<meta name="authors" content="'.SYSTEM_AUTORS.'">'."\n");

if ($style_file  != '')
{
	echo("\t".'<link href="'.$style_file.'" rel="StyleSheet" type="text/css" />'."\n");
}
echo($head_addons);
?>
</head>
<?
if ($body_addons != '')
	echo('<body '.$body_addons.">\n");
else
	echo('<body>'."\n");
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
	header('Content-Type: text/plain; charset=UTF-8');
}
//-----------------------------------------------------------
//	Global variables
//-----------------------------------------------------------

$g_current_date = 0;
$g_php_version = 0;

function repair_html_text($html)
{
	$doc = new DOMDocument();
	$html = '<div>'. $html . '</div>';
	$doc->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES','ISO-8859-2'));
	$html = substr($doc->saveXML($doc->getElementsByTagName('div')->item(0)), 5, -6);
	return mb_convert_encoding($html,'ISO-8859-2','UTF-8');

}

//--------------------------------------------------------
function email_mime_header_encode($text)
{
	if ($text != '')
		return  '=?utf-8?B?'.base64_encode($text).'?=';
	else
		return '';
}

function email_format_name($name, $email)
{
	if ($name != '')
		return email_mime_header_encode($name).' <'.$email.'>';
	else
		return $email;
}

function SendEmail($FromName, $FromA,$ToName,$ToA,$msg,$subject)
{	// send info mail
	global $g_emailadr, $g_fullname;

	$name_from = email_format_name($FromName, $FromA);
	$name_to = email_format_name($ToName,$ToA);
	
	$extra_headers  = 'MIME-Version: 1.0'.EMAIL_ENDL;
	$extra_headers .= 'Content-type: text/plain; charset="UTF-8"'.EMAIL_ENDL;
	$extra_headers .= 'Content-Transfer-Encoding: 8bit'.EMAIL_ENDL;
	$extra_headers .= 'From: '.$name_from.EMAIL_ENDL;
	$extra_headers .= 'Reply-To: '.$name_from.EMAIL_ENDL;
	$extra_headers .= 'X-Mailer: '.SYSTEM_NAME.'/'.GetCodeVersion();

	$send = (bool) mail($name_to,email_mime_header_encode($subject),$msg,$extra_headers);
	
	// debug output to file
	$fp = fopen(dirname(__FILE__) .'/logs/email_log.txt', 'a');
	$text = 'Email to \''.$ToA.'\' '.($send ? 'send.':' failed to send.');
	fputs($fp, $text."\r\n");
	fclose($fp);
	
	return $send;
}
//--------------------------------------------------------

function IsValidEmail($email)
{
	if ($email != '')
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	return false;
}
//--------------------------------------------------------
function __prepare_email($email)
{
	$email = str_replace("(at)","@",$email);
	$data = preg_split('/[;, ]/',$email, -1, PREG_SPLIT_NO_EMPTY);
	$data1 = array();
	foreach( $data as $item )
	{
		if (IsValidEmail($item))
			$data1[] = $item;
	}
	return $data1;
}

//--------------------------------------------------------
function GetFirstEmail($email)
{
	$data = __prepare_email($email);
	if (sizeof($data) > 0)
		return $data[0];
	else
		return '';
}
//--------------------------------------------------------
function GetAllEmails($email)
{
	return __prepare_email($email);
}
//--------------------------------------------------------

function GetFormatedTextDel(&$text, $cancelled = false)
{
	$result = '';
	if ($cancelled)
		$result .= '<del>';
	$result .= $text;
	if ($cancelled)
		$result .= '</del>';
	return $result;
}

//--------------------------------------------------------
function mb_str_pad(
  $input,
  $pad_length,
  $pad_string=" ",
  $pad_style=STR_PAD_RIGHT,
  $encoding="UTF-8")
{
    return str_pad(
      $input,
      strlen($input)-mb_strlen($input,$encoding)+$pad_length,
      $pad_string,
      $pad_style);
}
//--------------------------------------------------------

function full_url()
{
	return 'http'.((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? 's' : '').'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
}

?>
