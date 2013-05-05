<?php /* maly trener - zobrazeni detailu financi pro clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
if (!IsLoggedSmallManager() && !IsLoggedManager() && !IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

//$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

include ("./common.inc.php");
include_once './payment.inc.php'; // pomocne funkce a javascript pro finance

// vytvorit platbu - out nebo in
if (IsSet($payment) && IsLoggedFinance())
{

 	$editor_id = $usr->user_id;

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


include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common_user.inc.php");
include ("./ctable.inc.php");
DrawPageTitle('Finance èlena');
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
	include ("./user_finance_update.inc.php");
} else if (IsSet($storno) && $storno == "storno" && IsLoggedFinance())
{
	$set_back_button = true;
	include ("./user_finance_storno.inc.php");
} else
{
	$set_back_button = false;
	$user_id = (IsSet($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;
	include ("./user_finance.inc.php");
	
	if (IsLoggedFinance())
	{
	?>
<hr>
	<?
		include ("./user_finance_out.inc.php");
	?>
<hr>
	<?
		include ("./user_finance_in.inc.php");
	}
}
?>
<hr>
<br>
<?
if (!$set_back_button && $user_id != 0)
	echo('<BUTTON onclick="location.href=\'user_finance_view.php?user_id='.$user_id.'\'; self.focus();">Obnov stránku</BUTTON>&nbsp;');
if ($set_back_button && $user_id != 0)
	echo('<BUTTON onclick="location.href=\'user_finance_view.php?user_id='.$user_id.'\'; self.focus();">Zpìt</BUTTON>&nbsp;');
?>
<BUTTON onclick="javascript:close_popup();">Zavøi okno</BUTTON><BR>
</CENTER>
</BODY>
</HTML>