<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance èlena',false);
?>
<CENTER>
<?
//inicializace id uzivatele pro vypis financi
$user_id = $usr->user_id;
//zamezi zobrazeni moznosti pro zmenu z Clenskeho menu
$finance_readonly = "readonly";

include ('./user_finance.inc.php');
?>
<BR>
</CENTER>