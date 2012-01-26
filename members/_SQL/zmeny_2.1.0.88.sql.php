<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.1.0.88';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani tabulky finance
$sql[1] = 'CREATE TABLE IF NOT EXISTS `'.TBL_FINANCE."` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_accounts_editor` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_accounts_user` smallint(5) unsigned NOT NULL DEFAULT '0',
  `id_zavxus` int(10) unsigned DEFAULT NULL,
  `amount` smallint(5) NOT NULL,
  `date` date NOT NULL,
  `note` varchar(255) CHARACTER SET cp1250 COLLATE cp1250_czech_cs DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1250 COLLATE=cp1250_czech_cs AUTO_INCREMENT=1";

# *** pridani sloupce pro pravo na finance do tabulky accounts
$sql[2] = 'ALTER TABLE `'.TBL_ACCOUNTS."` ADD `policy_fin` TINYINT( 1 ) UNSIGNED DEFAULT '0' NOT NULL";

//#############################################################################

require ('action.inc.php');
?>