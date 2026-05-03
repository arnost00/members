<?php
define('__HIDE_TEST__', '_KeAr_PHP_WEB_');

require_once('./cfg/_globals.php');
require_once('./connect.inc.php');
require_once('./sess.inc.php');
require_once('./common.inc.php');
require_once('./common_fin.inc.php');

if (!IsLoggedFinance()) {
    header("Location: index.php");
    exit;
}

require_once('./cron_bank_sync.php');

db_Connect();

$message = "";

// Zpracování importu
if (isset($_POST['action']) && $_POST['action'] === 'import_confirmed') {
    if (isset($_POST['tx']) && is_array($_POST['tx'])) {
        $imported = 0;
        $failed = 0;
        $editor_id = $usr->user_id;
        $datum = date('Y-m-d');
        global $db_conn;
        
        foreach ($_POST['tx'] as $tx_id => $data) {
            if (isset($data['import']) && $data['import'] == '1') {
                $amount = (float)$data['amount'];
                $vs = correct_sql_string($data['vs']);
                $msg = correct_sql_string($data['msg']);
                $matched_user_id = isset($data['matched_user_id']) ? (int)$data['matched_user_id'] : 0;
                $tx_id_esc = correct_sql_string($tx_id);
                
                // Kontrola zda transakce existuje a neni prirazena
                $check_res = query_db("SELECT id FROM " . TBL_BANK_TRANSACTIONS . " WHERE transaction_id = '$tx_id_esc' AND finance_id IS NULL");
                if ($check_res && mysqli_num_rows($check_res) > 0) {
                    $row_tx = mysqli_fetch_assoc($check_res);
                    $db_tx_id = $row_tx['id'];

                    if ($matched_user_id > 0) {
                        $note = "Banka: VS " . $vs . " " . $msg;
                        $note_esc = correct_sql_string($note);
                        $query_fin = "INSERT INTO ".TBL_FINANCE." (id_users_editor, id_users_user, amount, note, date, id_zavod) 
                                      VALUES ($editor_id, $matched_user_id, $amount, '$note_esc', '$datum', NULL)";
                        if (query_db($query_fin)) {
                            $finance_id_sql = mysqli_insert_id($db_conn);
                            
                            // Aktualizujeme zaznam v tabulce bankovnich transakci
                            $sql_upd = "UPDATE " . TBL_BANK_TRANSACTIONS . " SET finance_id = $finance_id_sql WHERE id = $db_tx_id";
                            if (query_db($sql_upd)) {
                                $imported++;
                            } else {
                                $failed++;
                            }
                        } else {
                            $failed++;
                        }
                    } else {
                        $failed++;
                    }
                } else {
                    // Transakce nenalezena nebo uz prirazena
                    $failed++;
                }
            }
        }
        $message = "Import byl úspěšně dokončen. Naimportováno: $imported, Chyb: $failed.";
    } else {
        $message = "Nebyly vybrány žádné transakce k importu.";
    }
}

// Načtení dat z API (vlozi nove transakce do DB)
$days_back = 7;
global $g_bank_sync_start_date;
if (!empty($g_bank_sync_start_date)) {
    try {
        $start_date = new DateTime($g_bank_sync_start_date);
        $now = new DateTime();
        $diff = $now->diff($start_date);
        $days_back = $diff->days;
    } catch (Exception $e) {
        $days_back = 7;
    }
}
if ($days_back < 1) $days_back = 1;
if ($days_back > 88) $days_back = 88;

run_bank_sync($days_back);

// Ziskani neprirazenych transakci z DB s nastavenym matched_user_id z VS
$transactions = [];
$res_tx = query_db("SELECT * FROM " . TBL_BANK_TRANSACTIONS . " WHERE status = 'PROCESSED' AND finance_id IS NULL ORDER BY created_at DESC");
if ($res_tx) {
    while ($row = mysqli_fetch_assoc($res_tx)) {
        // Find matched user id by VS again, or we can store it in TBL_BANK_TRANSACTIONS. 
        // We didn't store matched_user_id in TBL_BANK_TRANSACTIONS, we can deduce it from VS here like before
        $vs = $row['variable_symbol'];
        $clean_vs = ltrim($vs, '0');
        $matched_user_id = null;
        if (strlen($clean_vs) > 0) {
            $padded_vs = str_pad($clean_vs, 4, '0', STR_PAD_LEFT);
            $user_res = query_db("SELECT id FROM " . TBL_USER . " WHERE hidden = 0 AND reg LIKE '%$padded_vs'");
            if ($user_res && mysqli_num_rows($user_res) > 0) {
                $user_row = mysqli_fetch_assoc($user_res);
                $matched_user_id = $user_row['id'];
            }
        }

        $transactions[] = [
            'transaction_id' => $row['transaction_id'],
            'amount' => $row['amount'],
            'currency' => $row['currency'],
            'vs' => $row['variable_symbol'],
            'cs' => $row['constant_symbol'],
            'ss' => $row['specific_symbol'],
            'msg' => $row['originator_message'],
            'status' => $row['status'],
            'matched_user_id' => $matched_user_id,
            'created_at' => $row['created_at']
        ];
    }
}

