<?php
require_once dirname(__FILE__) . '/RaiffeisenbankConnector.php';

class RaiffeisenbankMockConnector extends RaiffeisenbankConnector {

    protected function getBaseApiUrl() {
        return 'http://127.0.0.1:10300/rbcz/premium/api';
    }

    protected function isMtlsEnabled() {
        return false;
    }
}
