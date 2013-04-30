<?php /* zobrazeni reklamace pro platbu */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("./connect.inc.php");
require ("./sess.inc.php");
include ("./common.inc.php");
require_once 'payment.inc.php';

db_Connect();

$user_id = $usr->user_id;

// vytvorit reklamaci, pokud byl odeslan formular
if (IsSet($claim_text) and IsSet($payment_id))
{	
 	//vytazeni posledni reklamace pro tuto platbu
 	//pokud je uzivatel stejny jako prihlaseny, tak to bude update
 	//pokud je jiny, tak je to insert	
 	@$result_last_claim = MySQL_Query("select id, user_id, payment_id, text, date from ".TBL_CLAIM." c where c.payment_id = ".$payment_id." order by date desc LIMIT 1");
 	$record_last_claim = MySQL_Fetch_Array($result_last_claim);
 	if ($user_id == $record_last_claim['user_id'])
	{
		updateClaim($record_last_claim['id'], $claim_text);
	} else {
		createClaim($user_id, $payment_id, $claim_text);
	}
}


include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common_user.inc.php");
include ("./ctable.inc.php");
DrawPageTitle('Reklamace plateb');

@$result_claims = MySQL_Query("select id, user_id, payment_id, text, date from ".TBL_CLAIM." c where c.payment_id = ".$payment_id." order by date desc");
$record_claims = MySQL_Fetch_Array($result_claims);
if ($record_claims != null) mysql_data_seek($result_claims, 0);
($user_id == $record_claims['user_id'])?$actual_text = $record_claims['text']:$actual_text="";

?>

<form class="form" action="?" method="post">
	<fieldset>
		<label for="claim_text" id="label_claim_text">Co se ti nelíbí?</label>
		<textarea rows="3" id="claim_text" name="claim_text"><?=$actual_text?></textarea>
		<input type="hidden" id="payment_id" name="payment_id" value="<?=$payment_id;?>"/>
		<button type="submit">Odešli</button>
	</fieldset>
</form>

<hr>
<?
DrawPageTitle('Historie');
while ($record_claims = MySQL_Fetch_Array($result_claims))
{
	echo "<div class=\"claim-history\">".$record_claims['text']."</div>";
}

?>

<style>
div.claim-history {
	border-bottom: 2px solid grey;
}
textarea#claim_text {
	width: 100%;
}
</style>