<?php /* zobrazeni prirazeni nesparovane platby */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once 'payment.inc.php';

if (!IsLoggedFinance()) {
    header("location: ".$g_baseadr."error.php?code=21");
    exit;
}

db_Connect();
$user_id = (int)$usr->user_id;

$tx_id = (isset($_REQUEST['tx_id']) && is_numeric($_REQUEST['tx_id'])) ? (int)$_REQUEST['tx_id'] : 0;

if (isset($_POST['submit'])) {
    $assign_user_id = (isset($_POST['assign_user_id']) && is_numeric($_POST['assign_user_id'])) ? (int)$_POST['assign_user_id'] : 0;

    $user_ok = ($assign_user_id > 0)
        ? query_db("SELECT id FROM ".TBL_USER." WHERE id = $assign_user_id AND hidden = 0 LIMIT 1")
        : false;
    if (!$user_ok || !mysqli_fetch_assoc($user_ok)) {
        header("location: ".$g_baseadr."error.php?code=21");
        exit;
    }

    $query = "SELECT * FROM ".TBL_BANK_TRANSACTIONS." WHERE id = $tx_id AND status = 'ORPHAN'";
    $res = query_db($query);
    if ($res && ($record = mysqli_fetch_assoc($res))) {
        $amount = $record['amount'];
        $note = "Banka: VS " . $record['variable_symbol'] . " " . $record['originator_message'];
        $date = date('d.m.Y', strtotime($record['created_at']));

        $fin_id = createPayment($user_id, $assign_user_id, $amount, $note, $date, null);

        if ($fin_id > 0) {
            query_db("UPDATE ".TBL_BANK_TRANSACTIONS." SET status='PROCESSED', finance_id=$fin_id WHERE id=$tx_id");
        }

        echo "<script>window.opener.location.reload(); window.close();</script>";
        exit;
    }
}

require_once ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
DrawPageTitle('Přiřazení bankovní transakce');

$query = "SELECT * FROM ".TBL_BANK_TRANSACTIONS." WHERE id = $tx_id";
$res = query_db($query);
$record = $res ? mysqli_fetch_assoc($res) : null;
if (!$record) {
    echo "Nenalezeno.";
    HTML_Footer();
    exit;
}

echo "Částka: " . htmlspecialchars((string)$record['amount'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars((string)$record['currency'], ENT_QUOTES, 'UTF-8') . "<br>";
echo "VS: " . htmlspecialchars((string)$record['variable_symbol'], ENT_QUOTES, 'UTF-8') . "<br>";
echo "Zpráva: " . htmlspecialchars((string)$record['originator_message'], ENT_QUOTES, 'UTF-8') . "<br>";
echo "Datum: " . htmlspecialchars(date('d.m.Y H:i:s', strtotime($record['created_at'])), ENT_QUOTES, 'UTF-8') . "<br><br>";

?>
<form class="form" action="?" method="post">
	<fieldset style="border: none;">
		<label for="assign_user_id">Uživatel:</label>
		<select name="assign_user_id" id="assign_user_id">
<?php
$query = "SELECT id, sort_name, reg FROM " . TBL_USER . " WHERE hidden = 0 ORDER BY sort_name ASC";
$res = query_db($query);
while ($u = mysqli_fetch_assoc($res)) {
    echo "<option value='".(int)$u['id']."'>".htmlspecialchars((string)$u['sort_name'], ENT_QUOTES, 'UTF-8')." (".htmlspecialchars((string)$u['reg'], ENT_QUOTES, 'UTF-8').")</option>\n";
}
?>
		</select>
		<input type="hidden" name="tx_id" value="<?=(int)$tx_id;?>"/>
		<button type="submit" id="submit" name="submit">Přiřadit</button>
	</fieldset>
</form>

<?php
HTML_Footer();
?>
