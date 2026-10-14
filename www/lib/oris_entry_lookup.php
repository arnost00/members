<?php
// Whether the ORIS entry still exists in the event. Lookup failures or malformed data count as "exists"
// so the local entry is never deleted blindly.
function orisEntryExists($service, $comp, $entryId) {
    try {
        $entriesRes = $service->getEventEntries($comp);
    } catch (Exception $e) {
        return true;
    }
    if (!is_array($entriesRes)) {
        return true;
    }
    foreach ($entriesRes as $entry) {
        // Only a valid list can confirm absence. ORIS may key the list by entry ID.
        if (!is_array($entry) || !isset($entry['ID'])
            || (!is_int($entry['ID']) && !is_string($entry['ID']))
            || !preg_match('/^[1-9][0-9]*$/D', (string)$entry['ID'])) {
            return true;
        }
        if ((string)$entry['ID'] === (string)$entryId) {
            return true;
        }
    }
    return false;
}
