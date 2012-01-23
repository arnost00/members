<?php
function savedebug($buffer) {
file_put_contents(dirname(__FILE__) . '/logs/debug_'.md5(date('d.m.Y - H:i:s')).'.log', $buffer);
return $buffer;
}

error_reporting(E_ALL);
ob_start('savedebug');
  
include(dirname(__FILE__) .'/timestamp.inc.php');
_set_global_RT_Start();
?>
<?php 

// define only when debug (block email send)
define('_DEBUG_SEND_',1);



// Date in the past 
header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// always modified 
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 

// HTTP/1.1 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false); 

// HTTP/1.0 
header("Pragma: no-cache"); 

?>
<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>

<?
require(dirname(__FILE__) .'/cfg/_globals.php');
require (dirname(__FILE__) .'/connect.inc.php');
require (dirname(__FILE__) .'/common.inc.php');
include (dirname(__FILE__) .'/common_race.inc.php');
require (dirname(__FILE__) .'/common_rg_race.inc.php');
require (dirname(__FILE__) .'/version.inc.php');

define ('EMAIL_ENDL',"\n");
define ('DIV_LINE','-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-');
define ('DIV_LINE2','=====================================================================');
//--------------------------------------------------------
// functions

class CLogMe
{
	var $filename;
	
	function TS()
	{
		return date('d.m.Y - H:i:s');
	}
	
	function __construct ($_filename)
	{
		$this->filename = $_filename;
		$this->writeline('Start ... '.$this->TS());
	}
	
	function end()
	{
		$this->writeline('Stop .... '.$this->TS());
		$this->writeline(DIV_LINE);	
	}
	
	function writeline($text)
	{
		$fp = fopen( $this->filename, 'a');
		fputs( $fp, $text."\r\n");
		fclose($fp);
	}
}

function IsValilEmail($email)
{
	if ($email != '')
	{
		return true;
	}
	return false;
}
function ClearAllModifyFlags()
{
	$query='UPDATE '.TBL_RACE." SET modify_flag='0'";
	$result=MySQL_Query($query)
		or die("Chyba p�i prov�d�n� dotazu do datab�ze.");
	if ($result == FALSE)
		echo("Nepoda�ilo se zm�nit �daje o upozor�ov�n�.\n");
	else	
		echo("Modify flags cleared.\n");
}

function MatchRaceType($race_val,$notify_val)
{
	global $g_racetype,$g_racetype_cnt;
	
	for ($ii = 0; $ii < $g_racetype_cnt;$ii++)
	{
		if($race_val == $g_racetype [$ii]['enum'])
		{
			return (($notify_val & $g_racetype [$ii]['id']) != 0);
		}
	}
	return false;
}

function MatchRaceSubType($race_val,$notify_val)
{
	global $g_zebricek_cnt, $g_zebricek;

	if ($race_val == 0 || $notify_val == 0)
	{
//		echo($race_val.' <+>'.$notify_val.'<br>');
		return true;
	}
	for($ii=0; $ii<$g_zebricek_cnt; $ii++)
	{
		if(($notify_val & $g_zebricek [$ii]['id']) != 0)
		{
			if (($race_val & $g_zebricek [$ii]['id']) != 0)
				return true;
		}
	}
	return false;
}

function GetMailRaceInfoLineTf(&$zaznam,&$reg_term)
{
	if($zaznam['vicedenni'])
		$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
	else
		$datum=Date2String($zaznam['datum']);

	$termin = _Reg2Str($reg_term);

	$nazev = $zaznam['nazev'];
	$oddil = $zaznam['oddil'];

	return $datum.' / '.$nazev.' / '.$oddil.' ['.$termin.']';
}

function GetMailRaceInfoLine(&$zaznam)
{
	if($zaznam['vicedenni'])
		$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);
	else
		$datum=Date2String($zaznam['datum']);

	$nazev = $zaznam['nazev'];
	$oddil = $zaznam['oddil'];

	return $datum.' / '.$nazev.' / '.$oddil;
}

function GenerateEmail(&$ToA,&$msg)
{	// send info mail
	global $g_emailadr, $g_fullname;

	$extra_headers  = 'MIME-Version: 1.0'.EMAIL_ENDL;
	$extra_headers .= 'Content-type: text/plain; charset="Windows-1250"'.EMAIL_ENDL;
	$extra_headers .= 'From: '.$g_fullname.' <'.$g_emailadr.'>'.EMAIL_ENDL;
	$extra_headers .= 'Reply-To: '.$g_emailadr.EMAIL_ENDL;
	$extra_headers .= 'X-Mailer: '.SYSTEM_NAME.'/'.GetCodeVersion();

	if (!defined('_DEBUG_SEND_'))
	{
		if (mail ($ToA,'informace o oddilovych prihlaskach',$msg,$extra_headers))
			echo('Email send<br>');
		else
			echo('Email not send<br>');
	}
	else
	{	// debug output to file
		$fp = fopen(dirname(__FILE__) .'/logs/dbg_mail_'.md5(date('d.m.Y - H:i:s')).'.txt', 'a');
		fputs($fp, DIV_LINE2."\r\n");
		fputs($fp, $ToA."\r\n");
		fputs($fp, DIV_LINE2."\r\n");
		fputs($fp, $extra_headers."\r\n");
		fputs($fp, DIV_LINE2."\r\n");
		fputs($fp, $msg."\r\n");
		fputs($fp, DIV_LINE2."\r\n");
		fclose($fp);
	}
}
//--------------------------------------------------------

