<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.7.660';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani tabulky pro bankovni transakce
$sql[1] = "CREATE TABLE `" . TBL_BANK_TRANSACTIONS . "` (
 `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
 `transaction_id` varchar(64) NOT NULL UNIQUE,
 `amount` decimal(10,2) NOT NULL,
 `currency` varchar(3) NOT NULL,
 `variable_symbol` varchar(20) DEFAULT NULL,
 `constant_symbol` varchar(20) DEFAULT NULL,
 `specific_symbol` varchar(20) DEFAULT NULL,
 `originator_message` text DEFAULT NULL,
 `status` enum('PROCESSED', 'ORPHAN', 'ERROR') NOT NULL DEFAULT 'ORPHAN',
 `finance_id` int(10) unsigned DEFAULT NULL,
 `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Bankovni transakce z API';";

//#############################################################################

require_once ('action.inc.php');
?>
