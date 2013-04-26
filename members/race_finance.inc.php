<link rel="stylesheet" type="text/css" href="css_finance.css" media="screen" />
<?php /* finance -  show exact race finance */

$query_prihlaseni = "
select u.id u_id, u.sort_name, f.id, f.amount, f.note, zu.kat from ".TBL_USER." u inner join
".TBL_ZAVXUS." zu on u.id = zu.id_user left join
(select * from ".TBL_FINANCE." where id_zavod = $race_id and storno is null) f on f.id_users_user = zu.id_user
where zu.id_zavod = $race_id and u.hidden = '0' order by u.sort_name
";
$vysledek_prihlaseni = mysql_query($query_prihlaseni);

$query_platici = "
select u.id u_id, u.sort_name, f.id, f.amount, f.note, null kat from ".TBL_USER." u inner join
(select * from ".TBL_FINANCE." where id_zavod = $race_id and storno is null) f on f.id_users_user = u.id
where f.id_zavod = $race_id
and u.id not in (select id_user from ".TBL_ZAVXUS." where id_zavod = $race_id) 
and u.hidden = '0' 
order by u.sort_name
";
$vysledek_platici = mysql_query($query_platici);

$query_neprihlaseni = "
select u.id u_id, u.sort_name, null id, null amount, null note, null kat from ".TBL_USER." u where id not in
(SELECT distinct(f.id_users_user) id
FROM ".TBL_FINANCE." f where f.id_zavod = $race_id and f.storno is null
union
SELECT distinct(zu.id_user) id
FROM ".TBL_ZAVXUS." zu where zu.id_zavod = $race_id) 
and u.hidden = '0' 
order by u.sort_name
";
$vysledek_neprihlaseni = mysql_query($query_neprihlaseni);

//vytazeni informaci o zavode
@$vysledek_race=MySQL_Query("select z.nazev, from_unixtime(z.datum, '%Y-%c-%e') datum from ".TBL_RACE." z where z.id = ".$race_id);
$zaznam_race=MySQL_Fetch_Array($vysledek_race);

DrawPageSubTitle('Vybraný závod');

@$vysledek_z=MySQL_Query('SELECT * FROM '.TBL_RACE." WHERE `id`='$race_id' LIMIT 1");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

include_once ("./url.inc.php");
include_once ("./common_race.inc.php");

RaceInfoTable($zaznam_z,'',false,false,true);

include_once ('./url.inc.php');

?>
<div class="update-categories">
<div class="sub-title">Naplò pouze vybrané kategorie pro pøihlášené závodníky</div>
Vše<input type="checkbox" id="all-ckbx"/><div id="ckbx-cat"></div>
<label for="in-amount">Èástka</label><input type="number" id="in-amount"/>
<label for="in-note">Poznámka</label><input type="text" id="in-note"/>
<button onclick="fillInputsByCategory()">Vlož</button>
</div>

<script>
//zaskrtnuti vse checkboxu po kliku na Vse
$("#all-ckbx").click( function() {
	$(".ckbx-cat").prop('checked', $("#all-ckbx").is(':checked'));
});

//naplni amount a note hodnotama z inputu in-amount a in-note
function fillInputsByCategory() {
	var kat = $("#in-kat").val();
	var amount = $("#in-amount").val();
	var note = $("#in-note").val();

	jQuery.each( cats, function( index, value ) {
		if ($('#ckbx-'+value).is(':checked')) {
			$("input.amount-"+value).val(amount);
			$("input.note-"+value).val(note);
		}
				
	});
}
</script>

<? 

echo "<form method=\"post\" action=\"?payment=pay&race_id=$race_id\">";

DrawPageSubTitle('Závodníci v závodì');
$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
IsLoggedFinance()?$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER):"";


echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_amount = 0;
$i = 1;

