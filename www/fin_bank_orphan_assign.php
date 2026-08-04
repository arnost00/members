<?php /* zobrazeni prirazeni nesparovane platby */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST, EXTR_SKIP);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./common.inc.php");
require_once 'payment.inc.php';

db_Connect();
$user_id = $usr->user_id;

$tx_id = (IsSet($tx_id) && is_numeric($tx_id)) ? (int)$tx_id : 0;

if (IsSet($submit)) {
    // Process the assignment
    $assign_user_id = (int)$_POST['assign_user_id'];
    
    $query = "SELECT * FROM ".TBL_BANK_TRANSACTIONS." WHERE id = $tx_id AND status = 'ORPHAN'";
    $res = query_db($query);
    if ($record = mysqli_fetch_assoc($res)) {
        $amount = $record['amount'];
        $note = "Banka: VS " . $record['variable_symbol'] . " " . $record['originator_message'];
        $date = date('d.m.Y', strtotime($record['created_at']));
        
        $fin_id = createPayment($user_id, $assign_user_id, $amount, $note, $date, null);
        
        if ($fin_id > 0) {
            // update bank transaction
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
$record = mysqli_fetch_assoc($res);
if (!$record) {
    echo "Nenalezeno.";
    HTML_Footer();
    exit;
}

echo "Částka: " . $record['amount'] . " " . $record['currency'] . "<br>";
echo "VS: " . $record['variable_symbol'] . "<br>";
echo "Zpráva: " . $record['originator_message'] . "<br>";
echo "Datum: " . date('d.m.Y H:i:s', strtotime($record['created_at'])) . "<br><br>";

?>
<form class="form" action="?" method="post">
	<fieldset style="border: none;">
		<label for="assign_user_id">Uživatel:</label>
		<select name="assign_user_id" id="assign_user_id">
<?php
$query = "SELECT id, sort_name, reg FROM " . TBL_USER . " WHERE hidden = 0 ORDER BY sort_name ASC";
$res = query_db($query);
while ($u = mysqli_fetch_assoc($res)) {
    echo "<option value='".$u['id']."'>".$u['sort_name']." (".$u['reg'].")</option>\n";
}
?>
		</select>
		<input type="hidden" name="tx_id" value="<?=$tx_id;?>"/>
		<button type="submit" id="submit" name="submit">Přiřadit</button>
	</fieldset>
</form>

<?php
HTML_Footer();
?>