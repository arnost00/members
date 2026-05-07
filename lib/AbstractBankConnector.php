<?php
require_once dirname(__FILE__) . '/BankConnectorInterface.php';

abstract class AbstractBankConnector implements BankConnectorInterface {

    protected function getBankAccountNumber() {
        global $g_bank_account_number;
        return $g_bank_account_number;
    }

    protected function getBankClientId() {
        global $g_bank_client_id;
        return $g_bank_client_id;
    }

    protected function getCertificatePath() {
        global $g_bank_cert_path;
        return $g_bank_cert_path;
    }

    protected function getCertificatePassword() {
        global $g_bank_cert_pass;
        return $g_bank_cert_pass;
    }

    protected function isMtlsEnabled() {
        return true;
    }
}
