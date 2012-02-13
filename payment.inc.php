<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
/**
 * library for payments
*/


/* 
 * vytvor platbu
 * params: amount, user_id (target of money) ...
 *
*/
function createPayment($user_id, $amount, $note, $race = null, $csos = True)
{
	$origin_amount = null;
	$percent = null;
	if ($csos)
	{
		$flatper = getUserPaymentMethod($user_id); //pokud vrati -1, tak user pouziva flatrate
		if ($flatper >= 0)
		{
			$origin_amount = $amount;
			$amount = $origin_amount * $flatper;
		}
	}
	//insert into finance id_account_user = $user_id, amount = $amount, origin_amount = $origin_amount ...
}

/*
 * vraci flatrate pro zadane user_id
 * v budoucnu pouzit pro vraceni informace, zda user neni i sponsor
*/
function getUserPaymentMethod($id)
{
	//select flatrate, percents from user where id = $id;
	$paymentMethod['rate'] = $flatrate;
	$paymentMethod['percent'] = $percents;
	return $paymentMethod;
}

/*
 * vraci -1 pro flatrate, jinak procenta v desetinne podobe
*/
function getUserPercent($id)
{
	//TODO popremyslet, zda by nebylo lepsi vracet false (nebo -1, null?) v pripade, kdy je user na flatrate
	//TODO nebo rovnou nespojit s getUserPaymentMethod, kdy by vracela False a zaroven i vysi procent
	$select = "select flatrate, percents from '".TBL_USER."' where id = $id";
	
// 	while ($zaznam=MySQL_Fetch_Array($vysledek))
	
	if ($flatrate) return -1;
	return $percent;
}

function getCSOSFlag($id)
{
	//select csos from race where id = $id;
	return $csos;
}

/* 
 * hisotrizuj platby
 * ve sloupci fin v user bude suma historizovanych plateb
 *
*/
function historizePaymentsForUser($to_date, $user_id)
{
	//navys sloupec fin z tabulky user o vysi plateb probehlych do $to_date
	//update user set fin = fin + (select sum(amount) from finance where user = $user_id and date <= $to_date and history = 0) where user = $user_id;
	//historizace spoctenych plateb 
	//update finance set history = 1 where user = $user_id and date <= $to_date and history = 0;
}

/*
 * prepocte historizovane zaznamy a ulozi uzivateli
 * storno platby se nepocitaji
*/
function recalculateHistory($user_id)
{
	//update user set fin = (select sum(amount) from finance where user_id = $user_id and history = 1 and storno = 0) where id = $user_id;
}

/*
 * storno platby
 * pokud je platba historizovana, pak prepocti historii
*/
function stornoPayment($id)
{
	//update finance set storno = 1 where id = $id;
	//select user_id, history from payment where id = $id;
	if ($history)
	{
		recalculateHistory($user_id);
	}
}

/*
 * vrati pole zustatku pro vsechny uzivatele
 * sloupce ve vracenem poli : id, fin, prijmeni, jmeno
*/
function getAllUsersCurrentBalance()
{	// priprava ... vraci sloupec fin z user ... prepsat na opravdovy zustatek !!!
	$vysl=MySQL_Query("SELECT id, fin, prijmeni, jmeno FROM ".TBL_USER);
	$data = array();
	if ($vysl != FALSE)
	{
		while ($zazn=MySQL_Fetch_Array($vysl))
		{
			$data[$zazn['id']] = $zazn;
		}
	}
	else
		return array();

	return $data;
}

?>