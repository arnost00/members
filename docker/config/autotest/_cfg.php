<?

/*

	Configuration data
	Copy this file as '_cfg.php' and set your real data for running members

*/

//==================================================================
// config file
//==================================================================

//==================================================================
// db server
//==================================================================
// adresa db serveru
$g_dbserver='db';
$g_dbport='3306';

// uzivatelske informace
$g_dbuser='root';
$g_dbpass='lkj234@sf!--+KLM';

// jmeno databaze
$g_dbname='members_test';

//==================================================================
// jwt secret key
//==================================================================
// head -c 64 /dev/urandom | base64
$g_jwt_secret_key = "DEVELOPMENT+ONLY+++GENERATE+A+SECURE+64+BYTE+KEY+IN+PRODUCTION++/uV4zp8UPeLiSXUL62Ae/w=="; // ### TREBA ZMENIT ###

//==================================================================
// api
//==================================================================
// api version
$g_api_version = 2.12;
// secret token to protect cron/admin-only scripts from public HTTP access
// generate with: openssl rand -hex 32
$g_cron_secret = 'CHANGE_ME_TO_A_RANDOM_STRING';
//==================================================================
// http server
//==================================================================
// jmeno WWW serveru
$g_shortcut='ZBM';
$g_fullname='AUTOTEST SK Žabovřesky Brno';
$g_www_title= $g_shortcut.' :: '.$g_fullname;
$g_www_name= $g_shortcut.' - '.$g_fullname;

$g_www_meta_description = "Stránky oddílu orientačního běhu ".$g_fullname;
$g_www_meta_keyword = $g_shortcut.", Žabovřesky, Zabovresky";

// zakladni URL adresa WWW serveru (ukoncen "/" !!)
$g_baseadr='http://web:10100/members/';

// adresa hlavnich stranek oddilu
$g_mainwww='http://web:10100/';

// e-mailove adresy
$g_emailadr='email@eob.cz';
// Logovat informace o neuspesnem prihlaseni
$g_log_loginfailed=true;

$g_is_release = true;

//==================================================================
// customization
//==================================================================
$g_mail_in_public_directory = true;

// vedouci na zavody
$g_enable_race_boss = true;

$g_club_logo['FileN'] = 'logo.png';
$g_club_logo['SizeH'] = 60;
$g_club_logo['SizeW'] = 156;

$g_enable_mailinfo = true;
$g_mailinfo_minimal_daysbefore = 1;
$g_mailinfo_maximal_daysbefore = 14;

// nastaveni barevneho profilu
$g_color_profile = '_colors_black_blue.php';

// finance
$g_enable_finances = true;
$g_enable_finances_claim = true;
$g_finances_race_list_sort_old = false;
$g_finance_payement_IBAN = 'CZ6855000000004067843369';
//$g_finance_payement_VS = '001';


// doprava & ubytovani 
$g_enable_race_transport = true;
$g_race_transport_default = 1;
$g_enable_race_accommodation = true;
$g_race_accommodation_default = 1;
$g_enable_race_capacity = true;

// Externi informacni system - podporovane hodnoty 'OrisCZConnector' a ''
$g_external_is_connector = 'OrisCZConnector';
// Identifikator oddilu v informacnim systemu
$g_external_is_club_id = '205';

$g_custom_entry_list_text = '';

//==================================================================
// bank API
//==================================================================
$g_bank_connector = 'RaiffeisenbankMockConnector';
// Nastavení bankovního API
$g_bank_cert_path = '/path/to/cert.p12';
$g_bank_cert_pass = 'cert_password';
$g_bank_client_id = 'raiffeisen_client_id';
$g_bank_account_number = '4067843369';
$g_bank_sync_start_date = '';

$g_cfg_date_path = '/tmp/members_cfg_date.php';
if (file_exists($g_cfg_date_path)) {
    require_once($g_cfg_date_path);
}

?>
