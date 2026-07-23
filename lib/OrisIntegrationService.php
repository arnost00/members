<?php
/**
 * OrisIntegrationService
 * Unified service layer for interacting with the ORIS API.
 */

require_once __DIR__ . '/OrisExceptions.php';
require_once __DIR__ . '/OrisDTOs.php';

class OrisIntegrationService {

    public const DEFAULT_BASE_URL = 'https://oris.ceskyorientak.cz/';
    private const CLUB_KEY_REQUIRED_METHODS = [
        'createEntry' => true,
        'updateEntry' => true,
        'deleteEntry' => true,
        'createServiceEntry' => true,
        'updateServiceEntry' => true,
        'deleteServiceEntry' => true,
        'getClubEntryRights' => true,
        'setClubEntryRights' => true,
        'getClubUserList' => true,
        'createPerson' => true,
        'editPerson' => true,
        'createClubUser' => true,
        'editClubUser' => true,
        'createUserLogin' => true,
    ];

    private $apiUrl;
    private $clubKey;

    public function __construct($clubKey = null, $apiUrl = null) {
        $this->clubKey = empty($clubKey) ? null : $clubKey;
        $this->apiUrl = self::normalizeApiUrl($apiUrl ?? (self::DEFAULT_BASE_URL . 'API/'));
    }

    public function hasClubKey() {
        return !empty($this->clubKey);
    }

    public function getApiUrl() {
        return $this->apiUrl;
    }

    public static function normalizeApiUrl($apiUrl) {
        $apiUrl = trim((string)$apiUrl);
        if ($apiUrl === '') {
            return self::DEFAULT_BASE_URL . 'API/';
        }

        $apiUrl = rtrim($apiUrl, '/');
        if (preg_match('#/API$#i', $apiUrl)) {
            return $apiUrl . '/';
        }

        return $apiUrl . '/API/';
    }

    private function log(string $msg): void {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
        file_put_contents(__DIR__ . '/../logs/oris_sync.log', $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Internal generic HTTP request method.
     */
    private function makeRequest($method, $params = [], $isPost = false) {
        $requiresClubKey = isset(self::CLUB_KEY_REQUIRED_METHODS[$method]);
        if ($requiresClubKey && !$this->hasClubKey()) {
            throw new OrisValidationException("ORIS method '{$method}' requires configured clubkey.");
        }

        $params['method'] = $method;
        $params['format'] = 'json';
        if ($requiresClubKey) {
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

    public function createPerson(array $params) {
        return $this->makeRequest('createPerson', $params, true);
    }

    public function editPerson(array $params) {
        return $this->makeRequest('editPerson', $params, true);
    }

    public function createClubUser(array $params) {
        return $this->makeRequest('createClubUser', $params, true);
    }

    public function editClubUser(array $params) {
        return $this->makeRequest('editClubUser', $params, true);
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
        if (!empty($clubId)) {
            $params['clubid'] = $clubId;
        }
        return $this->makeRequest('getEventEntries', $params);
    }

    public function getEventServiceEntries($eventId, $clubId = null) {
        $params = ['eventid' => $eventId];
        if (!empty($clubId)) {
            $params['clubid'] = $clubId;
        }
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

class OrisIntegrationServiceFactory {

    public static function create() {
        return new OrisIntegrationService(
            self::getConfiguredClubKey(),
            self::getApiUrl(self::getConfiguredBaseUrl())
        );
    }

    public static function getConfiguredClubKey() {
        global $g_oris_club_key;

        return empty($g_oris_club_key) ? null : $g_oris_club_key;
    }

    public static function getConfiguredBaseUrl() {
        global $g_oris_base_url, $g_oris_api_url;

        if (!empty($g_oris_base_url)) {
            return self::normalizeBaseUrl($g_oris_base_url);
        }

        if (!empty($g_oris_api_url)) {
            return self::normalizeBaseUrl(preg_replace('#/API/?$#i', '', $g_oris_api_url));
        }

        return OrisIntegrationService::DEFAULT_BASE_URL;
    }

    public static function getConfiguredApiUrl() {
        return self::getApiUrl(self::getConfiguredBaseUrl());
    }

    private static function getApiUrl($baseUrl = null) {
        if ($baseUrl === null || trim((string)$baseUrl) === '') {
            return OrisIntegrationService::DEFAULT_BASE_URL . 'API/';
        }

        return OrisIntegrationService::normalizeApiUrl($baseUrl);
    }

    private static function normalizeBaseUrl($baseUrl) {
        $baseUrl = trim((string)$baseUrl);
        if ($baseUrl === '') {
            return OrisIntegrationService::DEFAULT_BASE_URL;
        }

        $baseUrl = preg_replace('#/API/?$#i', '', $baseUrl);

        return rtrim($baseUrl, '/') . '/';
    }
}
