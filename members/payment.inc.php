<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
require_once './modify_log.inc.php';

/**
 * library for payments
*/

/*
 * vytvor reklamaci platby
 */
function createClaim($user_id, $payment_id, $claim_text)
{
	
	$query = "insert into ".TBL_CLAIM." (user_id, payment_id, text) 
		values (".$user_id.", ".$payment_id.", '".$claim_text."')";
	mysql_query($query);
	$query = "update ".TBL_FINANCE." set claim = 1 where id = $payment_id";
	mysql_query($query);
}

/*
 * edituj reklamaci
 */
function updateClaim($claim_id, $claim_text)
{
	$query = "update ".TBL_CLAIM." set text='".$claim_text."' where id = $claim_id";
	mysql_query($query);
}

/*
 * uzavri reklamaci
 */
function closeClaim($claim_id, $payment_id)
{
	$query = "update ".TBL_CLAIM." set closed = 1 where id = $claim_id";
	mysql_query($query);
	$query = "update ".TBL_FINANCE." set claim = 0 where id = $payment_id";
	mysql_query($query);
}

/* 
 * vytvor platbu
 * params: amount, user_id (target of money) ...
 *
*/
function createPayment($editor_id, $user_id, $amount, $note, $datum, $id_zavod)
{
	if ($datum==null)
		$datum=date("Y-m-d");
	else
		$datum = String2SQLDateDMY($datum);
	$note = correct_sql_string($note);
	$query = "insert into ".TBL_FINANCE." (id_users_editor, id_users_user, amount, note, date, id_zavod) values 
			(".$editor_id.", ".$user_id.", ".$amount.", '".$note."', '".$datum."', '".$id_zavod."')";
	mysql_query($query);
	$lastId = mysql_insert_id();
	SaveItemToModifyLog_Add(TBL_FINANCE, "id=$lastId|user_id=$user_id|amount=$amount");
}

/*
 * nastav platbu jako stornovanou
 */
function stornoPayment($editor_id, $trn_id, $storno_note)
{
	$datum=date("Y-m-d");
	$storno_note = correct_sql_string($storno_note);
	$query = "update ".TBL_FINANCE." set storno='1', storno_by=".$editor_id.", storno_note='".$storno_note."', storno_date = '".$datum."' where id = $trn_id";
	mysql_query($query);
	SaveItemToModifyLog_Add(TBL_FINANCE, "id=$trn_id|note=$storno_note");
}

function updatePayment($editor_id, $trn_id, $id_zavod, $amount, $note)
{
	$note = correct_sql_string($note);
	$query = "update ".TBL_FINANCE." set id_zavod=".$id_zavod.", amount=".$amount.", note='".$note."' where id = $trn_id";
	mysql_query($query);
	SaveItemToModifyLog_Edit(TBL_FINANCE, "id=$trn_id|user_id=$editor_id|amount=$amount|note=$note");
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
 * sloupce ve vracenem poli : id, fin_total, prijmeni, jmeno
*/
function getAllUsersCurrentBalance()
{
	$query = 'SELECT u.id, hidden, prijmeni,jmeno, ifnull(f.sum_amount,0) sum_amount, (n.amount+f.sum_amount) total_amount, u.chief_pay FROM '.TBL_USER.' u 
		left join (select sum(fin.amount) sum_amount, id_users_user from '.TBL_FINANCE.' fin where (fin.storno is null) group by fin.id_users_user) f on u.id=f.id_users_user 
		left join (select ui.chief_pay payer_id, ifnull(sum(fi.amount),0) amount from '.TBL_USER.' ui 
		left join '.TBL_FINANCE.' fi on fi.id_users_user = ui.id where ui.chief_pay is not null and (fi.storno is null or fi.storno != 1) group by ui.chief_pay) n on u.id=n.payer_id 
		left join '.TBL_FINANCE_TYPES.' ft on ft.id = u.finance_type
		group by u.id ORDER BY u.`sort_name` ASC;';
		
	$vysl=MySQL_Query($query);
	$data = array();
	if ($vysl != FALSE)
	{
		while ($zazn=MySQL_Fetch_Array($vysl))
		{
			if (($zazn['chief_pay']>0 && $zazn['chief_pay']<>$zazn['id']) || $zazn['hidden'])
			{
				// pokud za nej plati nekdo jiny, vubec nebrat v potaz !
				// nebo pokud je skryt
			}
			else
			{
				$data[$zazn['id']] = $zazn;
				$data[$zazn['id']]['fin_total'] = $zazn['sum_amount'];
				
				if ($zazn['total_amount'] != null )
					$data[$zazn['id']]['fin_total'] = $zazn['total_amount'];
			}
		}
	}
	else
		return array();
	
	return $data;
}

/*
 * pridani informace, kdo komu penize poslal, pridava se do poznamky
*/
function createFinanceNoteFromTo($lid_from, $lid_to)
{
		$select = "select sort_name name from ".TBL_USER." where id in (".$lid_from.", ".$lid_to.")";
		$vysledek_name_from_name_to = MySQL_Query($select);
		$zaznam_from = MySQL_Fetch_Array($vysledek_name_from_name_to);
		$zaznam_to = MySQL_Fetch_Array($vysledek_name_from_name_to);
		return " <i>[".$zaznam_from['name']."->".$zaznam_to['name']."]</i> ";
}

?>