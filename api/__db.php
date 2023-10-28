<?php
require_once('../cfg/_cfg.php');
require_once('../cfg/_tables.php');
require_once("./__api.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // debug

if (!isset($mysqli)) {
    $mysqli = new mysqli($g_dbserver, $g_dbuser, $g_dbpass, $g_dbname);
    
    if ($mysqli->connect_errno) {
        raise_and_die("db connect error: " . $mysqli->connect_error);
    }
    
    $mysqli->query("SET CHARACTER SET UTF8");
}

function db_execute($query, ...$params) {
    global $mysqli;

    $types = join("", array_map(function ($var) {return _determine_type($var);}, $params));

    try {
        $prepared = $mysqli->prepare($query);
        $prepared->bind_param($types, ...$params);
        // foreach ($params as $key => $val) {
        //     $prepared->bindParam($key, $val);
        // }
        $prepared->execute();
        $output = $prepared->get_result();
        $prepared->close();
    } catch (Throwable $e) {
        raise_and_die("db error: " . $e->getMessage(), 500);
    }

    return $output;
}

function _determine_type($var) {
    switch (gettype($var)) {
        case "boolean":
        case "integer":
            return "i";
        case "double":
            return "d";
        case "string":
        default:
            return "s";
    }
}
?>