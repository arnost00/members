<?php if (!defined('__HIDE_TEST__')) exit; /* zamezeni samostatneho vykonani */ ?>
<?

define ('CS_EMPTY_ITEM','1001');
define ('CS_MIN_LEN_LOGIN','1002');
define ('CS_LOGIN_EXIST','1003');
define ('CS_LOGIN_UPDATED','1004');
define ('CS_BAD_CUR_PASS','1005');
define ('CS_NODIFF_PASS','1006');
define ('CS_MIN_LEN_PASS','1007');
define ('CS_DIFF_NEWPASS','1008');
define ('CS_PASS_UPDATED','1009');
define ('CS_ACC_UPDATED','1010');
define ('CS_ACC_CREATED','1011');
define ('CS_USER_PASS_UPDATED','1012');
define ('CS_ADM_PASS_REQ','1013');
define ('CS_ADM_NOT_FOUND','1014');
define ('CS_USER_LOCK_ACC','1015');
define ('CS_ADM_PASS_WRONG','1016');
/*
define ('CS_','1017');
*/
define ('CS_UNKNOWN_ERROR','9999');

function GetResultString($code)
{

	switch($code)
	{
		case CS_EMPTY_ITEM:
			$result = 'Musíš nìco zadat!'; break;
		case CS_MIN_LEN_LOGIN:
			$result = 'Minimální délka pøihlašovací jména je 4 znaky !'; break;
		case CS_LOGIN_EXIST:
			$result = 'Toto pøihlašovací jméno již existuje.'; break;
		case CS_LOGIN_UPDATED:
			$result = 'Podpis a pøihlašovací jméno byly aktualizovány.'; break;
		case CS_BAD_CUR_PASS:
			$result = 'Špatnì zadané souèasné heslo !'; break;
		case CS_NODIFF_PASS:
			$result = 'Nové i staré heslo nemùže být stejné !'; break;
		case CS_MIN_LEN_PASS:
			$result = 'Minimální délka hesla jsou 4 znaky !'; break;
		case CS_DIFF_NEWPASS:
			$result = 'Nové heslo i kontrolní heslo musejí být stejná !'; break;
		case CS_PASS_UPDATED:
			$result = 'Heslo bylo zmìnìno.'; break;
		case CS_ACC_UPDATED:
			$result = 'Byl upraven úèet èlena.'; break;
		case CS_ACC_CREATED:
			$result = 'Byl založen nový úèet.'; break;
		case CS_USER_PASS_UPDATED:
			$result = 'Bylo zmìnìno heslo èlena.'; break;
		case CS_ADM_PASS_REQ:
			$result = 'Musíš zadat heslo admina!'; break;
		case CS_ADM_NOT_FOUND:
			$result = 'Nepodaøilo se najít admina !!!'; break;
		case CS_USER_LOCK_ACC:
			$result = 'Byl zamèen/odemèen úèet èlena.'; break;
		case CS_ADM_PASS_WRONG:
			$result = 'Musíš zadat správné heslo admina!'; break;
/*
		case :
			$result = ''; break;
*/
		case CS_UNKNOWN_ERROR:
			$result = 'Neznámá chyba.'; break;
		default :
			$result = '';
	}
	return $result;
}

?>