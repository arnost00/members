<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance èlena',false);
?>
<CENTER>
<?
//inicializace id uzivatele pro vypis financi
$user_id = $usr->user_id;

include ('./user_finance.inc.php');
?>
<BR>
</CENTER>