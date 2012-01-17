<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.7.4.51';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupce reg_bike do tabulky users

$sql[1] = 'ALTER TABLE `'.TBL_USER.'` ADD `reg2` INT( 4 ) UNSIGNED ZEROFILL DEFAULT \'0000\' NOT NULL AFTER `reg`';

# *** pridani sloupce typ do tabulky zavxus

$sql[2] = 'ALTER TABLE `'.TBL_ZAVXUS.'` ADD `pozn_in` VARCHAR(255) DEFAULT NULL';

//#############################################################################

require ('action.inc.php');
?>