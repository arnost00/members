<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
require_once ("./cfg/_cfg.php");
require_once ("./common.inc.php"); // For GetFirstEmail

if (!IsLoggedSmallAdmin())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

$oris_id = (isset($oris_id) && is_numeric($oris_id)) ? (int)$oris_id : 0;
$user_id = (isset($id) && is_numeric($id)) ? (int)$id : 0;

if ($oris_id > 0 && $user_id > 0)
{
    db_Connect();
    $vysledek=query_db("SELECT * FROM ".TBL_USER." WHERE id = '$user_id' LIMIT 1");
    $zaznam=mysqli_fetch_array($vysledek);

    if ($zaznam)
    {
        $params = array(
            'format' => 'json',
            'method' => 'editPerson',
            'userid' => $oris_id,
            'si' => $zaznam['si_chip'],
            'clubkey' => $g_oris_club_key,
            'firstname' => $zaznam['jmeno'],
            'lastname' => $zaznam['prijmeni'],
            'email' => GetFirstEmail($zaznam['email']),
            'street' => $zaznam['adresa'],
            'city' => $zaznam['mesto'],
            'zip' => $zaznam['psc'],
            'country' => (!empty($zaznam['narodnost']) ? $zaznam['narodnost'] : 'CZ')
        );
        
        $url = "https://oris.ceskyorientak.cz/API/?" . http_build_query($params);
        
        // Use file_get_contents
        $response = file_get_contents($url);
        $result = json_decode($response);
        
        if ($result && $result->Status == 'OK') {
            // Success
            header("location: ".$g_baseadr."index.php?id="._SMALL_ADMIN_GROUP_ID_."&subid=2");
        } else {
            // Error
            require_once ("./header.inc.php"); 
            DrawPageTitle('Chyba synchronizace s ORIS');
            echo "Nepodařilo se aktualizovat údaje v systému ORIS.<br>";
            echo "<b>Debug info:</b><br>";
            echo "URL: " . $url . "<br>";
            echo "Raw response: " . htmlspecialchars($response) . "<br>";
            echo "Odpověď serveru: " . ($result ? $result->Status : "Unknown error") . "<br>";
            if (isset($result->Message)) echo "Zpráva: " . $result->Message . "<br>";
            if (!isset($g_oris_club_key) || empty($g_oris_club_key)) echo "<b>Warning: API key is empty!</b><br>";
            echo "<br><a href=\"".$g_baseadr."index.php?id="._SMALL_ADMIN_GROUP_ID_."&subid=2\">Zpět</a>";
            HTML_Footer();
        }
    } else {
        echo "User not found in DB.";
    }
} else {
    header("location: ".$g_baseadr."index.php?id="._SMALL_ADMIN_GROUP_ID_."&subid=2");
}
?>
