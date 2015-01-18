<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

@extract($_REQUEST);

require ("connect.inc.php");
require ("sess.inc.php");
require ("ctable.inc.php");
if (!IsLoggedFinance())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
require("cfg/_globals.php");

db_Connect();
$user_id = (isset($user_id) && is_numeric($user_id)) ? (int)$user_id : 0;

$sql_query = 'SELECT * FROM '.TBL_USER." WHERE id = '$user_id' LIMIT 1";

// id je z tabulky "finance types"
$sql_query2 = 'SELECT * FROM '.TBL_FINANCE_TYPES.' ORDER BY id';


@$vysledek=MySQL_Query($sql_query);
@$zaznam=MySQL_Fetch_Array($vysledek);
@$vysledek2=MySQL_Query($sql_query2);
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>
include ("./common.inc.php");

DrawPageTitle('Výbìr typu oddílového pøíspìvku pro èlena');

DrawPageSubTitle($zaznam['sort_name']);

function get_row($id,$nazev,$popis,$selected_id)
{
	$row = '<input type="radio" name="type" value="'.$id.'" id="radio_'.$id.'" ';
	if ($selected_id==$id) 
		$row .= 'checked="checked"';
	$row .='><label for="radio_'.$id.'">'.$nazev.'</label><br>';
	return $row;
}

?>

<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<CENTER>
<?
if ($vysledek === FALSE )
{
	echo('Chyba v databázi, kontaktuje administrátora.<br>');
}
else
{
?>
<FORM METHOD="POST" ACTION="user_finance_type_exc.php<? echo "?user_id=".$user_id?>">
<?
	$data_tbl = new html_table_form('fin_type');
	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	//row 0
	$rows = get_row(0,'Není definováno','',$zaznam["finance_type"])."\n";

	$num_rows = mysql_num_rows($vysledek2);
	if ($num_rows > 0)
	{
	
		while ($zaznam2=MySQL_Fetch_Array($vysledek2))
		{
		    $rows .= get_row($zaznam2['id'],$zaznam2['nazev'],$zaznam2['popis'],$zaznam["finance_type"]);
			$rows .= "\n";
		}
		
	echo $data_tbl->get_new_row_text('Typ pøíspìvku',$rows);
/*

Název', '<INPUT TYPE="text" NAME="nazev" size="50" MAXLENGTH=50 VALUE="'.$zaznam['nazev'].'">');
echo $data_tbl->get_new_row('Popis', '<TEXTAREA name="popis" cols="50" rows="10" wrap=virtual>'.$zaznam['popis'].'</TEXTAREA>');
*/
echo $data_tbl->get_empty_row();
echo $data_tbl->get_new_row('','<INPUT TYPE="submit" VALUE="Odeslat">');
echo $data_tbl->get_footer()."\n";

	}
?>
</FORM>
<?
}
?>
<BR><hr><BR>
<A HREF="index.php?id=800&subid=1">Zpìt</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?include ("footer.inc.php");?>
<!-- Footer End -->
</TD></TR>
</TABLE>

</BODY>
</HTML>
