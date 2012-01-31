<?php /* zavody - zobrazeni zavodu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Finance èlena');
?>
<CENTER>

<?

$account_id = $usr->account_id;

include ('./user_finance.inc.php');

echo $data_tbl->get_footer()."\n";
?>

<BR>
</CENTER>