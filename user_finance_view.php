<?php /* maly trener - zobrazeni detailu financi pro clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
if (!IsLoggedSmallManager() && !IsLoggedManager() && !IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

require_once ("./common.inc.php");
require_once './payment.inc.php'; // pomocne funkce a javascript pro finance

// vytvorit platbu - out nebo in
if (IsSet($payment) && IsLogged())
{

 	$editor_id = $usr->user_id;
 	
 	if ($payment == "both" && $id_to != -1)
 	{
	
		//pridani informace, kdo komu penize poslal, pridava se do poznamky
		$note = createFinanceNoteFromTo($id_from, $id_to).$note;
		
 		//odecist penize z uctu ODKUD
 		createPayment($id_from, $id_from, -$amount, $note, null, null);
 		//pripsat penize na ucet KOMU
 		createPayment($id_from, $id_to, $amount, $note, null, null);
 	}
	if ($payment == "out" or $payment == "in")
	{
		$payment == "out"?$amount = -$amount:$amount;
		$user_id = (IsSet($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;
		createPayment($editor_id, $user_id, $amount, $note, $datum, $id_zavod);
	}
	if ($payment == "storno")
	{
		stornoPayment($editor_id, $trn_id, $storno_note);
	}
	if ($payment == "update")
	{
		updatePayment($editor_id, $trn_id, $id_zavod, $amount, $note);
	}
}


require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
//require_once ("./common_user.inc.php");
require_once ("./ctable.inc.php");
DrawPageTitle('Finance člena');
?>

<script>
//automaticky refresh stranky, ze ktere bylo toto okno volano
window.onunload = refreshParent();
function refreshParent() {
	window.opener.location.reload();
}
</script>
<script src="./payment.inc.js"></script>
<CENTER>
<?

if (IsSet($change) && $change == "change" && IsLoggedFinance())
{
	$set_back_button = true;
	require_once ("./user_finance_update.inc.php");
} else if (IsSet($storno) && $storno == "storno" && IsLoggedFinance())
{
	$set_back_button = true;
	require_once ("./user_finance_storno.inc.php");
} else
{
	$set_back_button = false;
	$user_id = (IsSet($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;
	
	require_once ("./user_finance.inc.php");
	
	if (IsLoggedFinance())
	{
	?>
<hr>
	<?
		require_once ("./user_finance_out.inc.php");
	?>
<hr>
	<?
		require_once ("./user_finance_in.inc.php");
	}
}
?>
<hr>
<br>
<?
if (!$set_back_button && $user_id != 0)
	echo('<BUTTON onclick="location.href=\'user_finance_view.php?user_id='.$user_id.'\'; self.focus();">Obnov stránku</BUTTON>&nbsp;');
if ($set_back_button && $user_id != 0)
	echo('<BUTTON onclick="location.href=\'user_finance_view.php?user_id='.$user_id.'\'; self.focus();">Zpět</BUTTON>&nbsp;');
?>
<BUTTON onclick="javascript:close_popup();">Zavři okno</BUTTON><BR>
</CENTER>
<?
HTML_Footer();
?>