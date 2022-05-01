<?
if (!IsSet($action)) $action = 0;

_print_upd_info();

if ($action == 0)
{
	_list_sql_queries($sql);
}
else if ($action == 1)
{
	db_connect();
	_run_sql_queries($sql);
	_list_sql_queries($sql, true);
	db_close();
}
else if ($action == 2)
{
	_list_sql_queries_copy_paste($sql);
}
else if ($action == 3)
{
	db_connect();
	post_sql_function();
	db_close();
}
else
	echo '- nothing -'."\n";


echo '<HR>';
echo '<a href="./zmeny.sql.php">Zpět</a>';
echo '<HR>';
echo '<span class="HiddenText">--end--</span>'."\n";
echo '</body></html>';
?>