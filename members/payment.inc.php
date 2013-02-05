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
function createPayment($editor_id, $user_id, $amount, $note, $datum, $id_zavod)
{
	if ($datum==null) $datum=date("Y-m-d");
	$query = "insert into ".TBL_FINANCE." (id_users_editor, id_users_user, amount, note, date, id_zavod) values 
			(".$editor_id.", ".$user_id.", ".$amount.", '".$note."', '".$datum."', ".$id_zavod.")";
	mysql_query($query);
}


function stornoPayment($editor_id, $trn_id, $storno_note)
{
	$datum=date("Y-m-d");
	$query = "update ".TBL_FINANCE." set storno='1', storno_by=".$editor_id.", storno_note='".$storno_note."', storno_date = '".$datum."' where id = $trn_id";
	mysql_query($query);
}

function updatePayment($editor_id, $trn_id, $amount, $note)
{
	$query = "update ".TBL_FINANCE." set amount=".$amount.", note='".$note."' where id = $trn_id";
	mysql_query($query);
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

// /*
//  * storno platby
//  * pokud je platba historizovana, pak prepocti historii
// */
// function stornoPayment($id)
// {
// 	//update finance set storno = 1 where id = $id;
// 	//select user_id, history from payment where id = $id;
// 	if ($history)
// 	{
// 		recalculateHistory($user_id);
// 	}
// }

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