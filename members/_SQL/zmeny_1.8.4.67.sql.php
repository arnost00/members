<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.8.4.67';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupce poslano do tabulky
$sql[1] = 'ALTER TABLE `'.TBL_RACE."` ADD `send` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL";

$sql[2] = 'ALTER TABLE `'.TBL_USER.'` ADD `rc` VARCHAR( 10 ) NOT NULL';

$sql[3] = 'ALTER TABLE `'.TBL_USER.'` CHANGE `si_chip` `si_chip` INT( 9 ) UNSIGNED DEFAULT NULL';

//#############################################################################

require_once ('action.inc.php');
?>