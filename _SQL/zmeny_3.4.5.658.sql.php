<?
// zmeny pro verzi 3.4.9.658 - ORIS API integration

$version_upd = '3.4.5.658';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//############################################################################
$sql[0] = "ALTER TABLE `".TBL_ZAVXUS."` 
    ADD COLUMN `oris_entry_id` INT UNSIGNED NULL,
    ADD COLUMN `sync_status` ENUM('LOCAL_ONLY', 'SYNCED', 'PENDING_CREATE', 'PENDING_UPDATE', 'PENDING_DELETE', 'FAILED_CREATE', 'FAILED_UPDATE', 'FAILED_DELETE') NOT NULL DEFAULT 'LOCAL_ONLY',
    ADD COLUMN `sync_timestamp` DATETIME NULL,
    ADD COLUMN `sync_error_payload` TEXT NULL,
    ADD INDEX `sync_status` (`sync_status`)";

$sql[1] = "ALTER TABLE `".TBL_RACE."` 
    ADD COLUMN `oris_entry_start` DATETIME NULL";

require_once ('action.inc.php');
?>