$logme = new CLogMe(dirname(__FILE__) .'/logs/cron_log_info.txt');
db_Connect();

//$curr_date = mktime (0,0,0,5,1,2010);
//$curr_date = mktime (0,0,0,6,1,2010);
$curr_date = GetCurrentDate();

$logme->writeline('Processing date: '.Date2String($curr_date));

$d1 = $curr_date;
$query_m='SELECT * FROM '.TBL_MAILINFO.' ORDER BY `id`';
$query_r='SELECT * FROM '.TBL_RACE.' WHERE datum >= '.$curr_date.' AND (prihlasky > 0 OR modify_flag > 0) ORDER BY datum';

$vysledek_m=MySQL_Query($query_m);
$vysledek_r=MySQL_Query($query_r);

$cnt_send = $cnt_tested = 0;
if (mysql_num_rows($vysledek_m) > 0 && mysql_num_rows($vysledek_r) > 0)
{
	$races = array();
	while ($zaznam_r=MySQL_Fetch_Array($vysledek_r))
	{
//		echo(Date2String($zaznam_r['datum']).' - '.$zaznam_r['nazev'].'<br>');
		$races[] = $zaznam_r;
	}

	// test
	while ($zaznam_m=MySQL_Fetch_Array($vysledek_m))
	{
		$cnt_tested++;
/*		
		echo('Email - '.$zaznam_m['email'].'<br>');
//		print_r($zaznam_m);
		echo(' | ');
		echo GetRaceTypeNameSpec($zaznam_m['type']);
		echo(' | ');
		echo GetZebricekName2($zaznam_m['sub_type']);
		echo("<br>\n");
*/		
		if (!IsValilEmail($zaznam_m['email']))
			continue;
		$send_email = false;
		$full_msg = 'Vybran� informace o term�nech a zm�n�ch v p��hl�kov�m syst�mu '.$g_shortcut.EMAIL_ENDL;
		$full_msg .= DIV_LINE.EMAIL_ENDL.EMAIL_ENDL;

		if ($zaznam_m['active_tf'])
		{
			$active = false;
			$lines = array();
//			echo("Aktivn� - Bl��c� se konec term�nu p�ihl�ek<br>\n");
			for($ii = 0; $ii < sizeof($races); $ii++)
			{
				$new_reg = _GetNewReg($races[$ii],$curr_date);
/*
				print_r($new_reg);
				echo(' | ');
				echo GetRaceTypeName($races[$ii]['typ']);
				echo(' | ');
				echo GetZebricekName2($races[$ii]['zebricek']);
				echo("<br>\n");
*/
				$match_type = MatchRaceType($races[$ii]['typ'], $zaznam_m['type']);
				$match_sub_type = MatchRaceSubType($races[$ii]['zebricek'], $zaznam_m['sub_type']);
				
				if ($new_reg[0] != 0 && $match_type && $match_sub_type)
				{
					$diff = _DateDiffInDays($new_reg[0],$curr_date);
//					echo('za dni: '.$diff.'<br>');
					if ($diff == $zaznam_m['daysbefore'])
					{
						$active = $send_email = true;
//						echo('* Zavod - '.$races[$ii]['nazev'].' - priblizil se termin prihlasek<br>');
						$lines[] = GetMailRaceInfoLineTf($races[$ii],$new_reg);
					}
					else if ($diff < $zaznam_m['daysbefore'])
					{
//						echo('* Zavod - '.$races[$ii]['nazev'].' - vice se priblizil termin prihlasek<br>');
						$lines[] = GetMailRaceInfoLineTf($races[$ii],$new_reg);
					}
				}
			}
			if($active) // add to msg
			{
				$full_msg .= 'Bl�� se jeden nebo v�ce term�nu p�ihl�ek: '.EMAIL_ENDL;
				for ($jj = 0; $jj < sizeof($lines); $jj++)
					$full_msg .= ' * '.$lines[$jj].EMAIL_ENDL;
				$full_msg .= EMAIL_ENDL;
			}
		}
		if ($zaznam_m['active_ch'])
		{
			if (($zaznam_m['ch_data'] & $g_modify_flag [0]['id']) != 0)
			{
				// zmena terminu prihlasek
				$active = false;
				$lines = array();
				for($ii = 0; $ii < sizeof($races); $ii++)
				{
					if (($races[$ii]['modify_flag'] & $g_racetype[0]['id']) != 0) 
					{
						$active = $send_email = true;
//						echo('* Zavod - '.$races[$ii]['nazev'].' - doslo ke zmene - ('.$races[$ii]['modify_flag'].') - '.GetModifyFlagDesc($races[$ii]['modify_flag']).'<br>');
						$lines[] = GetMailRaceInfoLine($races[$ii]);
					}
				}
				if($active) // add to msg
				{
					$full_msg .= 'Zm�na v term�nech p�ihl�ek na z�vody: '.EMAIL_ENDL;
					for ($jj = 0; $jj < sizeof($lines); $jj++)
						$full_msg .= ' * '.$lines[$jj].EMAIL_ENDL;
					$full_msg .= EMAIL_ENDL;
				}
			}
			
			if (($zaznam_m['ch_data'] & $g_modify_flag [1]['id']) != 0)
			{
				// pridani zavodu
				$active = false;
				$lines = array();
				for($ii = 0; $ii < sizeof($races); $ii++)
				{
					if (($races[$ii]['modify_flag'] & $g_racetype[1]['id']) != 0)
					{
						$active = $send_email = true;
//						echo('* Zavod - '.$races[$ii]['nazev'].' - doslo ke zmene - ('.$races[$ii]['modify_flag'].') - '.GetModifyFlagDesc($races[$ii]['modify_flag']).'<br>');
						$lines[] = GetMailRaceInfoLine($races[$ii]);
					}
				}
				if($active) // add to msg
				{
					$full_msg .= 'P�id�no do kalend��e z�vod�: '.EMAIL_ENDL;
					for ($jj = 0; $jj < sizeof($lines); $jj++)
						$full_msg .= ' * '.$lines[$jj].EMAIL_ENDL;
					$full_msg .= EMAIL_ENDL;
				}
			}
			
			if (($zaznam_m['ch_data'] & $g_modify_flag [2]['id']) != 0)
			{
				// zmena terminu zavodu
				$active = false;
				$lines = array();
				for($ii = 0; $ii < sizeof($races); $ii++)
				{
					if (($races[$ii]['modify_flag'] & $g_racetype[2]['id']) != 0)
					{
						$active = $send_email = true;
//						echo('* Zavod - '.$races[$ii]['nazev'].' - doslo ke zmene - ('.$races[$ii]['modify_flag'].') - '.GetModifyFlagDesc($races[$ii]['modify_flag']).'<br>');
						$lines[] = GetMailRaceInfoLine($races[$ii]);
					}
				}
				if($active) // add to msg
				{
					$full_msg .= 'Zm�na term�nu z�vod�: '.EMAIL_ENDL;
					for ($jj = 0; $jj < sizeof($lines); $jj++)
						$full_msg .= ' * '.$lines[$jj].EMAIL_ENDL;
					$full_msg .= EMAIL_ENDL;
				}
			}
		}
		if ($zaznam_m['active_rg'])
		{
			$active = false;
			$lines = array();
//			echo("Aktivn� - Upozornit, �e uplynul term�n (reg)<br>\n");
			for($ii = 0; $ii < sizeof($races); $ii++)
			{
				$old_reg = _GetOldReg($races[$ii],$curr_date);
//				print_r($old_reg);
				if ($old_reg[0] != 0)
				{
					$diff = _DateDiffInDays($old_reg[0],$curr_date);
//					echo('ubehlo dni: '.$_diff);
					if ($diff == 1)
					{
						$active = $send_email = true;
//						echo('* Zavod - '.$races[$ii]['nazev'].' - vcera byl termin prihlasek<br>');
						$lines[] = GetMailRaceInfoLine($races[$ii]);
					}
				}
			}
			if($active) // add to msg
			{
				$full_msg .= 'Pr�v� skon�il intern� term�n p�ihl�ek: '.EMAIL_ENDL;
				for ($jj = 0; $jj < sizeof($lines); $jj++)
					$full_msg .= ' * '.$lines[$jj].EMAIL_ENDL;
				$full_msg .= EMAIL_ENDL;
			}
		}
		if ($send_email)
		{
			echo('<b>Send email to user.</b><br>');
			$full_msg .= DIV_LINE.EMAIL_ENDL;
			$full_msg .= 'Vygenerov�no dne '.Date2String($curr_date).EMAIL_ENDL;
			$full_msg .= 'Zm�nu a p��padn� zru�en� zas�lan�ch informac� provedete p�es p�ihl�kov� syst�m odd�lu '.$g_shortcut.'.'.EMAIL_ENDL;
			$full_msg .= 'Nejl�pe p��mo na adrese '.$g_baseadr.EMAIL_ENDL;
			
			GenerateEmail($zaznam_m['email'],$full_msg);
			$cnt_send++;
		}
	}
	
/*	
	echo('<pre>');
	print_r($races);
	echo('</pre>');
*/	
	// test
}

if (!defined('_DEBUG_SEND_'))
{
	ClearAllModifyFlags();
}

_set_global_RT_End();
$logme->writeline('Processed requests: '.$cnt_tested.'. Emails send: '.$cnt_send);
$logme->writeline('Generation time = '._get_global_RT_difference_TS().' sec');
$logme->end();
ob_end_flush();
?>	