<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.8.1.61';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** aktualizace hodnoty sloupce

$sql[1] = 'UPDATE `'.TBL_ZAVXUS.'` SET `termin` = 1 WHERE `termin` = 0';

# *** pridani sloupce poslano do tabulky

$sql[2] = 'ALTER TABLE `'.TBL_RACE.'` ADD `poslano` TINYINT UNSIGNED NOT NULL';

//#############################################################################

require_once ('action.inc.php');
?>