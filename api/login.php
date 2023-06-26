<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require_once("../connect.inc.php");
require_once("../common.inc.php");

require_once("./__jwt.php");
require_once("./__api.php");

load_json_data();

$username = $_POST["username"];
$password = $_POST["password"];

if (!isset($username) | !isset($password))
{
    raise_and_die("username or password is not set");
}

db_Connect();

$username = correct_sql_string($username);
$password = correct_sql_string($password);

$query = "SELECT * FROM " . TBL_ACCOUNT . " WHERE `login` = '" . $username . "' LIMIT 1";
// $query = 'SELECT * FROM ' . TBL_ACCOUNT . ' WHERE `login` = \'' . $username . '\' LIMIT 1';

// check output
$output = query_db($query) or raise_and_die("error while communicating with database");

// check username
$output = mysqli_fetch_array($output);
if (!$output)
{
    raise_and_die("invalid username");
}

// check password
if (!password_verify(md5($password), $output["heslo"]))
{
    raise_and_die("invalid password");
}

// check account lock
if ($output["locked"])
{
    raise_and_die("account is locked");
}

// set last visited
$query = "UPDATE " . TBL_ACCOUNT . " SET last_visit='" . GetCurrentDate() . "' WHERE id='" . $output["id"] . "'";
query_db($query) or raise_and_die("error while updating last visit");

print_r($output);

$result["data"] = generate_jwt([
    "user_id" => $output["id_users"],
    "name" => $output["login"],
]);

print_and_die();
?>