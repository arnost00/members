<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.1.0.91';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** zmena nazvu sloupce z zavxus na id_zavod u tabulky finance
$sql[1] = 'ALTER TABLE `'.TBL_FINANCE.'` CHANGE `id_zavxus` `id_zavod` INT( 10 ) unsigned DEFAULT NULL,
`id_users_editor` smallint(5) unsigned NOT NULL,
`id_users_user` smallint(5) unsigned NOT NULL,';

$sql[2] = 'ALTER TABLE `'.TBL_FINANCE.'` ADD `storno` tinyint(1) DEFAULT NULL,
`storno_by` int(10) unsigned DEFAULT NULL,
`storno_date` date DEFAULT NULL,
`storno_note` varchar(255) COLLATE cp1250_czech_cs DEFAULT NULL,';

//#############################################################################

require ('action.inc.php');
?>