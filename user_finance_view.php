<?php /* maly trener - zobrazeni detailu financi pro clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
if (!IsLoggedSmallManager() && !IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

include_once './payment.inc.php'; // pomocne funkce a javascript pro finance

// vytvorit platbu - out nebo in
if (IsSet($payment))
{

 	$editor_id = $usr->user_id;
 	
	if ($payment == "out" or $payment == "in")	
	{		
		$payment == "out"?$amount = -$amount:$amount;
		createPayment($editor_id, $user_id, $amount, $note, $datum, $id_zavod);
	}
	if ($payment == "storno")
	{
		stornoPayment($editor_id, $trn_id, $storno_note);
	}
	if ($payment == "update")
	{
		updatePayment($editor_id, $trn_id, $amount, $note);
	}
}


include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");
include ("./ctable.inc.php");
DrawPageTitle('Finance èlena', false);
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

if (IsSet($change) and $change == "change")
{
	include ("./user_finance_update.inc.php");
} else if (IsSet($storno) and $storno == "storno")
{
	include ("./user_finance_storno.inc.php");
} else
{
	include ("./user_finance.inc.php");
	?>
	<hr>
	<?
	include ("./user_finance_out.inc.php");
	?>
	<hr>
	<?
	include ("./user_finance_in.inc.php");
}
?>
<hr>
<br>
<BUTTON onclick="javascript:close_popup();">Zpìt</BUTTON><BR>
</CENTER>
</BODY>
</HTML>