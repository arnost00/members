<?php /* ORIS sync log viewer - přihlašovatel */
if (!defined("__HIDE_TEST__")) exit; ?>
<?php
define('ORIS_SYNC_LOG', __DIR__ . '/logs/oris_sync.log');
$LOG_LINES = 3000;

DrawPageTitle('ORIS sync — log');

// Handle clear
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_log'])) {
    file_put_contents(ORIS_SYNC_LOG, '');
    echo '<p style="color:green">Log vymazán.</p>';
}

// Read last N lines
$lines = [];
if (file_exists(ORIS_SYNC_LOG)) {
    $all = file(ORIS_SYNC_LOG, FILE_IGNORE_NEW_LINES);
    $lines = array_slice($all, -$LOG_LINES);
    $lines = array_reverse($lines);
}
?>
<form method="post">
  <button type="submit" name="clear_log" value="1"
    onclick="return confirm('Opravdu vymazat log?')"
    style="margin-bottom:8px">Vymazat log</button>
</form>
<?php if (empty($lines)): ?>
  <p>Log je prázdný.</p>
<?php else: ?>
  <pre style="font-size:12px;background:#f4f4f4;color:#111;padding:10px;overflow-x:auto;border:1px solid #ccc"><?php
    foreach ($lines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
  ?></pre>
<?php endif; ?>
