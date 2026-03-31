<?php
if (!defined('__HIDE_TEST__')) exit;

/**
 * Raiffeisenbank API synchronization script
 */

function cron_rb_sync_log($msg) {
    global $g_baseadr; // Just using as reference, assuming we have a logs folder
    $log_file = dirname(__FILE__) . '/logs/rb_sync_log.txt';
    $timestamp = date('d.m.Y H:i:s');
    $line = "[$timestamp] $msg\n";
    file_put_contents($log_file, $line, FILE_APPEND);
}

// Helper to generate UUID v4 for X-Request-Id
function generate_uuid_v4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function run_rb_sync() {
    global $g_bank_cert_path, $g_bank_cert_pass, $g_bank_client_id, $g_bank_account_number;
    global $db_conn;

    // Ensure database connection
    db_Connect();
    
    cron_rb_sync_log("Starting RB sync run.");
    
    if (empty($g_bank_cert_path) || empty($g_bank_client_id) || empty($g_bank_account_number)) {
        cron_rb_sync_log("Error: Bank configuration is missing (cert path, client ID, or account number).");
        return;
    }
    
    // Hourly rate limiting
    $last_sync_file = dirname(__FILE__) . '/logs/last_rb_sync.txt';
    $current_time = time();
    if (file_exists($last_sync_file)) {
        $last_sync_time = (int)file_get_contents($last_sync_file);
        if ($current_time - $last_sync_time < 10) {
            cron_rb_sync_log("Skipping run. Last sync was less than an hour ago.");
            return;
        }
    }
    
    // Fetch last 24 hours of transactions to be safe, filtering by time
    $date_from = date('Y-m-d', strtotime('-30 day'));
    $date_to = date('Y-m-d');
    
    // Call Raiffeisenbank API
    // Append query params. We will urlencode the date
    $url = "https://api.rb.cz/rbcz/premium/api/accounts/{$g_bank_account_number}/CZK/transactions?from=" . urlencode($date_from) . "&to=" . urlencode($date_to);
    
    cron_rb_sync_log("Fetching transactions from RB API: $url");

    $cert_path = $g_bank_cert_path;
    if (!file_exists($cert_path) && file_exists(dirname(__FILE__) . '/' . ltrim($cert_path, '/'))) {
        $cert_path = dirname(__FILE__) . '/' . ltrim($cert_path, '/');
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, $cert_path);
    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $g_bank_cert_pass);
    curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
    
    $request_id = generate_uuid_v4();
    
    // Raiffeisen headers
    $headers = array(
        "X-Request-Id: " . $request_id,
        "X-IBM-Client-Id: " . $g_bank_client_id,
        "Accept: application/json"
    );
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false || $http_code !== 200) {
        cron_rb_sync_log("Error fetching API. HTTP Code: $http_code. cURL Error: $curl_error. Response: " . (is_string($response) ? $response : ''));
        return;
    }
    
    $data = json_decode($response, true);
    if (!isset($data['transactions']) || !is_array($data['transactions'])) {
        cron_rb_sync_log("Error: JSON does not contain 'transactions' array.");
        return;
    }
    
    $transactions = $data['transactions'];
    $tx_count = count($transactions);
    cron_rb_sync_log("Fetched $tx_count transactions from API.");
    
    // Get all users to match against variable symbol
    $users_result = query_db("SELECT id, reg FROM " . TBL_USER . " WHERE hidden = 0");
    $user_regs = array();
    if ($users_result) {
        while ($u = mysqli_fetch_assoc($users_result)) {
            // Usually reg number is formatted, e.g., 'ZBM1234'. We need the last 4 digits.
            if (preg_match('/(\d{4})$/', $u['reg'], $matches)) {
                $user_regs[$matches[1]] = $u['id'];
            }
        }
    }
    
    $processed = 0;
    $orphaned = 0;
    $duplicates = 0;
    
    foreach ($transactions as $tx) {
        // Extract basic data (adjust based on actual RB JSON schema)
        $tx_id = isset($tx['entryReference']) ? mysqli_real_escape_string($db_conn, $tx['entryReference']) : '';
        $amount = isset($tx['amount']['value']) ? (float)$tx['amount']['value'] : 0.0;
        $currency = isset($tx['amount']['currency']) ? mysqli_real_escape_string($db_conn, $tx['amount']['currency']) : 'CZK';
        
        // RB Premium API fields
        $remittance = isset($tx['entryDetails']['transactionDetails']['remittanceInformation']) ? $tx['entryDetails']['transactionDetails']['remittanceInformation'] : array();
        
        $vs = isset($remittance['creditorReferenceInformation']['variable']) ? mysqli_real_escape_string($db_conn, $remittance['creditorReferenceInformation']['variable']) : '';
        $cs = isset($remittance['creditorReferenceInformation']['constant']) ? mysqli_real_escape_string($db_conn, $remittance['creditorReferenceInformation']['constant']) : '';
        $ss = isset($remittance['creditorReferenceInformation']['specific']) ? mysqli_real_escape_string($db_conn, $remittance['creditorReferenceInformation']['specific']) : '';
        $msg = isset($remittance['originatorMessage']) ? mysqli_real_escape_string($db_conn, $remittance['originatorMessage']) : '';
        
        if (empty($tx_id)) continue;
        if ($amount <= 0) continue;
        
        // Check if transaction exists
        $check_res = query_db("SELECT id FROM " . TBL_BANK_TRANSACTIONS . " WHERE transaction_id = '$tx_id'");
        if ($check_res && mysqli_num_rows($check_res) > 0) {
            $duplicates++;
            continue;
        }
        
        // Match logic
        $status = 'ORPHAN';
        $finance_id_sql = "NULL"; // finance_id is filled later when entered into system, but we can prepare it
        
        $matched_user_id = null;
        
        // Remove leading zeros from VS if any, or just match exactly. 
        // For a 4 digit club registration number, if VS is "1234" it should match user reg "ZBM1234" (digit 1234).
        // Let's strip leading zeros just in case, or pad it
        $clean_vs = ltrim($vs, '0');
        if (strlen($clean_vs) > 0 && isset($user_regs[str_pad($clean_vs, 4, '0', STR_PAD_LEFT)])) {
            $status = 'PROCESSED'; // Means it matches a user, though actual processing to fin table happens later
            $matched_user_id = $user_regs[str_pad($clean_vs, 4, '0', STR_PAD_LEFT)];
            $processed++;
        } else {
            $orphaned++;
        }
        
        if ($status === 'PROCESSED' && $matched_user_id) {
            $editor_id = 0; // system
            $note = "Banka: VS " . $vs . " " . $msg;
            $datum = date('Y-m-d');
            $note_esc = correct_sql_string($note);
            $query_fin = "INSERT INTO ".TBL_FINANCE." (id_users_editor, id_users_user, amount, note, date, id_zavod) 
                          VALUES ($editor_id, $matched_user_id, $amount, '$note_esc', '$datum', NULL)";
            if (query_db($query_fin)) {
                $finance_id_sql = mysqli_insert_id($db_conn);
            }
        }
        
        // Insert
        $sql = "INSERT INTO " . TBL_BANK_TRANSACTIONS . " 
                (transaction_id, amount, currency, variable_symbol, constant_symbol, specific_symbol, originator_message, status, finance_id) 
                VALUES 
                ('$tx_id', $amount, '$currency', '$vs', '$cs', '$ss', '$msg', '$status', $finance_id_sql)";
        
        if (!query_db($sql)) {
            cron_rb_sync_log("Error inserting transaction $tx_id: " . mysqli_error($db_conn));
        }
    }
    
    cron_rb_sync_log("Sync finished. Processed/Matched: $processed, Orphaned: $orphaned, Duplicates skipped: $duplicates.");
    
    // Update last sync time
    file_put_contents($last_sync_file, $current_time);
}

// Execute
run_rb_sync();
?>