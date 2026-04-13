<?php

interface BankConnectorInterface {
    /**
     * Získá transakce za daný počet dní.
     * 
     * @param int $days_back Počet dní do minulosti (např. 30).
     * @return array Pole transakcí. Každá transakce musí mít tyto klíče:
     *               'transaction_id', 'amount', 'currency', 'vs', 'cs', 'ss', 'msg', 'created_at'
     */
    public function getTransactions($days_back);
}
