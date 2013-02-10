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

$race_id = (IsSet($race_id) && is_numeric($race_id)) ? (int)$race_id : 0;

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
		stornoPayment($editor_id, $trn_id, $datum, $storno_note);
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
DrawPageTitle('Finance z�vodu', false);
?>
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
	include ("./race_finance.inc.php");
	?>
	<hr>
	<?
	//include ("./user_finance_out.inc.php");
	?>
	<hr>
	<?
	//include ("./user_finance_in.inc.php");
}
?>
<hr>
<br>
<BUTTON onclick="javascript:close_popup();">Zp�t</BUTTON><BR>
</CENTER>
</BODY>
</HTML>