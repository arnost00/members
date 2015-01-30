<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.0.1.75';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupce si_chip do tabulky zavxus
$sql[1] = 'ALTER TABLE `'.TBL_ZAVXUS."` ADD `si_chip` INT( 9 ) UNSIGNED DEFAULT '0' NOT NULL";

# *** uprava sloupce poznamka v tabulce race
$sql[2] = 'ALTER TABLE `'.TBL_RACE."` CHANGE `poznamka` `poznamka` TEXT NOT NULL";

# *** uprava sloupce si_chip v tabulce user
$sql[3] = 'ALTER TABLE `'.TBL_USER."` CHANGE `si_chip` `si_chip` INT( 9 ) UNSIGNED DEFAULT '0' NOT NULL";

//#############################################################################

require_once ('action.inc.php');
?>