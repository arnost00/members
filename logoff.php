<?
require("./cfg/_cfg.php");
require ("./sess.inc.php");
$usr = new sess;
$_SESSION["usr"] = $usr;
header("location: ".$g_baseadr);
?>