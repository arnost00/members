<?
require_once("./cfg/_cfg.php");
require_once ("./sess.inc.php");
$usr = new sess;
$_SESSION["usr"] = $usr;
header("location: ".$g_baseadr);
?>