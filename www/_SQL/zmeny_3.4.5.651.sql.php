<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.5.651';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupcu pro název zařízení
$sql[1] = 'ALTER TABLE `' . TBL_TOKENS . '` ADD `device_name` VARCHAR( 32 ) NOT NULL AFTER `device`';

# *** přidani sloupcu pro sledování verzí nainstalované aplikace
$sql[2] = 'ALTER TABLE `' . TBL_TOKENS . '` ADD `app_version` VARCHAR( 16 ) NOT NULL';

# *** přidani sloupcu pro sledování času posledního otevření aplikace
$sql[3] = 'ALTER TABLE `' . TBL_TOKENS . '` ADD `app_last_opened` TIMESTAMP NOT NULL';

//#############################################################################

require_once ('action.inc.php');
?>
