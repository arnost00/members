<?php
require_once("./__api.php");

$action = require_action();

switch ($action) {
    case "countries":
        (@include_once("../country_list_array.inc.php")) or raise_and_die("could not load countries");

        $result["data"] = get_county_list_array();

        print_and_die();
    default:
        raise_and_die("provided action is not implemented");
        break;
}

?>