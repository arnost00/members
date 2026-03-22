<?php
require_once("connect.inc.php");
require_once("lib/OrisIntegrationService.php");
require_once("oris_user.class.php");

function logMessage($msg) {
    $date = date('Y-m-d H:i:s');
    $line = "[$date] $msg\n";
    
    // Output to console when run via CLI or manual sync trigger
    if (php_sapi_name() === 'cli' || defined('ORIS_MANUAL_SYNC')) {
        echo $line;
    }
    
    // Log to file in the root directory logs folder
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/oris_sync.log';
    file_put_contents($logFile, $line, FILE_APPEND);
}

function processEntry($row, $action, $service) {
    global $g_oris_club_key, $g_shortcut;
    
    $id = $row['id'];
    $userId = $row['id_user'];
    $raceId = $row['id_zavod'];
    
    logMessage("Processing $action for entry ID $id (user: $userId, race: $raceId)");
    
    // Get ORIS user ID
    $userQuery = "SELECT * FROM `" . TBL_USER . "` WHERE `id` = " . (int)$userId;
    $userRes = query_db($userQuery);
    $userRow = mysqli_fetch_assoc($userRes);
    
    $clubuser = null;
    $rgnum = null;
    if ($userRow && isset($userRow['reg'])) {
        // Reg number usually only contains the digits, so we prepend the club shortcut (e.g. ZBM)
        $rgnum = $userRow['reg'];
        // Ensure the club prefix is attached if it isn't already
        if (!empty($rgnum) && !preg_match('/^[A-Z]{3}/', $rgnum)) {
            $rgnum = $g_shortcut . str_pad($rgnum, 4, '0', STR_PAD_LEFT);
        }
        
        // Also check if we have clubuser directly saved in user table
        if (isset($userRow['oris_id']) && !empty($userRow['oris_id'])) {
            $clubuser = $userRow['oris_id'];
        }
        
        if (empty($clubuser)) {
            // Use the Oris API to get the internal user ID required for createEntry
            if (!empty($rgnum)) {
                try {
                    $userApiRes = $service->getUser($rgnum);
                    if (is_array($userApiRes) && isset($userApiRes['ID'])) {
                        $globalUserId = $userApiRes['ID'];
                        
                        // Now get the club user ID
                        try {
                            $clubUsersRes = $service->getClubUsers($globalUserId);
                            if (is_array($clubUsersRes)) {
                                $clubUsers = $clubUsersRes;
                                // Check if it's associative array representing a single user directly
                                if (isset($clubUsers['ID'])) {
                                    $clubUsers = [$clubUsers];
                                }
                                
                                // We must find the correct clubuser entry for the current club
                                // First, try to fetch current club key from config
                                global $g_external_is_club_id;
                                $clubId = $g_external_is_club_id ?? null;
                                
                                foreach ($clubUsers as $cu) {
                                    if (isset($cu['ID'])) {
                                        // If club ID is known, prefer that match
                                        if ($clubId && isset($cu['ClubID']) && $cu['ClubID'] == $clubId) {
                                            $clubuser = $cu['ID'];
                                            break;
                                        }
                                        // Otherwise, just take the first one or valid one
                                        if (empty($clubuser)) {
                                            $clubuser = $cu['ID'];
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $errMsg = $e->getMessage();
                            logMessage(" - Warning: getClubUsers failed for user ID $globalUserId: $errMsg");
                            if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
                                logMessage(" - Network error getting club users. Skipping to keep PENDING.");
                                return 'queued';
                            }
                        }
                        
                        if (empty($clubuser)) {
                            logMessage(" - Warning: Could not resolve ORIS Club User ID for user ID $globalUserId");
                        }
                    } else {
                        logMessage(" - Warning: Could not resolve ORIS User ID for rgnum $rgnum");
                    }
                } catch (Exception $e) {
                    $errMsg = $e->getMessage();
                    logMessage(" - Warning: getUser failed for rgnum $rgnum: $errMsg");
                    if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
                        logMessage(" - Network error getting user. Skipping to keep PENDING.");
                        return 'queued';
                    }
                }
            }
        }
    }
    
    // Get Race ORIS ID
    $raceQuery = "SELECT * FROM `" . TBL_RACE . "` WHERE `id` = " . (int)$raceId;
    $raceRes = query_db($raceQuery);
    $raceRow = mysqli_fetch_assoc($raceRes);
    
    // Ensure the race was imported from ORIS
    $comp = $raceRow['ext_id'] ?? null;
    if (empty($comp)) {
        logMessage(" - Skipped: Race $raceId is not imported from ORIS (no ext_id). Marking LOCAL_ONLY.");
        $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = 'LOCAL_ONLY' WHERE `id` = " . (int)$id;
        query_db($updateQuery);
        return;
    }

    // Ignore relays and sprint relays
    // S is typically for štafety (relays) in local DB
    if ($raceRow && isset($raceRow['typ0']) && $raceRow['typ0'] === 'S') {
        logMessage(" - Skipped: Race $raceId is a relay (typ0 = S). Marking LOCAL_ONLY.");
        $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = 'LOCAL_ONLY' WHERE `id` = " . (int)$id;
        query_db($updateQuery);
        return;
    }
    
    // Fetch Classes from ORIS Event to map the string category (e.g. H21) to the ORIS class ID
    $classId = null;
    $katName = $row['kat'];
    if (!empty($comp)) {
        try {
            $eventRes = $service->getEvent($comp);
            if (is_array($eventRes) && isset($eventRes['Classes'])) {
                $classes = is_array($eventRes['Classes']) ? $eventRes['Classes'] : [];
                
                foreach ($classes as $cls) {
                    if (!is_array($cls)) continue;
                    $clsName = $cls['Name'] ?? '';
                    // Some categories might have different spacing or case
                    if (trim($clsName) === trim($katName)) {
                        $classId = $cls['ID'] ?? null;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            logMessage(" - Warning: getEvent failed for event $comp: $errMsg");
            if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
                logMessage(" - Network error getting event. Skipping to keep PENDING.");
                return 'queued';
            }
        }
    }
    
    if (empty($classId)) {
        logMessage(" - Warning: Could not resolve ORIS Class ID for category '$katName'");
        $errorPayload = correct_sql_string(json_encode(['status' => 'error', 'message' => "Nelze spárovat kategorii '$katName' s ORISem."]));
        $failedStatus = 'FAILED_' . strtoupper($action);
        $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` 
            SET `sync_status` = '$failedStatus', 
                `sync_error_payload` = '$errorPayload' 
            WHERE `id` = " . (int)$id;
        query_db($updateQuery);
        return false;
    }
    
    $si = $row['si_chip'];
    if (empty($si) && !empty($userRow['si_chip'])) {
        $si = $userRow['si_chip'];
    }
    $rentSi = $row['rent_si'] ?? 0; // assuming rent_si is added or mapped
    $note = $row['pozn'] ?? '';
    
    $response = [];
    logMessage(" - Sending $action request to ORIS API. clubuser: $clubuser, class: $classId (mapped from $katName), si: $si");
    
    if (empty($clubuser)) {
        logMessage(" - Failed: Cannot perform action '$action' because ORIS Club User ID (clubuser) could not be resolved.");
        $errorPayload = correct_sql_string(json_encode(['status' => 'error', 'message' => 'Chybí ORIS ID uživatele v klubu (clubuser).']));
        $failedStatus = 'FAILED_' . strtoupper($action);
        $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` 
            SET `sync_status` = '$failedStatus', 
                `sync_error_payload` = '$errorPayload' 
            WHERE `id` = " . (int)$id;
        query_db($updateQuery);
        return false;
    }

    try {
        if ($action === 'create') {
            // Check if it already exists before creating to avoid "Již přihlášen"
            $existingEntryId = null;
            if (!empty($comp) && !empty($clubuser)) {
                try {
                    $entriesRes = $service->getEventEntries($comp);
                    if (is_array($entriesRes)) {
                        $entries = $entriesRes;
                        foreach ($entries as $entry) {
                            if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                                $existingEntryId = $entry['ID'];
                                logMessage(" - Found existing ORIS Entry ID $existingEntryId for clubuser $clubuser during create. Switching to update.");
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    logMessage(" - Warning: getEventEntries failed: " . $e->getMessage());
                }
            }

            if (!empty($existingEntryId)) {
                $updateDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note, $existingEntryId);
                $response = $service->updateEntry($updateDto);
                // Also update the action string so log messages below make sense
                $action = 'update';
            } else {
                $createDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note);
                $response = $service->createEntry($createDto);
            }
        } elseif ($action === 'update') {
            $entryIdToUpdate = $row['oris_entry_id'] ?? null;
            
            if (empty($entryIdToUpdate) && !empty($comp) && !empty($clubuser)) {
                logMessage(" - Warning: Missing ORIS Entry ID for update. Attempting to fetch from ORIS.");
                try {
                    $entriesRes = $service->getEventEntries($comp);
                    if (is_array($entriesRes)) {
                        $entries = $entriesRes;
                        foreach ($entries as $entry) {
                            if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                                $entryIdToUpdate = $entry['ID'];
                                logMessage(" - Found ORIS Entry ID $entryIdToUpdate for clubuser $clubuser");
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    logMessage(" - Warning: getEventEntries failed: " . $e->getMessage());
                }
            }

            if (empty($entryIdToUpdate)) {
                // It was never created on ORIS side successfully, so let's fall back to create instead of failing
                logMessage(" - Warning: Missing ORIS Entry ID for update. Falling back to create.");
                $createDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note);
                $response = $service->createEntry($createDto);
            } else {
                $updateDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note, $entryIdToUpdate);
                $response = $service->updateEntry($updateDto);
            }
        } elseif ($action === 'delete') {
            $entryIdToDelete = $row['oris_entry_id'] ?? null;
            
            // If we don't have the entry ID locally, we must fetch it from ORIS first
            if (empty($entryIdToDelete) && !empty($comp) && !empty($clubuser)) {
                logMessage(" - Warning: Missing ORIS Entry ID for delete. Attempting to fetch from ORIS.");
                try {
                    $entriesRes = $service->getEventEntries($comp);
                    if (is_array($entriesRes)) {
                        $entries = $entriesRes;
                        foreach ($entries as $entry) {
                            if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                                $entryIdToDelete = $entry['ID'];
                                logMessage(" - Found ORIS Entry ID $entryIdToDelete for clubuser $clubuser");
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    logMessage(" - Warning: getEventEntries failed: " . $e->getMessage());
                }
            }
            
            if (empty($entryIdToDelete)) {
                // Already doesn't exist remotely or could not be found
                logMessage(" - Warning: Could not find ORIS Entry ID to delete. Assuming already deleted.");
                $response = ['ID' => null];
            } else {
                $response = $service->deleteEntry($entryIdToDelete);
            }
        }
        
        // Log the exact raw payload sent to ORIS if available (custom debug info)
        if (isset($response['request'])) {
            logMessage(" - Raw POST Data: " . $response['request']);
        }

        // Action succeeded since no exception was thrown
        // 'create' usually returns ID in $response['ID']
        // 'update' might not return the ID, so we use the one we just found/used
        $entryId = $response['ID'] ?? null;
        if (empty($entryId)) {
            if ($action === 'update' && !empty($entryIdToUpdate)) {
                $entryId = $entryIdToUpdate;
            } elseif ($action === 'update' && !empty($existingEntryId)) { // from the create fallback block
                $entryId = $existingEntryId;
            } else {
                $entryId = $row['oris_entry_id'];
            }
        }
        
        logMessage(" - Success: Action $action completed. ORIS Entry ID: " . ($entryId ?: "N/A"));
        
        if ($action === 'delete') {
            // Remove the row locally after successful delete
            $updateQuery = "DELETE FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$id;
            query_db($updateQuery);
        } else {
            // Update the row
            $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` 
                SET `sync_status` = 'SYNCED', 
                    `oris_entry_id` = " . ($entryId ? (int)$entryId : "NULL") . ", 
                    `sync_timestamp` = NOW(), 
                    `sync_error_payload` = NULL 
                WHERE `id` = " . (int)$id;
            query_db($updateQuery);
        }
        return true;

    } catch (Exception $e) {
        $errMsg = $e->getMessage();
        logMessage(" - Failed: Action $action encountered an error: $errMsg");
        
        if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
            // It's a temporary network error or 5xx server error, keep it as PENDING and retry later
            logMessage(" - Will keep entry in PENDING status for retry later because it is a network error.");
            return 'queued';
        }
        
        $errorData = [
            'status' => 'error',
            'message' => $errMsg,
            'api_status' => ($e instanceof OrisApiException) ? $e->getApiStatus() : 'Exception'
        ];
        $errorPayload = correct_sql_string(json_encode($errorData));
        $failedStatus = 'FAILED_' . strtoupper($action);
        $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` 
            SET `sync_status` = '$failedStatus', 
                `sync_error_payload` = '$errorPayload' 
            WHERE `id` = " . (int)$id;
        query_db($updateQuery);
        return false;
    }
}

function getOrisSyncError($id) {
    $q = query_db("SELECT sync_error_payload FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$id);
    if ($q && $r = mysqli_fetch_assoc($q)) {
        if (!empty($r['sync_error_payload'])) {
            $err = json_decode($r['sync_error_payload'], true);
            return $err['message'] ?? 'Neznámá chyba';
        }
    }
    return 'Neznámá chyba';
}
?>
