<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.0.5.482';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupcu pro dopravu
$sql[1] = 'ALTER TABLE `'.TBL_RACE.'` ADD `ubytovani` TINYINT( 1 ) NULL DEFAULT NULL AFTER `transport`';
$sql[2] = 'ALTER TABLE `'.TBL_ZAVXUS.'` ADD `ubytovani` TINYINT( 1 ) NULL DEFAULT NULL AFTER `transport`';

//#############################################################################

require_once ('action.inc.php');
?>
