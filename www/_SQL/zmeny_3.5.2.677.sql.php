<?
// zmeny pro verzi 3.5.2.677 - typ clenstvi (radny clen / hostovani / treninkove clenstvi), issue #79

$version_upd = '3.5.2.677';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** do tabulky users pridej typ clenstvi: R = radny clen, H = hostovani, T = treninkove clenstvi
$sql[0] = "ALTER TABLE `".TBL_USER."` ADD COLUMN `clenstvi` ENUM('R','H','T') NOT NULL DEFAULT 'R' AFTER `finance_type`";

//#############################################################################

require_once ('action.inc.php');
?>
