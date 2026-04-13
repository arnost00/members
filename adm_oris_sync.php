<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");
require_once("connect.inc.php");
require_once("sess.inc.php");
require_once("common.inc.php");

if (!IsLoggedAdmin() && !IsLoggedRegistrator()) {
    die("Access denied. You must be an administrator or a registrator to run this script manually.");
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ORIS Sync Daemon Manual Trigger</title>
    <style>
        body { font-family: monospace; background: #222; color: #0f0; padding: 20px; }
        .log { white-space: pre-wrap; margin-top: 20px; }
        .btn { padding: 10px 20px; background: #444; color: #fff; text-decoration: none; border: 1px solid #777; }
    </style>
</head>
<body>
    <h2>Manual ORIS Sync Execution</h2>
    <p>Executing daemon...</p>
    <div class="log">
<?php
// Capture output
ob_start();
define('ORIS_MANUAL_SYNC', true);
try {
    require_once("oris_sync_daemon.php");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

// Display standard output
echo htmlspecialchars($output);
?>
    </div>
    <br>
    <a href="index.php" class="btn">Return to System</a>
</body>
</html>