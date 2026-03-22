<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");
require_once("connect.inc.php");
require_once("sess.inc.php");
require_once("common.inc.php");
require_once("header.inc.php");

if (!IsLoggedAdmin() && !IsLoggedRegistrator()) {
    die("Access denied. You must be an administrator or a registrator to view this log.");
}

DrawPageTitle('ORIS Sync Log');

$logDir = dirname(__FILE__) . '/logs';
$logFile = $logDir . '/oris_sync.log';

echo '<div style="margin: 20px; font-family: monospace; background: #222; color: #0f0; padding: 10px; border: 1px solid #777; max-height: 600px; overflow-y: scroll;">';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    if ($content !== false) {
        echo nl2br(htmlspecialchars($content));
    } else {
        echo "Error reading log file.";
    }
} else {
    echo "Log file does not exist yet.";
}
echo '</div>';

echo '<div style="margin: 20px;"><a href="index.php">Zpět</a></div>';

HTML_Footer();
?>