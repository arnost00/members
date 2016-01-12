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
	$amount = abs((int)$amount); // only positive numbers
	$id_to = (int)$id_to;
	$note = (IsSet($note)) ? correct_sql_string($note) : '';

	//odecist penize z uctu ODKUD
	createPayment($id_from, $id_from, -$amount, $note, null, null);
	//pripsat penize na ucet KOMU
	createPayment($id_from, $id_to, $amount, $note, null, null);
}
//---------- KONEC BLOK KODU PRO FINANCE ----------//

require_once ('./user_finance.inc.php');
?>
<BR>
</CENTER>