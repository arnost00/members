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

if (IsSet($payment))
{

	$editor_id = $usr->user_id;
	$id_zavod = $race_id;
	$datum = null;
	if ($payment == "pay")
	{
		$i = 1;
		$var = "userid".$i;
		while (isset($$var))
		{
			$user_id = $$var;
			$var = "paymentid".$i;
			$payment_id = $$var;
			$var = "am".$i;
			$amount = $$var;
			$var = "nt".$i;
			$note = $$var;

			if ($amount != "")
			{
				if ($payment_id)
				{
					updatePayment($editor_id, $payment_id, $amount, $note);
				} else {
					createPayment($editor_id, $user_id, $amount, $note, $datum, $id_zavod);
				}
			}
			$i++;
			$var = "userid".$i;
		}
	}
}


include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");
include ("./common_user.inc.php");
include ("./ctable.inc.php");
DrawPageTitle('Finance závodu');
?>
<CENTER>
<script language="javascript">
<!--
	javascript:set_default_size(800,800);
//-->
</script>
<?

include ("./race_finance.inc.php");

?>
<hr>
<br>
<BUTTON onclick="javascript:close_popup();">Zavøi okno</BUTTON><BR>
</CENTER>
</BODY>
</HTML>