// Získání registračních čísel pro přiřazené uživatele
$matched_users = [];
if (is_array($transactions)) {
    $user_ids_to_fetch = [];
    foreach ($transactions as $tx) {
        if (!empty($tx['matched_user_id'])) {
            $user_ids_to_fetch[] = (int)$tx['matched_user_id'];
        }
    }
    if (!empty($user_ids_to_fetch)) {
        $user_ids_to_fetch = array_unique($user_ids_to_fetch);
        $ids_str = implode(',', $user_ids_to_fetch);
        $res = query_db("SELECT id, reg, jmeno, prijmeni FROM " . TBL_USER . " WHERE id IN ($ids_str)");
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $matched_users[$row['id']] = $row;
            }
        }
    }
}

$g_page_title = 'Import z banky';
require_once('./header.inc.php');
?>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD rowspan=2 width="180" bgcolor="<? echo $g_colors['body_bgcolor']; ?>" valign=top align=left>
<!-- navigace -->
<?php require_once('./nav.inc.php'); ?>
<!-- navigace -->
</TD>
<TD rowspan=2 width="2%"></TD>
<TD width="90%" ALIGN="left" valign="top">

    <h2>Import plateb z banky</h2>

    <?php if ($message): ?>
        <div class="msg"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (is_array($transactions) && count($transactions) > 0): ?>
        <form method="POST" action="fin_bank_sync.php">
            <input type="hidden" name="action" value="import_confirmed">
            
            <table>
                <thead>
                    <tr>
                        <th>Import?</th>
                        <th>Datum</th>
                        <th>Částka</th>
                        <th>Měna</th>
                        <th>VS</th>
                        <th>Zpráva</th>
                        <th>Stav</th>
                        <th>Reg. číslo (jméno)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_processed = false;
                    foreach ($transactions as $tx): 
                        // Only displaying PROCESSED matched ones, ORPHAN are not fetched from DB for this view
                        $has_processed = true;
                    ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="tx[<?php echo htmlspecialchars($tx['transaction_id']); ?>][import]" value="1" checked>
                                <input type="hidden" name="tx[<?php echo htmlspecialchars($tx['transaction_id']); ?>][amount]" value="<?php echo htmlspecialchars($tx['amount']); ?>">
                                <input type="hidden" name="tx[<?php echo htmlspecialchars($tx['transaction_id']); ?>][vs]" value="<?php echo htmlspecialchars($tx['vs']); ?>">
                                <input type="hidden" name="tx[<?php echo htmlspecialchars($tx['transaction_id']); ?>][msg]" value="<?php echo htmlspecialchars($tx['msg']); ?>">
                                <input type="hidden" name="tx[<?php echo htmlspecialchars($tx['transaction_id']); ?>][matched_user_id]" value="<?php echo htmlspecialchars($tx['matched_user_id'] ?? ''); ?>">
                            </td>
                            <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($tx['created_at'] ?? ''))); ?></td>
                            <td><?php echo htmlspecialchars($tx['amount']); ?></td>
                            <td><?php echo htmlspecialchars($tx['currency']); ?></td>
                            <td><?php echo htmlspecialchars($tx['vs']); ?></td>
                            <td><?php echo htmlspecialchars($tx['msg']); ?></td>
                            <td>
                                <?php if ($tx['status'] === 'PROCESSED'): ?>
                                    <span class="status-processed">Přiřazeno</span>
                                <?php else: ?>
                                    <span class="status-orphan">Sirotek</span>
                                <?php endif; ?>
                            </td>
                            <td><?php 
                                $uid = $tx['matched_user_id'] ?? 0;
                                if ($uid > 0 && isset($matched_users[$uid])) {
                                    $u = $matched_users[$uid];
                                    echo htmlspecialchars($u['reg'] . ' (' . $u['jmeno'] . ' ' . $u['prijmeni'] . ')');
                                } else {
                                    echo '-';
                                }
                            ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <br>
            <?php if ($has_processed): ?>
                <button type="submit" class="btn">Provést import vybraných</button>
            <?php else: ?>
                <p>Nebyly nalezeny žádné přiřazené transakce k importu.</p>
            <?php endif; ?>
            <a href="index.php?id=800" class="btn" style="background-color: #f44336;">Zavřít</a>
        </form>
    <?php else: ?>
        <p>Nenalezeny žádné nové transakce.</p>
        <a href="index.php?id=800" class="btn" style="background-color: #f44336;">Zavřít</a>
    <?php endif; ?>

</TD>
<TD rowspan=2 width="2%"></TD>
</TR>
<TR><TD ALIGN=CENTER VALIGN=bottom height="15"></TD></TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?php require_once('./footer.inc.php'); ?>
<!-- Footer End -->
</TD></TR>
</TABLE>
<?php
HTML_Footer();
?>
