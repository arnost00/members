<?
// zmeny pro verzi 3.5.2.676 - index pro filtrovani financi podle data

$version_upd = '3.5.2.676';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
// SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

$sql[0] = "ALTER TABLE `".TBL_FINANCE."`
    ADD INDEX `idx_finance_storno_date` (`storno`, `date`)";

//#############################################################################

require_once ('action.inc.php');
?>
