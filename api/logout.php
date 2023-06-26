<?php
// require_once("../sess.inc.php");

require_once("./__api.php");

session_start();

if (isset($_SESSION["usr"]))
{
    session_unset();
    session_destroy();
}

print_and_die();
?>