<?php
/**
 * Background synchronization daemon for ORIS API.
 * This file is expected to be run by a cron job or scheduled task.
 */
require_once("connect.inc.php");
require_once("sess.inc.php");
require_once("common.inc.php");
require_once("lib/OrisIntegrationService.php");
require_once("oris_user.class.php");

// Establish database connection if not already connected
if (!isset($GLOBALS['db_conn']) || !$GLOBALS['db_conn']) {
    db_Connect();
}

// Load club key from config or environment
// Ensure $g_oris_club_key is defined in your cfg files
global $g_oris_club_key;

if (empty($g_oris_club_key)) {
    die("Error: ORIS club key neni nastaven.");
}

$service = new OrisIntegrationService($g_oris_club_key);

$query = "
    SELECT zx.*, r.oris_entry_start 
    FROM `" . TBL_ZAVXUS . "` zx
    LEFT JOIN `" . TBL_RACE . "` r ON zx.id_zavod = r.id
    WHERE zx.sync_status IN ('PENDING_CREATE', 'PENDING_UPDATE', 'PENDING_DELETE', 'FAILED_RETRY')
      AND (r.datum >= DATE_SUB(CURDATE(), INTERVAL 1 DAY) OR r.datum IS NULL)
    ORDER BY ISNULL(r.oris_entry_start), r.oris_entry_start ASC
";
$res = query_db($query);

while ($row = mysqli_fetch_assoc($res)) {
    $entryStartStr = $row['oris_entry_start'];
    if (!empty($entryStartStr)) {
        $entryStartTime = strtotime($entryStartStr);
        $now = time();
        if ($entryStartTime > $now) {
            $diff = $entryStartTime - $now;
            if ($diff <= 65) { // 65 seconds buffer for cron running every minute
                logMessage("Prihlasky se oteviraji za $diff seconds (at $entryStartStr). Jdu sapt pred pokracovanim...");
                sleep($diff);
            } else {
                // Race is not open yet and more than a minute away, skip for now
                logMessage("Preskakuju prihlasku ID {$row['id']} - Zavod otevira prihlasky v $entryStartStr (za $diff s).");
                continue;
            }
        }
    }

    $action = '';
    if ($row['sync_status'] === 'PENDING_CREATE') {
        $action = 'create';
    } elseif ($row['sync_status'] === 'PENDING_UPDATE') {
        $action = 'update';
    } elseif ($row['sync_status'] === 'PENDING_DELETE') {
        $action = 'delete';
    } elseif ($row['sync_status'] === 'FAILED_RETRY') {
        // We will just let it be an 'update' if there's an ID, or 'create_or_update' 
        // if there's no ID so processEntry can check ORIS first.
        $action = empty($row['oris_entry_id']) ? 'create' : 'update';
        // Wait, instead of setting it to 'create' which assumes it doesn't exist,
        // if we set it to 'update', the new logic in processEntry will fetch from ORIS.
        // If it's not found on ORIS, it falls back to 'create' anyway!
        $action = 'update';
    }

    if (!empty($action)) {
        processEntry($row, $action, $service);
    }
}

