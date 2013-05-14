<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '2.2.0.389';

//#############################################################################

require ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani tabulky pro reklamace
$sql[1] = 'CREATE TABLE `'.TBL_CLAIM."` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned NOT NULL,
  `text` text COLLATE cp1250_czech_cs NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) )";
//#############################################################################

require ('action.inc.php');
?>