<?php /* adminova stranka - rozcestnik pro admina */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Administrace stránek oddílu');
?>
<CENTER>
<? DrawPageSubTitle('Speciální pomocné "funkce"'); ?>
<B>! Používejte jen pokud víte co činíte !</B><BR>

<A HREF="srv_repair_regs_db.php" class="NaviColSm">Oprava tabulky registrací na závody.</A><BR>
<A HREF="_SQL/zmeny.sql.php" class="NaviColSm" target="_blank">Úpravy databáze (patche,updaty)</A><BR>

<BR><hr>

<? 
DrawPageSubTitle('Informace o systému');

$data_tbl = new html_table_nfo;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Název systému',SYSTEM_NAME);
echo $data_tbl->get_new_row('Verze systému', GetCodeVersion());
echo $data_tbl->get_new_row('Http Server',$_SERVER['SERVER_SOFTWARE']);
echo $data_tbl->get_new_row('Verze php', phpversion());
echo $data_tbl->get_new_row('Verze MySQL',mysqli_get_client_info($db_conn).' / '.mysqli_get_server_info($db_conn));

echo $data_tbl->get_footer()."\n";
?>

</CENTER>