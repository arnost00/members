<?php

define('__HIDE_TEST__', '_KeAr_PHP_WEB_');

require_once './cfg/_globals.php';
require_once './connect.inc.php';
require_once './sess.inc.php';

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (!IsLoggedFinance() || !$g_enable_finances) {
    http_response_code(403);
    echo 'Přístup odepřen.';
    exit;
}

require_once './finance_filters.inc.php';

$range = FinanceSqlFilter::stringValue($_GET, 'range');
if (!FinanceHistoryFilter::isValidRangeKey($range)) {
    http_response_code(400);
    echo 'Neplatné časové období.';
    exit;
}

db_Connect();

require_once './common.inc.php';
require_once './ctable.inc.php';
require_once './ct_renderer_fin_history.inc.php';

$filter = new FinanceHistoryFilter($_GET);
try {
    $records = FinanceHistoryRepository::findForRanges($filter, [$range]);
} catch (RuntimeException $exception) {
    http_response_code(500);
    echo 'Transakce se nepodařilo načíst.';
    exit;
}

if (count($records) === 0) {
    http_response_code(404);
    echo 'Časové období nebylo nalezeno.';
    exit;
}

$table = FinanceHistoryRendererFactory::createHistoryTable();
echo $table->renderRows(new html_table_mc(), $records);