function logMessage($msg) {
    $date = date('Y-m-d H:i:s');
    $line = "[$date] $msg\n";
    // Output to console when run via CLI
    echo $line;
    // Log to file in the same directory
    file_put_contents(__DIR__ . '/logs/oris_sync.log', $line, FILE_APPEND);
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
        
        // Use the Oris API to get the internal user ID required for createEntry
        if (!empty($rgnum)) {
            $userApiRes = $service->getUser($rgnum);
            if (isset($userApiRes['Status']) && $userApiRes['Status'] === 'OK' && isset($userApiRes['Data']['ID'])) {
                $globalUserId = $userApiRes['Data']['ID'];
                
                // Now get the club user ID
                $clubUsersRes = $service->getClubUsers($globalUserId);
                if (isset($clubUsersRes['Status']) && $clubUsersRes['Status'] === 'OK' && isset($clubUsersRes['Data'])) {
                    // It might be a single object or an array of objects
                    $clubUsers = is_array($clubUsersRes['Data']) ? $clubUsersRes['Data'] : [];
                    if (isset($clubUsers['ID'])) {
                        $clubUsers = [$clubUsers];
                    }
                    
                    foreach ($clubUsers as $cu) {
                        if (isset($cu['ID'])) {
                            $clubuser = $cu['ID'];
                            break;
                        }
                    }
                }
                
                if (empty($clubuser)) {
                    logMessage(" - Warning: Could not resolve ORIS Club User ID for user ID $globalUserId");
                }
            } else {
                logMessage(" - Warning: Could not resolve ORIS User ID for rgnum $rgnum");
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
        $eventRes = $service->getEvent($comp);
        if (isset($eventRes['Status']) && $eventRes['Status'] === 'OK' && isset($eventRes['Data']['Classes'])) {
            $classes = is_array($eventRes['Data']['Classes']) ? $eventRes['Data']['Classes'] : [];
            
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
    }
    
    if (empty($classId)) {
        logMessage(" - Warning: Could not resolve ORIS Class ID for category '$katName'");
        // Fallback to sending the string, which will likely be rejected by ORIS but preserves behavior
        $classId = $katName;
    }
    
    $si = $row['si_chip'];
    $rentSi = $row['rent_si'] ?? 0; // assuming rent_si is added or mapped
    $note = $row['pozn'] ?? '';
    
    $response = [];
    logMessage(" - Sending $action request to ORIS API. clubuser: $clubuser, class: $classId (mapped from $katName), si: $si");
    
    if ($action === 'create') {
        // Check if it already exists before creating to avoid "Již přihlášen"
        $existingEntryId = null;
        if (!empty($comp) && !empty($clubuser)) {
            $entriesRes = $service->getEventEntries($comp);
            if (isset($entriesRes['Status']) && $entriesRes['Status'] === 'OK' && isset($entriesRes['Data'])) {
                $entries = is_array($entriesRes['Data']) ? $entriesRes['Data'] : [];
                foreach ($entries as $entry) {
                    if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                        $existingEntryId = $entry['ID'];
                        logMessage(" - Found existing ORIS Entry ID $existingEntryId for clubuser $clubuser during create. Switching to update.");
                        break;
                    }
                }
            }
        }

        if (!empty($existingEntryId)) {
            $response = $service->updateEntry($existingEntryId, $clubuser, $classId, $si, $rentSi, $note);
            // Also update the action string so log messages below make sense
            $action = 'update';
        } else {
            $response = $service->createEntry($clubuser, $classId, $si, $rentSi, $note);
        }
    } elseif ($action === 'update') {
        $entryIdToUpdate = $row['oris_entry_id'] ?? null;
        
        if (empty($entryIdToUpdate) && !empty($comp) && !empty($clubuser)) {
            logMessage(" - Warning: Missing ORIS Entry ID for update. Attempting to fetch from ORIS.");
            $entriesRes = $service->getEventEntries($comp);
            if (isset($entriesRes['Status']) && $entriesRes['Status'] === 'OK' && isset($entriesRes['Data'])) {
                $entries = is_array($entriesRes['Data']) ? $entriesRes['Data'] : [];
                foreach ($entries as $entry) {
                    if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                        $entryIdToUpdate = $entry['ID'];
                        logMessage(" - Found ORIS Entry ID $entryIdToUpdate for clubuser $clubuser");
                        break;
                    }
                }
            }
        }

        if (empty($entryIdToUpdate)) {
            // It was never created on ORIS side successfully, so let's fall back to create instead of failing
            logMessage(" - Warning: Missing ORIS Entry ID for update. Falling back to create.");
            $response = $service->createEntry($clubuser, $classId, $si, $rentSi, $note);
        } else {
            $response = $service->updateEntry($entryIdToUpdate, $clubuser, $classId, $si, $rentSi, $note);
        }
    } elseif ($action === 'delete') {
        $entryIdToDelete = $row['oris_entry_id'] ?? null;
        
        // If we don't have the entry ID locally, we must fetch it from ORIS first
        if (empty($entryIdToDelete) && !empty($comp) && !empty($clubuser)) {
            logMessage(" - Warning: Missing ORIS Entry ID for delete. Attempting to fetch from ORIS.");
            $entriesRes = $service->getEventEntries($comp);
            if (isset($entriesRes['Status']) && $entriesRes['Status'] === 'OK' && isset($entriesRes['Data'])) {
                $entries = is_array($entriesRes['Data']) ? $entriesRes['Data'] : [];
                foreach ($entries as $entry) {
                    if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                        $entryIdToDelete = $entry['ID'];
                        logMessage(" - Found ORIS Entry ID $entryIdToDelete for clubuser $clubuser");
                        break;
                    }
                }
            }
        }
        
        if (empty($entryIdToDelete)) {
            // Already doesn't exist remotely or could not be found
            logMessage(" - Warning: Could not find ORIS Entry ID to delete. Assuming already deleted.");
            $response = ['status' => 'success', 'data' => []];
        } else {
            $response = $service->deleteEntry($entryIdToDelete);
        }
    }
    
    // Log the exact raw payload sent to ORIS
    if (isset($response['request'])) {
        logMessage(" - Raw POST Data: " . $response['request']);
    }

    if ($response['status'] === 'success') {
        // 'create' usually returns ID in $response['data']['ID']
        // 'update' might not return the ID, so we use the one we just found/used
        $entryId = $response['data']['ID'] ?? null;
        if (empty($entryId)) {
            if ($action === 'update' && !empty($entryIdToUpdate)) {
                $entryId = $entryIdToUpdate;
            } elseif ($action === 'update' && !empty($existingEntryId)) { // from the create fallback block
                $entryId = $existingEntryId;
            } else {
                $entryId = $row['oris_entry_id'];
            }
        }
        
        logMessage(" - Success: Action $action completed. ORIS Entry ID: $entryId");
        
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
    } else {
        $errMsg = $response['message'] ?? 'Unknown error';
        logMessage(" - Failed: Action $action encountered an error: $errMsg");
        
        $errorPayload = correct_sql_string(json_encode($response));
        $updateQuery = "UPDATE `" . TBL_ZAVXUS . "` 
            SET `sync_status` = 'FAILED_RETRY', 
                `sync_error_payload` = '$errorPayload' 
            WHERE `id` = " . (int)$id;
        query_db($updateQuery);
    }
}
?>