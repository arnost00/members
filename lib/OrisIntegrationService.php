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
    
    public function __construct($clubKey = null) {
        $this->clubKey = $clubKey;
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
            throw new OrisNetworkException('cURL Error: ' . $error);
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
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

    public function getEventEntries($eventId) {
        return $this->makeRequest('getEventEntries', ['eventid' => $eventId]);
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
