<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.2.0.309';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** zmena nazvu sloupce z zavxus na id_zavod u tabulky finance
$sql[1] = 'ALTER TABLE `'.TBL_FINANCE.'` CHANGE `id_zavxus` `id_zavod` INT( 10 ) UNSIGNED DEFAULT NULL;';

$sql[2] = 'ALTER TABLE `'.TBL_FINANCE.'` CHANGE `id_users_editor` `id_users_editor` SMALLINT(5) UNSIGNED NOT NULL';

$sql[3] = 'ALTER TABLE `'.TBL_FINANCE.'` CHANGE `id_users_user` `id_users_user` SMALLINT(5) UNSIGNED NOT NULL;';

$sql[4] = 'ALTER TABLE `'.TBL_FINANCE.'` ADD `storno` TINYINT(1) DEFAULT NULL,
ADD `storno_by` INT(10) UNSIGNED DEFAULT NULL,
ADD `storno_date` DATE DEFAULT NULL,
ADD `storno_note` VARCHAR(255) COLLATE cp1250_czech_cs DEFAULT NULL;';

//#############################################################################

require ('action.inc.php');
?>