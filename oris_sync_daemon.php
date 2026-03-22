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
require_once("lib/oris_sync.inc.php");

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
    SELECT zx.*, r.oris_entry_start, u.si_chip as default_si_chip
    FROM `" . TBL_ZAVXUS . "` zx
        LEFT JOIN `" . TBL_RACE . "` r ON zx.id_zavod = r.id
        LEFT JOIN `" . TBL_USER . "` u ON zx.id_user = u.id
    WHERE zx.sync_status IN ('PENDING_CREATE', 'PENDING_UPDATE', 'PENDING_DELETE', 'FAILED_CREATE', 'FAILED_UPDATE', 'FAILED_DELETE')
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
    if ($row['sync_status'] === 'PENDING_CREATE' || $row['sync_status'] === 'FAILED_CREATE') {
        $action = 'create';
    } elseif ($row['sync_status'] === 'PENDING_UPDATE' || $row['sync_status'] === 'FAILED_UPDATE') {
        $action = 'update';
    } elseif ($row['sync_status'] === 'PENDING_DELETE' || $row['sync_status'] === 'FAILED_DELETE') {
        $action = 'delete';
    }

    if (!empty($action)) {
        processEntry($row, $action, $service);
    }
}
?>
