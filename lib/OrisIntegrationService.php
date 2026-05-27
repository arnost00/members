<?php
/**
 * OrisIntegrationService
 * Unified service layer for interacting with the ORIS API.
 */

require_once __DIR__ . '/OrisExceptions.php';
require_once __DIR__ . '/OrisDTOs.php';

class OrisIntegrationService {

    private $apiUrl = 'https://oris.ceskyorientak.cz/API/';
    private $clubKey;

    public function __construct($clubKey = null, $apiUrl = null) {
        $this->clubKey = $clubKey;
        if ($apiUrl !== null) {
            $this->apiUrl = $apiUrl;
        }
    }

    public static function create($apiUrl = null): self {
        global $g_oris_club_key;
        return new self($g_oris_club_key ?? null, $apiUrl);
    }
    
    private function log(string $msg): void {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents(__DIR__ . '/../logs/oris_sync.log', $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Internal generic HTTP request method.
     */
    private function makeRequest($method, $params = [], $isPost = false) {
        $params['method'] = $method;
        $params['format'] = 'json';
        if ($this->clubKey) {
            $params['clubkey'] = $this->clubKey;
        }

        // Log outgoing request — skip API meta-params, show key=value pairs
        $logParams = $params;
        unset($logParams['method'], $logParams['format'], $logParams['clubkey']);
        $paramStr = '';
        foreach ($logParams as $k => $v) {
            $paramStr .= "  $k=$v";
        }
        $this->log(($isPost ? 'POST' : 'GET') . ' ' . $method . $paramStr);

        $ch = curl_init();

        if ($isPost) {
            $postData = http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        } else {
            $url = $this->apiUrl . '?' . http_build_query($params);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);

        if(curl_errno($ch)){
            $error = curl_error($ch);
            curl_close($ch);
            $this->log('  << cURL error: ' . $error);
            throw new OrisNetworkException('cURL Error: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        // Log response in a readable way — show Status + compact Data summary
        $logStatus = ($decoded !== null) ? ($decoded['Status'] ?? '?') : '(invalid JSON)';
        $logData   = ($decoded !== null) ? ($decoded['Data']   ?? null) : null;
        $dataStr   = '';
        if (is_string($logData) && $logData !== '') {
            $dataStr = '  data="' . $logData . '"';
        } elseif (is_array($logData)) {
            if (isset($logData[0])) {
                $dataStr = '  data=[' . count($logData) . ' items]';
            } else {
                $pairs = [];
                $shown = 0;
                foreach ($logData as $k => $v) {
                    if ($shown++ >= 6) { $pairs[] = '…'; break; }
                    $pairs[] = $k . '=' . (is_scalar($v) ? $v : '[…]');
                }
                $dataStr = '  data={' . implode(', ', $pairs) . '}';
            }
        }
        $this->log('  << HTTP ' . $httpCode . '  status=' . $logStatus . $dataStr);

        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['Status']) && $decoded['Status'] === 'OK') {
            return $decoded['Data'] ?? $decoded;
        } else {
            $apiStatus = $decoded['Status'] ?? 'Unknown';
            $apiData = $decoded['Data'] ?? null;
            $msg = "API Error or HTTP {$httpCode}. Status: {$apiStatus}";
            if ($isPost) {
                $msg .= "\nPOST Data sent: " . print_r($params, true);
            }
            if (is_string($apiData)) {
                $msg .= " - " . $apiData;
            }
            throw new OrisApiException($msg, $apiStatus, $apiData);
        }
    }

    // --- Write/Mutating Operations (Phase C) ---

    public function createEntry(OrisEntryRequestDTO $dto) {
        return $this->makeRequest('createEntry', $dto->toArray(), true);
    }
    
    public function updateEntry(OrisEntryRequestDTO $dto) {
        return $this->makeRequest('updateEntry', $dto->toArray(), true);
    }
    
    public function deleteEntry($entryId) {
        return $this->makeRequest('deleteEntry', ['entryid' => $entryId], true);
    }

    // --- Read-Only and Protected Read Endpoints (Phase A & B) ---

    public function getUser($rgnum) {
        return $this->makeRequest('getUser', ['rgnum' => $rgnum]);
    }

    public function getClubUsers($userId) {
        return $this->makeRequest('getClubUsers', ['user' => $userId]);
    }

    public function getEventEntries($eventId, $clubId = null) {
        $params = ['eventid' => $eventId];
        if ($clubId !== null) { $params['clubid'] = $clubId; }
        return $this->makeRequest('getEventEntries', $params);
    }

    public function getEventServiceEntries($eventId, $clubId = null) {
        $params = ['eventid' => $eventId];
        if ($clubId !== null) { $params['clubid'] = $clubId; }
        return $this->makeRequest('getEventServiceEntries', $params);
    }

    public function getEvent($eventId) {
        return $this->makeRequest('getEvent', ['id' => $eventId]);
    }

    public function getEventList($fromDate, $toDate, $all = 1) {
        return $this->makeRequest('getEventList', [
            'all' => $all,
            'datefrom' => $fromDate,
            'dateto' => $toDate
        ]);
    }

    public function getRegistration($sport, $year) {
        return $this->makeRequest('getRegistration', [
            'sport' => $sport,
            'year' => $year
        ]);
    }
}
