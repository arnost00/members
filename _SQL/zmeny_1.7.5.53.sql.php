<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '1.7.5.53';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** uprava enumu sloupce lic do tabulky users

$sql[1] = 'ALTER TABLE `'.TBL_USER."` CHANGE `lic` `lic` ENUM( 'E', 'A', 'B', 'C', 'D', 'R', '-' ) DEFAULT '-'";

# *** pridani sloupce lic_mtbo a lic_lob do tabulky users

$sql[2] = 'ALTER TABLE `'.TBL_USER."` ADD `lic_mtbo` ENUM( 'E', 'A', 'B', 'C', 'D', 'R', '-' ) DEFAULT '-' AFTER `lic` ,ADD `lic_lob` ENUM( 'E', 'A', 'B', 'C', 'D', 'R', '-' ) DEFAULT '-' AFTER `lic_mtbo`";

# *** smazani sloupce reg2 z tabulky users

$sql[3] = 'ALTER TABLE `'.TBL_USER.'` DROP `reg2`';

# *** zmena sloupce zebricek v tabulce zavod

$sql[4] = 'ALTER TABLE `'.TBL_RACE."` CHANGE `zebricek` `zebricek` ENUM( 'a', 'b', 'c', 'o', 'm', 's', 'v', 'p', 'k', 'l' ) DEFAULT 'c' NOT NULL";

#pridat sloupce mesto a psc do tabulky users

$sql[5] = 'ALTER TABLE `'.TBL_USER."` ADD `mesto` VARCHAR( 25 ) NOT NULL AFTER `adresa` , ADD `psc` VARCHAR( 6 ) NOT NULL AFTER `mesto`";

#pridat sloupce policy_adm do tabulky accounts

$sql[6] = 'ALTER TABLE `'.TBL_ACCOUNT."` ADD `policy_adm` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL AFTER `policy_mng`";

//#############################################################################

require_once ('action.inc.php');
?>