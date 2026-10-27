<?php /* zobrazeni reklamace pro platbu */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once 'payment.inc.php';

if (!IsLogged())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

db_Connect();

$user_id = (int)$usr->user_id;
$payment_id = (isset($_REQUEST['payment_id']) && is_numeric($_REQUEST['payment_id'])) ? (int)$_REQUEST['payment_id'] : 0;

$pay_res = query_db("SELECT id, id_users_user FROM ".TBL_FINANCE." WHERE id = $payment_id LIMIT 1");
$pay_row = $pay_res ? mysqli_fetch_assoc($pay_res) : null;
$owns_payment = $pay_row && $user_id > 0 && (int)$pay_row['id_users_user'] === $user_id;

if (!$pay_row || (!IsLoggedFinance() && !$owns_payment))
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$submit = $_POST['submit'] ?? null;
$close = $_POST['close'] ?? null;
$claim_text = $_POST['claim_text'] ?? null;

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
		echo "<script>window.close();</script>";
 	} else {
 		if ($record_last_claim && $user_id == $record_last_claim['user_id'])
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

$payment_detail = "Zadal: ".htmlspecialchars((string)$record_payment['user_name'], ENT_QUOTES, 'UTF-8')."<br/>";
$payment_detail .= "Datum: ".htmlspecialchars((string)formatDate($record_payment['fin_date']), ENT_QUOTES, 'UTF-8')."<br/>";
$payment_detail .= "Částka: ".htmlspecialchars((string)$record_payment['fin_amount'], ENT_QUOTES, 'UTF-8')."<br/>";
$payment_detail .= "Poznámka: ".htmlspecialchars((string)$record_payment['fin_note'], ENT_QUOTES, 'UTF-8')."<br/><br/>";
echo $payment_detail;


$query = "SELECT c.user_id, c.payment_id, c.text, DATE_FORMAT( c.date, '%e.%c.%Y %k:%i' ) date, u.sort_name user_name"
	." FROM ".TBL_CLAIM." c LEFT JOIN ".TBL_USER." u ON u.id = c.user_id"
	." where c.payment_id = ".$payment_id." order by c.date desc";

@$result_claims = query_db($query);
$record_claims = mysqli_fetch_array($result_claims);

$actual_text = "";
if ($record_claims != null)
{
	mysqli_data_seek ($result_claims, 0);
	if ($user_id == $record_claims['user_id'])
		$actual_text = $record_claims['text'];
}

?>

<form class="form" action="?" method="post">
	<fieldset style="border: none;">
		<label for="claim_text" id="label_claim_text">Co se ti nelíbí?</label>
		<textarea rows="3" id="claim_text" name="claim_text"><?=htmlspecialchars((string)$actual_text, ENT_QUOTES, 'UTF-8')?></textarea>
		<input type="hidden" id="payment_id" name="payment_id" value="<?=(int)$payment_id;?>"/>
		<button type="submit" id="submit" name="submit">Odešli</button>
		<button type="submit" id="close" name="close">Uzavři reklamaci</button>
	</fieldset>
</form>

<hr>
<?
DrawPageTitle('Historie');
while ($record_claims = mysqli_fetch_array($result_claims))
{
	echo "<div class=\"claim-history\"><div class=\"claim-date-name\">".htmlspecialchars((string)$record_claims['date'], ENT_QUOTES, 'UTF-8')." - ".htmlspecialchars((string)$record_claims['user_name'], ENT_QUOTES, 'UTF-8')."</div><div class=\"claim-history-text\">".htmlspecialchars((string)$record_claims['text'], ENT_QUOTES, 'UTF-8')."</div></div>";
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
