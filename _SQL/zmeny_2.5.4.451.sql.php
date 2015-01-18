<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.5.4.451';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani tabulky pro typu oddilovych prispevku
$sql[1] = 'CREATE TABLE `'.TBL_FINANCE_TYPES."` (`id` int unsigned NOT NULL AUTO_INCREMENT,
  `nazev` VARCHAR( 50 ) NOT NULL ,
  `popis` VARCHAR( 255 ) NOT NULL ,
  PRIMARY KEY (`id`) )";
  
 # *** pridani sloupcu pro dopravu
$sql[2] = 'ALTER TABLE `'.TBL_USER.'` ADD `finance_type` int unsigned NOT NULL DEFAULT 0'; 

//#############################################################################

require ('action.inc.php');
?>
