<?php
define('__HIDE_TEST__', 1);

require_once('./connect.inc.php');
require_once('./sess.inc.php');

db_Connect();

if (!IsLoggedAdmin()) {
    header('location: ' . $g_baseadr . 'error.php?code=21');
    exit;
}

require_once('./header.inc.php');
DrawPageTitle('Ruční synchronizace z banky (API)');

echo '<div style="text-align: center;">';
echo '<h3>Spouštím synchronizaci transakcí z banky...</h3>';

require_once('./cron_bank_sync.php');
// The cron script provides `run_bank_sync` function
run_bank_sync();

echo '<p><b>Synchronizace byla dokončena. Zkontrolujte prosím logy pro detaily.</b></p>';
echo '<br>';
echo '<a href="index.php?id=300&subid=1">Zpět do servisního menu</a>';
echo '</div>';

require_once('./footer.inc.php');
?>
