<?php
require_once dirname(__FILE__) . '/AbstractBankConnector.php';

class RaiffeisenbankConnector extends AbstractBankConnector {

    protected function getBaseApiUrl() {
        return 'https://api.rb.cz/rbcz/premium/api';
    }

    private function log($msg) {
        $log_file = dirname(__FILE__) . '/../logs/bank_sync_log.txt';
        $timestamp = date('d.m.Y H:i:s');
        $line = "[$timestamp] $msg\n";
        file_put_contents($log_file, $line, FILE_APPEND);
    }

    private function generate_uuid_v4() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function getTransactions($days_back) {
        global $db_conn;

        $this->log("Starting Bank sync run via RaiffeisenbankConnector.");

        $mtls_enabled = $this->isMtlsEnabled();
        $base_api_url = rtrim($this->getBaseApiUrl(), '/');
        $g_bank_account_number = $this->getBankAccountNumber();
        $g_bank_client_id = $this->getBankClientId();
        $g_bank_cert_path = $this->getCertificatePath();
        $g_bank_cert_pass = $this->getCertificatePassword();

        if (empty($g_bank_account_number)) {
            $this->log("Error: Bank configuration is missing account number.");
            return [];
        }

        if (empty($g_bank_client_id)) {
            $this->log("Error: Bank configuration is missing client ID.");
            return [];
        }

        if ($mtls_enabled && empty($g_bank_cert_path)) {
            $this->log("Error: Bank configuration is missing certificate path while mTLS is enabled.");
            return [];
        }

        $days_back = (int)$days_back;
        if ($days_back < 1) $days_back = 1;
        if ($days_back > 90) $days_back = 90;

        $api_date_from = date('Y-m-d', strtotime("-{$days_back} day"));
        $api_date_to = date('Y-m-d');

        $base_url = $base_api_url . "/accounts/{$g_bank_account_number}/CZK/transactions?from=" . urlencode($api_date_from) . "&to=" . urlencode($api_date_to);

        $cert_path = $g_bank_cert_path;
        if (!file_exists($cert_path) && file_exists(dirname(__FILE__) . '/../' . ltrim($cert_path, '/'))) {
            $cert_path = dirname(__FILE__) . '/../' . ltrim($cert_path, '/');
        }

        $all_transactions = [];
        $page = 0;
        $last_page = false;

        while (!$last_page) {
            $url = $base_url . "&page=" . $page;
            $this->log("Fetching transactions from Raiffeisenbank API: $url");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($mtls_enabled) {
                curl_setopt($ch, CURLOPT_SSLCERT, $cert_path);
                curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $g_bank_cert_pass);
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, "P12");
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $request_id = $this->generate_uuid_v4();

            $headers = array(
                "X-Request-Id: " . $request_id,
                "X-IBM-Client-Id: " . $g_bank_client_id,
                "Accept: application/json"
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            $curl_errno = curl_errno($ch);
            curl_close($ch);

            if ($response === false || $http_code !== 200) {
                if ($curl_errno === CURLE_OPERATION_TIMEDOUT || $curl_errno === CURLE_COULDNT_CONNECT || $http_code >= 500) {
                    $this->log("Error: Bank API is currently unavailable (Timeout or 5xx error). HTTP Code: $http_code. cURL Error: $curl_error.");
                } else {
                    $this->log("Error fetching API. HTTP Code: $http_code. cURL Error: $curl_error. Response: " . (is_string($response) ? $response : ''));
                }
                break;
            }

            $data = json_decode($response, true);
            if (!isset($data['transactions']) || !is_array($data['transactions'])) {
                $this->log("Error: JSON does not contain 'transactions' array.");
                break;
            }

            $all_transactions = array_merge($all_transactions, $data['transactions']);
            
            if (isset($data['lastPage']) && $data['lastPage'] === true) {
                $last_page = true;
            } else if (count($data['transactions']) === 0) {
                $last_page = true; // safety fallback
            } else {
                $page++;
            }
        }

        $transactions = $all_transactions;
        $tx_count = count($transactions);
        $this->log("Fetched total $tx_count transactions from API across " . ($page + 1) . " pages.");

        $parsed_transactions = [];

        foreach ($transactions as $tx) {
            $tx_id = isset($tx['entryReference']) ? mysqli_real_escape_string($db_conn, $tx['entryReference']) : '';
            $amount = isset($tx['amount']['value']) ? (float)$tx['amount']['value'] : 0.0;
            $currency = isset($tx['amount']['currency']) ? mysqli_real_escape_string($db_conn, $tx['amount']['currency']) : 'CZK';

            $created_at = isset($tx['bookingDate']) ? date('Y-m-d H:i:s', strtotime($tx['bookingDate'])) : (isset($tx['valueDate']) ? date('Y-m-d H:i:s', strtotime($tx['valueDate'])) : date('Y-m-d H:i:s'));

            $remittance = isset($tx['entryDetails']['transactionDetails']['remittanceInformation']) ? $tx['entryDetails']['transactionDetails']['remittanceInformation'] : array();

            $vs = isset($remittance['creditorReferenceInformation']['variable']) ? mysqli_real_escape_string($db_conn, $remittance['creditorReferenceInformation']['variable']) : '';
            $cs = isset($remittance['creditorReferenceInformation']['constant']) ? mysqli_real_escape_string($db_conn, $remittance['creditorReferenceInformation']['constant']) : '';
            $ss = isset($remittance['creditorReferenceInformation']['specific']) ? mysqli_real_escape_string($db_conn, $remittance['creditorReferenceInformation']['specific']) : '';
            $msg = isset($remittance['originatorMessage']) ? mysqli_real_escape_string($db_conn, $remittance['originatorMessage']) : '';

            if (empty($tx_id) || $amount <= 0) continue;

            $parsed_transactions[] = [
                'transaction_id' => $tx_id,
                'amount' => $amount,
                'currency' => $currency,
                'vs' => $vs,
                'cs' => $cs,
                'ss' => $ss,
                'msg' => $msg,
                'created_at' => $created_at
            ];
        }

        return $parsed_transactions;
    }
}
