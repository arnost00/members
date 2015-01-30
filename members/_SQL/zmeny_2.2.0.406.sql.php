<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.2.0.406';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupce pro reklamace
$sql[1] = 'ALTER TABLE `'.TBL_FINANCE.'` ADD `claim` TINYINT( 1 ) NULL DEFAULT NULL COMMENT \'null = bez reklamace, 1 = aktivni reklamace, 0 = uzavrena reklamace\'';
//#############################################################################

require_once ('action.inc.php');
?>
