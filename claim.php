<?php /* zobrazeni reklamace pro platbu */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once 'payment.inc.php';

db_Connect();

$user_id = $usr->user_id;

// vytvorit reklamaci, pokud byl odeslan formular
if (IsSet($submit) or IsSet($close))
{
 	//vytazeni posledni reklamace pro tuto platbu
 	//pokud je uzivatel stejny jako prihlaseny, tak to bude update
 	//pokud je jiny, tak je to insert	
	$query = "select id, user_id, payment_id, text, date from ".TBL_CLAIM." c where c.payment_id = ".$payment_id." order by date desc LIMIT 1";
 	@$result_last_claim = query_db($query);
 	$record_last_claim = mysqli_fetch_array($result_last_claim);
 	if (IsSet($close))
 	{
 		closeClaim($record_last_claim['id'], $payment_id);
 	} else {
 		if ($user_id == $record_last_claim['user_id'])
 		{
 			updateClaim($record_last_claim['id'], $claim_text);
 		} else {
 			createClaim($user_id, $payment_id, $claim_text);
 		}
 	}
}

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
require_once ("./common_user.inc.php");
require_once ("./ctable.inc.php");
DrawPageTitle('Reklamace platby');

$query = "select u.sort_name user_name, f.date fin_date, f.amount fin_amount, f.note fin_note from ".TBL_FINANCE." f left join ".TBL_USER." u on u.id = f.id_users_editor"
	." where f.id = $payment_id";

@$result_payment = query_db($query);
$record_payment = mysqli_fetch_array($result_payment);

$payment_detail = "Zadal: ".$record_payment['user_name']."<br/>";
$payment_detail .= "Datum: ".formatDate($record_payment['fin_date'])."<br/>";
$payment_detail .= "Částka: ".$record_payment['fin_amount']."<br/>";
$payment_detail .= "Poznámka: ".$record_payment['fin_note']."<br/><br/>";
echo $payment_detail;


$query = "SELECT c.user_id, c.payment_id, c.text, DATE_FORMAT( c.date, '%e.%c.%Y %k:%i' ) date, u.sort_name user_name"
	." FROM ".TBL_CLAIM." c LEFT JOIN ".TBL_USER." u ON u.id = c.user_id"
	." where c.payment_id = ".$payment_id." order by c.date desc";
	
@$result_claims = query_db($query);
$record_claims = mysqli_fetch_array($result_claims);

if ($record_claims != null) mysqli_data_seek ($result_claims, 0);
$actual_text = "";
if ($user_id == $record_claims['user_id'])
{
	$actual_text = $record_claims['text'];
}

?>

<form class="form" action="?" method="post">
	<fieldset style="border: none;">
		<label for="claim_text" id="label_claim_text">Co se ti nelíbí?</label>
		<textarea rows="3" id="claim_text" name="claim_text"><?=$actual_text?></textarea>
		<input type="hidden" id="payment_id" name="payment_id" value="<?=$payment_id;?>"/>
		<button type="submit" id="submit" name="submit">Odešli</button>
		<button type="submit" id="close" name="close">Uzavři reklamaci</button>
	</fieldset>
</form>

<hr>
<?
DrawPageTitle('Historie');
while ($record_claims = mysqli_fetch_array($result_claims))
{
	echo "<div class=\"claim-history\"><div class=\"claim-date-name\">".$record_claims['date']." - ".$record_claims['user_name']."</div><div class=\"claim-history-text\">".$record_claims['text']."</div></div>";
}

?>

<style>
div.claim-history {
  	border-bottom: 2px solid grey;
	margin-bottom: 10px;
}
div.claim-date-name {
	display: inline-block;
}
div.claim-history-text {
	display: block;
	padding-left: 10px;
}
textarea#claim_text {
	width: 100%;
}
</style>