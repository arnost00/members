<?php /* adminova stranka - editace clenu oddilu */
if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
DrawPageTitle('Členská základna - ORIS');
if ($g_external_is_connector !== 'OrisCZConnector')
	exit;
?>
<ul>Základní exporty:
	<li><a href="javascript:open_win('./export_directory_exc.php?oris=1','')">Export pro ORIS (Import členů klubu)</a><br>
	<li><a href="javascript:open_win('./export_directory_exc.php?oris=2','')">Export pro ORIS (Import kontaktních informací)</a><br>
	<li><a href="javascript:open_win('./export_directory_exc.php?oris=3','')">Export pro ORIS (Registrace)</a><br>
</ul>
<CENTER>
<?
$ORIS_year = (isset($ORIS_year) && is_numeric($ORIS_year)) ? $ORIS_year : date("Y");
require_once "./common_user.inc.php";
require_once('./csort.inc.php');

require_once('./oris_user.class.php');

$curr_url = 'index.php?id='._SMALL_ADMIN_GROUP_ID_.'&subid=2&ORIS_year=';
echo('Data registrací z ORISu jsou k roku '.$ORIS_year.' ( <a href="'.$curr_url.($ORIS_year-1).'">-1 rok</a> / <a href="'.$curr_url.($ORIS_year+1).'">+1 rok</a> )'."\n<br />");
$curr_url .= $ORIS_year;

$sc = new column_sort_db();
$sc->add_column('sort_name','');
$sc->add_column('reg','');
$sc->set_url($curr_url,true);
$sub_query = $sc->get_sql_string();

$query = "SELECT u.id,u.prijmeni,u.jmeno,u.reg,u.si_chip as si,u.hidden,u.entry_locked, a.locked, a.policy_news, a.policy_regs, a.policy_mng, a.policy_adm, a.policy_fin, a.id aid FROM ".TBL_USER." u"
	." left join ".TBL_ACCOUNT." a on a.id_users = u.id where (u.hidden = False or u.hidden is null) "
	.$sub_query;
@$vysledek=query_db($query);

if (IsSet($result) && is_numeric($result) && $result != 0)
{
	require_once('./const_strings.inc.php');
	$res_text = GetResultString($result);
	Print_Action_Result($res_text);
}

//nahraj data z orisu a vloz do mapy
function startsWith( $haystack, $needle ) {
     $length = strlen( $needle );
     return substr( $haystack, 0, $length ) === $needle;
}

$json = file_get_contents('https://oris.orientacnisporty.cz/API/?format=json&method=getRegistration&sport=1&year='.$ORIS_year);
$obj = json_decode($json);

$arr_oris = array();

foreach ($obj->Data as $key=>$value)
{
	$user = new User();
	$user->create($value->UserID, $value->FirstName, $value->LastName, $value->RegNo, $value->SI, $value->ClubID);

	$reg = $value->RegNo;
	if (startsWith($reg, $g_shortcut))
	{
		$arr_oris["user"] [$reg]= $user;
		$arr_oris["members"][$reg] = 0;
	}
}
//konec nahrani dat z orisu

