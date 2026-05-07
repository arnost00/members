<?php
require_once("connect.inc.php");
require_once("lib/OrisIntegrationService.php");
require_once("oris_user.class.php");

function processEntry($row, $action, $service) {
    global $g_oris_club_key, $g_shortcut;

    $id = $row['id'];
    $userId = $row['id_user'];
    $raceId = $row['id_zavod'];

    // Get ORIS user ID
    $userQuery = "SELECT * FROM `" . TBL_USER . "` WHERE `id` = " . (int)$userId;
    $userRes = query_db($userQuery);
    $userRow = mysqli_fetch_assoc($userRes);

    $clubuser = null;
    $rgnum = null;
    if ($userRow && isset($userRow['reg'])) {
        $rgnum = $userRow['reg'];
        if (!empty($rgnum) && !preg_match('/^[A-Z]{3}/', $rgnum)) {
            $rgnum = $g_shortcut . str_pad($rgnum, 4, '0', STR_PAD_LEFT);
        }

        if (isset($userRow['oris_id']) && !empty($userRow['oris_id'])) {
            $clubuser = $userRow['oris_id'];
        }

        if (empty($clubuser)) {
            if (!empty($rgnum)) {
                try {
                    $userApiRes = $service->getUser($rgnum);
                    if (is_array($userApiRes) && isset($userApiRes['ID'])) {
                        $globalUserId = $userApiRes['ID'];

                        try {
                            $clubUsersRes = $service->getClubUsers($globalUserId);
                            if (is_array($clubUsersRes)) {
                                $clubUsers = $clubUsersRes;
                                if (isset($clubUsers['ID'])) {
                                    $clubUsers = [$clubUsers];
                                }

                                global $g_external_is_club_id;
                                $clubId = $g_external_is_club_id ?? null;

                                foreach ($clubUsers as $cu) {
                                    if (isset($cu['ID'])) {
                                        if ($clubId && isset($cu['ClubID']) && $cu['ClubID'] == $clubId) {
                                            $clubuser = $cu['ID'];
                                            break;
                                        }
                                        if (empty($clubuser)) {
                                            $clubuser = $cu['ID'];
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $errMsg = $e->getMessage();
                            if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
                                return 'queued';
                            }
                        }
                    }
                } catch (Exception $e) {
                    $errMsg = $e->getMessage();
                    if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
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

    $comp = $raceRow['ext_id'] ?? null;
    if (empty($comp)) {
        query_db("UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = 'LOCAL_ONLY' WHERE `id` = " . (int)$id);
        return;
    }

    // Ignore relays
    if ($raceRow && isset($raceRow['typ0']) && $raceRow['typ0'] === 'S') {
        query_db("UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = 'LOCAL_ONLY' WHERE `id` = " . (int)$id);
        return;
    }

    // Check if ORIS entries are open yet
    $orisEntryStart = $raceRow['oris_entry_start'] ?? null;
    if (!empty($orisEntryStart)) {
        $entryStartTs = strtotime($orisEntryStart);
        if ($entryStartTs !== false && $entryStartTs > time()) {
            return 'not_open';
        }
    }

    // Map category name to ORIS class ID
    $classId = null;
    $katName = $row['kat'];
    if (!empty($comp)) {
        try {
            $eventRes = $service->getEvent($comp);
            if (is_array($eventRes) && isset($eventRes['Classes'])) {
                $classes = is_array($eventRes['Classes']) ? $eventRes['Classes'] : [];
                foreach ($classes as $cls) {
                    if (!is_array($cls)) continue;
                    if (trim($cls['Name'] ?? '') === trim($katName)) {
                        $classId = $cls['ID'] ?? null;
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
                return 'queued';
            }
        }
    }

    if (empty($classId)) {
        $errorPayload = correct_sql_string(json_encode(['status' => 'error', 'message' => "Nelze spárovat kategorii '$katName' s ORISem."]));
        $failedStatus = 'FAILED_' . strtoupper($action);
        query_db("UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = '$failedStatus', `sync_error_payload` = '$errorPayload' WHERE `id` = " . (int)$id);
        return false;
    }

    $si = $row['si_chip'];
    if (empty($si) && !empty($userRow['si_chip'])) {
        $si = $userRow['si_chip'];
    }
    $rentSi = $row['rent_si'] ?? 0;
    $note = $row['pozn'] ?? '';

    if (empty($clubuser)) {
        $errorPayload = correct_sql_string(json_encode(['status' => 'error', 'message' => 'Chybí ORIS ID uživatele v klubu (clubuser).']));
        $failedStatus = 'FAILED_' . strtoupper($action);
        query_db("UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = '$failedStatus', `sync_error_payload` = '$errorPayload' WHERE `id` = " . (int)$id);
        return false;
    }

    try {
        $response = [];

        if ($action === 'create') {
            $existingEntryId = null;
            if (!empty($comp) && !empty($clubuser)) {
                try {
                    $entriesRes = $service->getEventEntries($comp);
                    if (is_array($entriesRes)) {
                        foreach ($entriesRes as $entry) {
                            if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                                $existingEntryId = $entry['ID'];
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // proceed with create
                }
            }

            if (!empty($existingEntryId)) {
                $updateDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note, $existingEntryId);
                $response = $service->updateEntry($updateDto);
                $action = 'update';
            } else {
                $createDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note);
                $response = $service->createEntry($createDto);
            }
        } elseif ($action === 'update') {
            $entryIdToUpdate = $row['oris_entry_id'] ?? null;

            if (empty($entryIdToUpdate) && !empty($comp) && !empty($clubuser)) {
                try {
                    $entriesRes = $service->getEventEntries($comp);
                    if (is_array($entriesRes)) {
                        foreach ($entriesRes as $entry) {
                            if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                                $entryIdToUpdate = $entry['ID'];
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // ignore
                }
            }

            if (empty($entryIdToUpdate)) {
                // Fall back to create if no existing entry found
                $createDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note);
                $response = $service->createEntry($createDto);
            } else {
                $updateDto = new OrisEntryRequestDTO($clubuser, $classId, $si, (bool)$rentSi, $note, $entryIdToUpdate);
                $response = $service->updateEntry($updateDto);
            }
        } elseif ($action === 'delete') {
            $entryIdToDelete = $row['oris_entry_id'] ?? null;

            if (empty($entryIdToDelete) && !empty($comp) && !empty($clubuser)) {
                try {
                    $entriesRes = $service->getEventEntries($comp);
                    if (is_array($entriesRes)) {
                        foreach ($entriesRes as $entry) {
                            if (isset($entry['ClubUserID']) && $entry['ClubUserID'] == $clubuser) {
                                $entryIdToDelete = $entry['ID'];
                                break;
                            }
                        }
                    }
                } catch (Exception $e) {
                    // ignore
                }
            }

            if (empty($entryIdToDelete)) {
                $response = ['ID' => null];
            } else {
                $response = $service->deleteEntry($entryIdToDelete);
            }
        }

        $entryId = $response['Entry']['ID'] ?? $response['ID'] ?? null;
        if (empty($entryId)) {
            if ($action === 'update' && !empty($entryIdToUpdate)) {
                $entryId = $entryIdToUpdate;
            } elseif ($action === 'update' && !empty($existingEntryId)) {
                $entryId = $existingEntryId;
            } else {
                $entryId = $row['oris_entry_id'];
            }
        }

        if ($action === 'delete') {
            query_db("DELETE FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$id);
        } else {
            query_db("UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = 'SYNCED', `oris_entry_id` = " . ($entryId ? (int)$entryId : "NULL") . ", `sync_timestamp` = NOW(), `sync_error_payload` = NULL WHERE `id` = " . (int)$id);
        }
        return true;

    } catch (Exception $e) {
        $errMsg = $e->getMessage();

        if ($e instanceof OrisNetworkException || ($e instanceof OrisApiException && strpos($errMsg, 'API Error or HTTP 5') !== false)) {
            return 'queued';
        }

        $errorData = [
            'status'     => 'error',
            'message'    => $errMsg,
            'api_status' => ($e instanceof OrisApiException) ? $e->getApiStatus() : 'Exception',
            'api_data'   => ($e instanceof OrisApiException) ? $e->getApiData() : null,
        ];
        $errorPayload = correct_sql_string(json_encode($errorData));
        $failedStatus = 'FAILED_' . strtoupper($action);
        query_db("UPDATE `" . TBL_ZAVXUS . "` SET `sync_status` = '$failedStatus', `sync_error_payload` = '$errorPayload' WHERE `id` = " . (int)$id);
        return false;
    }
}

function getOrisSyncError($id) {
    $q = query_db("SELECT sync_error_payload FROM `" . TBL_ZAVXUS . "` WHERE `id` = " . (int)$id);
    if ($q && $r = mysqli_fetch_assoc($q)) {
        if (!empty($r['sync_error_payload'])) {
            $err = json_decode($r['sync_error_payload'], true);
            if (!empty($err['api_data']) && is_string($err['api_data'])) {
                return $err['api_data'];
            }
            return $err['message'] ?? 'Neznámá chyba';
        }
    }
    return 'Neznámá chyba';
}
?>
