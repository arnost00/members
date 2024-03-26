<?php /* maly trener - zobrazeni detailu financi pro clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
if (!IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$race_id = (IsSet($race_id) && is_numeric($race_id)) ? (int)$race_id : 0;

db_Connect();

require_once './functions.php';
require_once './payment.inc.php'; // pomocne funkce a javascript pro finance
require_once './member.class.php'; // pomocne funkce pro uzivatele

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
					updatePayment($editor_id, $payment_id, $id_zavod, $amount, $note);
				} else {
					createPayment($editor_id, $user_id, $amount, $note, $datum, $id_zavod);
				}
			}

			$var = "cat".$i;
			$cat = $$var;
			
			$user = new Member($user_id);
			$user->updateCategoryOnRace($race_id, $cat);
			$i++;
			$var = "userid".$i;
		}
	}
}


require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common.inc.php");
require_once ("./common_user.inc.php");
require_once ("./ctable.inc.php");
DrawPageTitle('Finance závodu');
?>
<CENTER>
<script language="javascript">
<!--
	javascript:set_default_size(800,800);
//-->
</script>
<?

require_once ("./race_finance.inc.php");

?>
<hr>
<br>
<BUTTON onclick="javascript:close_popup();">Zavři okno</BUTTON><BR>
</CENTER>
<?
HTML_Footer();
?>