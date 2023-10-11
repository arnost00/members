<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php
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

// load json input to $_POST
parse_input_data();

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

function parse_input_data() {
	$_POST = json_decode(file_get_contents('php://input'), true);
}

// optional features //

function require_action() {
    @$action = $_POST["action"];
    if (!isset($action)) {
        raise_and_die("action is not set");
    }
    return $action;
}

function require_race_id() {
    @$race_id = $_POST["race_id"];
    if (!isset($race_id)) {
        raise_and_die("race_id is not set");
    }
    return $race_id;
}

// if set to true, force to use token otherwise user id can be specified
function require_user_id($force_token=false) {
    if (!$force_token) {
        @$user_id = $_POST["user_id"];
        if (isset($user_id)) {
            return $user_id;
        }
    }

    $token = require_token();
    $user_id = $token["user_id"];
    return $user_id;
}

?>