if (count($arr_oris) == 0)
{
	echo('<br /><br />Pro zadaný rok se nepodařilo načíst data z ORISu<br /><br />');
}
else
{
	echo "<h3  style=\"text-align: center;\">Výpis lidí, kteří jsou v tomto systému a jejich údaje z ORISu</h3>";

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");
	$data_tbl->set_header_col_with_help($col++,'SI',ALIGN_CENTER,"SI");
//	$data_tbl->set_header_col_with_help($col++,'Zamknut',ALIGN_CENTER,"Vypni uživateli možnost se přihlásit do systému");
//	$data_tbl->set_header_col_with_help($col++,'Skrytý',ALIGN_CENTER,"Uživatel je skrytý");
//	$data_tbl->set_header_col_with_help($col++,'Přihlášky',ALIGN_CENTER,"Možnost přihlašování se člena na závody");
	$data_tbl->set_header_col_with_help($col++,' --- ',ALIGN_CENTER,"");
	$data_tbl->set_header_col_with_help($col++,' ORIS reg.č.',ALIGN_CENTER,"Načtené registrační číslo z ORISu");
	$data_tbl->set_header_col_with_help($col++,' ORIS jméno',ALIGN_CENTER,"Načtené jméno z ORISu");
	$data_tbl->set_header_col_with_help($col++,' ORIS SI',ALIGN_CENTER,"Načtené SI z ORISu");


	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo '<thead>';
	echo $data_tbl->get_header_row_with_sort()."\n";
	echo '</thead>';

	$i=1;
	while ($zaznam=mysqli_fetch_array($vysledek))
	{
		$row = array();
		$row[] = $i++;
		$row[] = $zaznam['prijmeni'];
		$row[] = $zaznam['jmeno'];
		$reg =  RegNumToStr($zaznam['reg']);;
		$row[] = $reg;
		$row[]  = $zaznam['si'];
		//4. sloupec
/*
		$acc_hidden = '<A HREF="./user_hide_exc.php?id='.$zaznam['id'].'" onclick="return confirm_hide(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Skrýt</A>';
		$acc_locked = '<A HREF="./user_lock_exc.php?id='.$zaznam['id'].'" onclick="return confirm_lock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Zamknout</A>';

		$row[] = $acc_locked;
		$row[] = $acc_hidden;
		if ($zaznam['entry_locked'] != 0)
			$row[] = '<A HREF="./user_lock2_exc.php?gr_id='._SMALL_ADMIN_GROUP_ID_.'&id='.$zaznam['id'].'" onclick="return confirm_entry_lock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')"><span class="WarningText">Odemknout</span></A>';
		else
			$row[] = '<A HREF="./user_lock2_exc.php?gr_id='._SMALL_ADMIN_GROUP_ID_.'&id='.$zaznam['id'].'" onclick="return confirm_entry_unlock(\''.$zaznam['jmeno'].' '.$zaznam['prijmeni'].'\')">Zamknout</A>';
*/
		$row[] = '';
		$fullreg = $g_shortcut.$reg;
		if (array_key_exists($fullreg, $arr_oris["user"]))
		{
			$row[] = $arr_oris["user"][$fullreg]->getReg();
			$row[] = $arr_oris["user"][$fullreg]->getLastName()." ".$arr_oris["user"][$fullreg]->getFirstName();
			$oris_si = $arr_oris["user"][$fullreg]->getSI();
			if ($oris_si != $zaznam['si']) $oris_si = "<font style='color:red'>".$oris_si."</font>";
			$row[] = $oris_si;
			$arr_oris["members"][$fullreg]=1;
		}
		else
		{
			$row[] = '';
			$row[] = '';
			$row[] = '';
		}

		echo $data_tbl->get_new_row_arr($row)."\n";
	}

	echo $data_tbl->get_footer()."\n";

	echo '<BR><hr><BR>';

	/*
	*  vypis lidi, kteri jsou v orisu a nejsou v members
	*/

	echo "<h3  style=\"text-align: center;\">Výpis lidí, kteří jsou registrovaní v ORISu a nejsou zde, podle registračky</h3>";

	$data_tbl = new html_table_mc();
	$col = 0;
	$data_tbl->set_header_col($col++,'Poř.č.',ALIGN_CENTER);
	$data_tbl->set_header_col($col++,'Příjmení',ALIGN_LEFT);
	$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
	$data_tbl->set_header_col_with_help($col++,'Reg.č.',ALIGN_CENTER,"Registrační číslo");

	echo $data_tbl->get_css()."\n";
	echo $data_tbl->get_header()."\n";

	$data_tbl->set_sort_col(1,$sc->get_col_content(0));
	$data_tbl->set_sort_col(3,$sc->get_col_content(1));
	echo '<thead>';
	echo $data_tbl->get_header_row_with_sort()."\n";
	echo '</thead>';

	$i=1;
	foreach($arr_oris["members"] as $regnum=>$members)
	{
		if ($members == 0)
		{
			$row = array();
			$row[] = $i++;
			$row[] = $arr_oris["user"][$regnum]->getLastName();
			$row[] = $arr_oris["user"][$regnum]->getFirstName();
			$row[] = $arr_oris["user"][$regnum]->getReg();
			echo $data_tbl->get_new_row_arr($row)."\n";
		}
	}


	echo $data_tbl->get_footer()."\n";

}
?>

<BR>
</CENTER>
