<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance člena');
?>
<CENTER>
<?
//inicializace id uzivatele pro vypis financi
$user_id = $usr->user_id;
//zamezi zobrazeni moznosti pro zmenu z Clenskeho menu
$finance_readonly = "readonly";

//---------- BLOK KODU PRO FINANCE ----------//
require_once './payment.inc.php'; // pomocne funkce a javascript pro finance
if (IsSet($payment) && IsSet($user_id) && IsSet($id_to) && IsSet($amount) && $id_to != -1)
{
	// set, clean & check values
	$id_from = $user_id;
	$amount = (int)$amount; // only positive numbers
	$id_to = (int)$id_to;
	$note = (IsSet($note)) ? correct_sql_string($note) : '';
	$can_send = true;
	@$vysledek=MySQL_Query('SELECT id FROM '.TBL_USER.' WHERE `id` = \''.$id_to.'\' AND `hidden` = \'0\' LIMIT 1');
	if (!$vysledek)
	{
		$result = 'Nelze převést neexistujícímu členu.';
		$can_send = false;
	}

	@$vysledek2=MySQL_Query('SELECT SUM(fin.amount) AS amount  from '.TBL_FINANCE.' fin 
		where fin.id_users_user = '.$user_id.' and fin.storno is null');
	$db_sum_amount = 0;
	if ($vysledek2)
	{
		if ($zaznam2=MySQL_Fetch_Array($vysledek2))
		{
			$db_sum_amount = $zaznam2['amount'];
		}
	}
	if ($amount > $db_sum_amount)
	{
		$result = 'Nemáte dostatek pěnez pro převod .';
		$can_send = false;
	}

	if ($amount < 0)
	{
		$result = 'Nelze převést zápornou částku.';
		$can_send = false;
	}
	
	if ($can_send)
	{
		//pridani informace, kdo komu penize poslal, pridava se do poznamky
		$note = createFinanceNoteFromTo($id_from, $id_to).$note;
		//odecist penize z uctu ODKUD
		createPayment($id_from, $id_from, -$amount, $note, null, null);
		//pripsat penize na ucet KOMU
		createPayment($id_from, $id_to, $amount, $note, null, null);
		$result = 'Byla převedena částka : '.$amount;
	}
	Print_Action_Result($result);
}
//---------- KONEC BLOK KODU PRO FINANCE ----------//

require_once ('./user_finance.inc.php');
?>
<BR>
</CENTER>