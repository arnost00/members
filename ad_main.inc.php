<?php /* adminova stranka - rozcestnik pro admina */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Administrace stránek oddílu',false);
?>
<CENTER>

<A HREF="index.php?id=<? echo (_ADMIN_GROUP_ID_); ?>&subid=2" class="NaviColSm">Pøihlášky na závody</A><BR>
<A HREF="index.php?id=<? echo (_ADMIN_GROUP_ID_); ?>&subid=5" class="NaviColSm">Editace závodù</A><BR><BR>
<A HREF="index.php?id=<? echo (_ADMIN_GROUP_ID_); ?>&subid=3" class="NaviColSm">Èlenská základna oddílu</A><BR><BR>
<A HREF="index.php?id=<? echo (_ADMIN_GROUP_ID_); ?>&subid=4" class="NaviColSm">Náhled na úèty</A><BR>
<A HREF="index.php?id=<? echo (_ADMIN_GROUP_ID_); ?>&subid=6" class="NaviColSm">Výpis zmìn v databázi</A><BR>

<BR><hr>

<H2>Speciální pomocné "funkce"</H2>
<H3>! Používejte jen pokud víte co èiníte !</H3>

<A HREF="srv_repair_czech_names_db.php" class="NaviColSm">Oprava tøídících jmen u uživatelù</A><BR>
<A HREF="srv_repair_regs_db.php" class="NaviColSm">Oprava tabulky registrací na závody.</A><BR>
<A HREF="_SQL/zmeny.sql.php" class="NaviColSm" target="_blank">Úpravy databáze (patche,updaty)</A><BR>

<BR><hr>

<H2>Informace o systému</H2>

<?

$data_tbl = new html_table_nfo;
echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";

echo $data_tbl->get_new_row('Název systému',SYSTEM_NAME);
echo $data_tbl->get_new_row('Verze systému', GetCodeVersion());
echo $data_tbl->get_new_row('Http Server',$_SERVER['SERVER_SOFTWARE']);
echo $data_tbl->get_new_row('Verze php', phpversion());
echo $data_tbl->get_new_row('Verze MySQL',mysql_get_client_info().' / '.mysql_get_server_info());

echo $data_tbl->get_footer()."\n";
?>

</CENTER>