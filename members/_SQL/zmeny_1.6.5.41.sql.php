<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.6.5.41';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** zmena sloupce `reg` v tabulce users

$sql[1] = 'ALTER TABLE `'.TBL_USER."` CHANGE `reg` `reg` INT( 4 ) UNSIGNED ZEROFILL DEFAULT '0' NOT NULL";

# *** zmena hodnoty ve sloupci `policy_mng` v tabulce accounts

$sql[2] = 'UPDATE `'.TBL_ACCOUNT.'` SET `policy_mng` = 4 WHERE `policy_mng` = 1';


//#############################################################################

require_once ('action.inc.php');
?>