<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.2.2.577';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani tabulky pro preddefinovane 
$sql[1] = 'CREATE TABLE `'.TBL_CATEGORIES_PREDEF."` (`id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(50) NOT NULL, `cat_list` varchar(255) NOT NULL) COLLATE 'utf8_czech_ci';";

$sql[2] = 'INSERT INTO `'.TBL_CATEGORIES_PREDEF."` (`id`, `name`, `cat_list`) VALUES 
(1, 'Oblž', 'D10N;D12;D14;D16;D18;D21C;D21D;D35;D45;D55;H10N;H12;H14;H16;H18;H21C;H21D;H35;H45;H55;HDR;'),
(2, 'Oblž větší', 'D10N;D12C;D14C;D16C;D18C;D21C;D21D;D35C;D45C;D55C;H10N;H12C;H14C;H16C;H18C;H21C;H21D;H35C;H45C;H55C;HDR;'),
(3, 'žebříček B', 'D12B;D14B;D16B;D18B;D20B;D21B;D21C;D35B;D40B;D45B;D50B;D55B;D60B;D65B;H12B;H14B;H16B;H18B;H20B;H21B;H21C;H35B;H40B;H45B;H50B;H55B;H60B;H65B;H70B;H75B;'),
(4, 'žebříček A', 'D16A;D18A;D20A;D21A;D21E;H16A;H18A;H20A;H21A;H21E;'),
(5, 'Štafety', 'D14;D18;D21;D105;D140;H14;H18;H21;H105;H140;H165;dorost;dospělí;HD175;HD235;'),
(6, 'MTBO', 'W11;W14;W17;W20;W21E;W21A;W21B;W40;W50;W60;M11;M14;M17;M20;M21E;M21A;M21B;M40A;M40B;M50;M60;OPEN;');";

//#############################################################################

require_once ('action.inc.php');
?>
