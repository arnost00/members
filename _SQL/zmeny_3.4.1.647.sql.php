<?

//#############################################################################
//	vychozi verze
//#############################################################################

$version_upd = '3.4.1.647';

//#############################################################################

require_once ('prepare.inc.php');

//#############################################################################
//	SQL dotazy pro zmenu db. na novejsi verzi
//#############################################################################

# *** pridani sloupcu pro notifikace
$sql[1] = "CREATE TABLE `" . TBL_TOKENS . "` (
 `device` varchar(36) NOT NULL COMMENT 'https://capacitorjs.com/docs/apis/device#deviceid',
 `user_id` smallint(5) NOT NULL,
 `fcm_token` varchar(4096) NOT NULL,
 `fcm_token_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'update on token update',
 PRIMARY KEY (`device`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci";

//#############################################################################

require_once ('action.inc.php');
?>