echo $data_tbl->get_subheader_row("Pøihlášení")."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_prihlaseni))
{
	$id = $zaznam['id'];
	
	$row = array();
	$row[] = $zaznam['sort_name'];
	
	$amount = $zaznam['amount'];
	$input_amount = '<input class="amount-'.$zaznam['kat'].'" type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" size="5" maxlength="10" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input class="note-'.$zaznam['kat'].'" type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" size="40" maxlength="200" />';
	$row[] = $input_note;
	
	$row[] = "<span class=\"cat\">".$zaznam['kat']."</span>";
	$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['u_id'].'\',\'\')">Platby</A>';
	$row_text .= '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>'; 
	$row[] = $row_text;
	
	$row_class = "cat-".$zaznam['kat'];
	echo $data_tbl->get_new_row_arr($row, $row_class)."\n";
	$i++;
}
if ($i == 1)
{	// zadny zavodnik prihlasen
	echo $data_tbl->get_info_row('Není nikdo pøihlášen.')."\n";
}
$i0 = $i;
//---------------------------------------------------
echo $data_tbl->get_subheader_row("Nepøihlášení s platbami")."\n";
while ($zaznam=mysql_fetch_assoc($vysledek_platici))
{
	$id = $zaznam['id'];
	
	$row = array();
	$row[] = $zaznam['sort_name'];

	$amount = $zaznam['amount'];
	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" size="5" maxlength="10" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" size="40" maxlength="200" />';
	$row[] = $input_note;

	$row[] = $zaznam['kat'];

	$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['u_id'].'\',\'\')">Platby</A>';
	$row_text .= '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	$row[] = $row_text;	

	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
if (($i - $i0) == 0)
{	// zadny zavodnik s vkladem
	echo $data_tbl->get_info_row('Není nikdo jen s platbou.')."\n";
}

echo $data_tbl->get_footer()."\n";


echo '<br><input type="submit" value="Zmìnit platby"/>';
echo '</form>';
echo "<form method=\"post\" action=\"?payment=pay&race_id=$race_id\">";

DrawPageSubTitle('Ostatní závodníci');
$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Èástka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
IsLoggedFinance()?$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER):"";

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i = 1;
while ($zaznam=mysql_fetch_assoc($vysledek_neprihlaseni))
{
	
	$id = $zaznam['id'];
	
	$row = array();
	$row[] = $zaznam['sort_name'];
	
	$amount = $zaznam['amount'];
	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" size="5" maxlength="10" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" size="40" maxlength="200" />';
	$row[] = $input_note;
	
	$row[] = $zaznam['kat'];
	// 	IsLoggedFinance()?$row[]=" <a href=\"?change=change&trn_id=".$zaznam['fin_id']."\">Zmìnit</a> / <a href=\"?storno=storno&trn_id=".$zaznam['fin_id']."\">Storno</a>":"";
	$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['u_id'].'\',\'\')">Platby</A>';
	$row_text .= '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	$row[] = $row_text;

	echo $data_tbl->get_new_row_arr($row)."\n";
	$i++;
}
if ($i == 1)
{	// neni nikdo neprihlasen
	echo $data_tbl->get_info_row('Není nikdo kdo by nebyl pøihlášen.')."\n";
}

echo $data_tbl->get_footer()."\n";

?>
<div class="link-top"><a href="#top">Nahoru ...</a></div>
<input type="submit" value="Vytvoøit nové platby">
</form>

<script>
//vlozeni checkboxu pro vyber kategorii, kterym se budou menit platby
var ckbx = document.getElementById("ckbx-cat");
var cats = [];
$("span.cat").each(function(event) {
	//vloz novou kategorii, pokud jiz neni v seznamu
	if (cats.indexOf(this.innerHTML,0) < 0)	cats.push(this.innerHTML);
});
cats.sort();

//vlozeni checkboxu do pripraveneho divu
jQuery.each( cats, function( index, value ) {
	ckbx.innerHTML += value+"<input type=\"checkbox\" class=\"ckbx-cat\" value=\"" + value + "\" id=\"ckbx-" + value + "\" /> ";
});

//pri kliku na jednu kategorii se odznaci Vse
$("INPUT[type='checkbox'][class='ckbx-cat']").click( function() {
	$("#all-ckbx").prop('checked', false); 
});
</script>