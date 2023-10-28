<?php
// ----- BEGIN EXTRACT FROM __api.php ----- //
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

$result = [];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "POST": // let the code logic to handle
        break;
    case "OPTIONS":  // preflight cors request
        print_and_die();
        break;
    default:
        raise_and_die("method is not allowed", 405);
        break;
}

function print_and_die($response_code=200) {
    global $result, $mysqli;

    if (isset($mysqli)) { // close database if is open
        $mysqli->close();
    }

    $output = json_encode($result);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $output = '{"message": "' . json_last_error_msg() . '"}';
        $response_code = 500;
    }

    http_response_code($response_code);

    die($output);
}

function raise_and_die($error = null, $response_code=400) {
    global $result;

    $result = ["message" => $error];

    print_and_die($response_code);
}

// ----- END EXTRACT FROM __api.php ----- //

(@include_once('./clubs.inc.php')) or raise_and_die("could not load clubs", 500);

$result = $clubs;

print_and_die();
?>