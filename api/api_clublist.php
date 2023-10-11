<?php
// ----- BEGIN EXCERPT FROM __api.php ----- //
// ----- Errors -----

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// ----- Headers ------
// CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

// JSON output
header("Content-Type: application/json");

// ----- Functions -----

$result = [
    "status" => "ok",
    "message" => "",
    "data" => "",
];

function print_and_die($msg = null) {
    global $result, $mysqli;

	if ($msg !== null) {
		$result["message"] = $msg;
	}

    if (isset($mysqli)) { // close database if is open
        $mysqli->close();
    }

    $output = json_encode($result);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $output = json_encode(["status" => "error", "message" => json_last_error_msg(), "data" => ""]);
    }

    die($output);
}

function raise_and_die($msg = null) {
    global $result;

    $result["status"] = "error";

    print_and_die($msg);
}

// ----- END EXCERPT FROM __api.php ----- //

(@include_once('../../clubs.inc.php')) or raise_and_die("could not load clubs");

$result["data"] = $clubs;

print_and_die();
?>