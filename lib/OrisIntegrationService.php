<?php
/**
 * OrisIntegrationService
 * Handles the automated background forwarding of entries to ORIS API.
 */
class OrisIntegrationService {
    
    private $apiUrl = 'https://oris.ceskyorientak.cz/API/';
    private $clubKey;
    
    public function __construct($clubKey) {
        $this->clubKey = $clubKey;
    }
    
    /**
     * Executes the HTTP POST request to ORIS API
     */
    private function executeRequest($method, $params) {
        $params['method'] = $method;
        $params['format'] = 'json';
        $params['clubkey'] = $this->clubKey;
        
        $postData = http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Ensure SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        
        if(curl_errno($ch)){
            $error = curl_error($ch);
            curl_close($ch);
            return ['status' => 'error', 'message' => 'cURL Error: ' . $error, 'request' => $postData];
        }
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['Status']) && $decoded['Status'] === 'OK') {
            return ['status' => 'success', 'data' => $decoded['Data'] ?? $decoded, 'request' => $postData];
        } else {
            $apiErrorMsg = '';
            if (is_array($decoded) && isset($decoded['Status'])) {
                $apiErrorMsg = " ORIS Status: " . $decoded['Status'];
                // Some endpoints might return error details in Data or a specific Error field
                if (isset($decoded['Data']) && is_string($decoded['Data'])) {
                    $apiErrorMsg .= " - " . $decoded['Data'];
                }
            }
            return [
                'status' => 'error', 
                'message' => 'API Error or HTTP ' . $httpCode . $apiErrorMsg,
                'payload' => $response,
                'request' => $postData
            ];
        }
    }

    public function createEntry($clubuser, $classId, $si, $rentSi, $note = '') {
        $params = [
            'clubuser' => $clubuser,
            'class' => $classId
        ];
        
        if ($si) {
            $params['si'] = $si;
        }
        if ($rentSi) {
            $params['rent_si'] = 1;
        }
        if ($note) {
            $params['note'] = $note;
        }

        return $this->executeRequest('createEntry', $params);
    }
    
    public function updateEntry($entryId, $clubuser, $classId, $si, $rentSi, $note = '') {
        $params = [
            'entryid' => $entryId,
            'clubuser' => $clubuser,
            'class' => $classId
        ];
        
        if ($si) {
            $params['si'] = $si;
        }
        if ($rentSi) {
            $params['rent_si'] = 1;
        }
        if ($note) {
            $params['note'] = $note;
        }

        return $this->executeRequest('updateEntry', $params);
    }
    
    public function deleteEntry($entryId) {
        $params = [
            'entryid' => $entryId
        ];
        return $this->executeRequest('deleteEntry', $params);
    }

    public function getUser($rgnum) {
        $params = [
            'method' => 'getUser',
            'format' => 'json',
            'rgnum' => $rgnum
        ];
        
        $ch = curl_init();
        $url = $this->apiUrl . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function getClubUsers($userId) {
        $params = [
            'method' => 'getClubUsers',
            'format' => 'json',
            'user' => $userId
        ];
        
        $ch = curl_init();
        $url = $this->apiUrl . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function getEventEntries($eventId) {
        $params = [
            'method' => 'getEventEntries',
            'format' => 'json',
            'eventid' => $eventId
        ];
        
        $ch = curl_init();
        $url = $this->apiUrl . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }

    public function getEvent($eventId) {
        $params = [
            'method' => 'getEvent',
            'format' => 'json',
            'id' => $eventId
        ];
        
        $ch = curl_init();
        $url = $this->apiUrl . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>