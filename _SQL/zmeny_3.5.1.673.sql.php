<?
// zmeny pro verzi 3.5.1.673 - podpora vyberu etap pro vicedenni (etapove) zavody v ORIS synchronizaci

$version_upd = '3.5.1.673';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** do tabulky zavxus pridej sloupec pro vyber etap (napr. "1,2,3"), ORIS API vyzaduje stageX parametr
$sql[0] = "ALTER TABLE `".TBL_ZAVXUS."` ADD COLUMN `etapy` VARCHAR(20) NULL DEFAULT NULL AFTER `si_chip`";

//#############################################################################

require_once ('action.inc.php');
?>
