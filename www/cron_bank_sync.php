<?php
if (!defined('__HIDE_TEST__')) exit;

/**
 * Generic Bank API synchronization script
 */

require_once dirname(__FILE__) . '/lib/BankConnectorInterface.php';
require_once dirname(__FILE__) . '/lib/AbstractBankConnector.php';
require_once dirname(__FILE__) . '/lib/RaiffeisenbankConnector.php';
require_once dirname(__FILE__) . '/lib/RaiffeisenbankMockConnector.php';

function cron_bank_sync_log($msg) {
    global $g_baseadr;
    $log_file = dirname(__FILE__) . '/logs/bank_sync_log.txt';
    $timestamp = date('d.m.Y H:i:s');
    $line = "[$timestamp] $msg\n";
    file_put_contents($log_file, $line, FILE_APPEND);
}

function run_bank_sync($days_back = 30) {
    global $g_bank_connector;
    global $db_conn;

    db_Connect();
    
    cron_bank_sync_log("Starting bank sync run.");
    
    if (empty($g_bank_connector) || !class_exists($g_bank_connector)) {
        cron_bank_sync_log("Error: Bank connector configuration is missing or invalid class '$g_bank_connector'.");
        return;
    }

    $connector = new $g_bank_connector();
    if (!$connector instanceof BankConnectorInterface) {
        cron_bank_sync_log("Error: Bank connector class '$g_bank_connector' must implement BankConnectorInterface.");
        return;
    }

    $transactions = $connector->getTransactions($days_back);
    
    if (empty($transactions)) {
        cron_bank_sync_log("No transactions fetched or error occurred in connector.");
        return [];
    }

    $tx_count = count($transactions);
    
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
    
    $parsed_transactions = array();

    foreach ($transactions as $tx) {
        $tx_id = $tx['transaction_id'];
        $amount = $tx['amount'];
        $currency = $tx['currency'];
        $vs = $tx['vs'];
        $cs = $tx['cs'];
        $ss = $tx['ss'];
        $msg = $tx['msg'];
        $created_at = $tx['created_at'];
        
        // Filter by start date if configured
        global $g_bank_sync_start_date;
        if (!empty($g_bank_sync_start_date)) {
            $tx_date = date('Y-m-d', strtotime($created_at));
            if ($tx_date < $g_bank_sync_start_date) {
                continue;
            }
        }
        
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
        
        $parsed_transactions[] = array(
            'transaction_id' => $tx_id,
            'amount' => $amount,
            'currency' => $currency,
            'vs' => $vs,
            'cs' => $cs,
            'ss' => $ss,
            'msg' => $msg,
            'status' => $status,
            'matched_user_id' => $matched_user_id,
            'created_at' => $created_at
        );
        
        // Insert into TBL_BANK_TRANSACTIONS
        $insert_sql = "INSERT INTO " . TBL_BANK_TRANSACTIONS . " 
                (transaction_id, amount, currency, variable_symbol, constant_symbol, specific_symbol, originator_message, status, finance_id, created_at) 
                VALUES 
                ('$tx_id', $amount, '$currency', '$vs', '$cs', '$ss', '$msg', '$status', NULL, '$created_at')";
        query_db($insert_sql);
    }
    
    cron_bank_sync_log("Sync fetched. Found new to process: $processed, Orphaned: $orphaned, Duplicates skipped: $duplicates.");
    
    return $parsed_transactions;
}
?>
