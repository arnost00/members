<?php /* adminova stranka - editace clena */
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");

exit; // <-- temporary disabled

require("./cfg/_colors.php");
require("./cfg/_globals.php");
require ("./connect.inc.php");
require ("./sess.inc.php");
require ("./common.inc.php");
require ("./ctable.inc.php");

if (!IsLoggedRegistrator())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}
db_Connect();
include ("./header.inc.php"); // header obsahuje uvod html a konci <BODY>

DrawPageTitle('Hromadná pøihláška na závody');
?>
<TABLE width="100%" cellpadding="0" cellspacing="0" border="0">
<TR>
<TD width="2%"></TD>
<TD width="90%" ALIGN=left>
<?

// id je z tabulky "users"
@$vysledekU=MySQL_Query("SELECT id,prijmeni,jmeno FROM ".TBL_USER." WHERE id=".$id." LIMIT 1");
$zaznamU=MySQL_Fetch_Array($vysledekU)

?>
<H3>Vybraný èlen : <? echo $zaznamU["jmeno"]." ".$zaznamU["prijmeni"]; ?></H3>
<CENTER>
<?
include ("./common_race.inc.php");

@$vysledek=MySQL_Query("SELECT id,datum,datum2,nazev,misto,typ,prihlasky, prihlasky1,prihlasky2,prihlasky3,prihlasky4,prihlasky5,vicedenni FROM ".TBL_RACE." ORDER BY datum");

@$vysledek1=MySQL_Query("SELECT * FROM ".TBL_ZAVXUS." where id_user=$id");

?>
<FORM METHOD=POST ACTION="./rg_user_exc.php?id=<?echo $id;?>">
<?
$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'Datum',ALIGN_CENTER,40);
$data_tbl->set_header_col($col++,'Název'.AddPointerImg(),ALIGN_LEFT,150);
$data_tbl->set_header_col($col++,'T',ALIGN_CENTER);
$data_tbl->set_header_col($col++,'Termín p.',ALIGN_CENTER,80);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER,50);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_CENTER,50);
$data_tbl->set_header_col($col++,'Poznámka interní',ALIGN_CENTER,50);

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";



while ($zaznam1=MySQL_Fetch_Array($vysledek1))
{
	$z=$zaznam1['id_zavod'];
	$a_kat[$z]=$zaznam1['kat'];
	$a_pozn[$z]=$zaznam1['pozn'];
	$a_pozn2[$z]=$zaznam1['pozn_in'];
	$a_term[$z]=$zaznam1['termin'];
	$zaz[]=$z;
}

while ($zaznam=MySQL_Fetch_Array($vysledek))
{
	$row = array();

	$race_is_old = (GetTimeToRace($zaznam['datum']) == -1);

	$pozn = '';
	$pozn2 = '';
	if($zaznam['vicedenni'])
	{	// vicedenni
		$datum=Date2StringFT($zaznam['datum'],$zaznam['datum2']);

		$prihlasky_curr = GetActiveRaceRegDate($zaznam);
		$prihlasky=Date2String($prihlasky_curr);

		if ($race_is_old)
			$prihlasky_out = '<span class="TextAlertExpLight">'.$prihlasky.'</span>';
		else if ($prihlasky_curr != 0 && GetTimeToReg($prihlasky_curr) == -1)
			$prihlasky_out = '<span class="TextAlert">'.$prihlasky.'</span>';
		else
			$prihlasky_out = $prihlasky;

		$z=$zaznam['id'];
		if ($race_is_old)
		{
			if (IsSet($zaz) && count($zaz) > 0 && in_array($z,$zaz))
			{
				$kat = $a_kat[$z];
				$pozn = $a_pozn[$z];
				$pozn2 = $a_pozn2[$z];
			}
			else
				$kat = '-';
		}
		else if (IsSet($zaz) && count($zaz) > 0 && in_array($z,$zaz))
		{
			if($a_term[$z] != $prihlasky_curr)
			{
				$kat = $a_kat[$z].' / '.$a_term[$z];
				$pozn = $a_pozn[$z];
				$pozn2 = $a_pozn2[$z];
			}
			else
			{
				$kat = '<INPUT TYPE="text" NAME="zavod['.$z.']" value="'.$a_kat[$z].'" SIZE=5>';
				$pozn = '<INPUT TYPE="text" NAME="pozn['.$z.']" value="'.$a_pozn[$z].'" SIZE=25>';
				$pozn2 = '<INPUT TYPE="text" NAME="pozn2['.$z.']" value="'.$a_pozn2[$z].'" SIZE=25>';
			}
		}
		else
		{
			$kat = '<INPUT TYPE="text" NAME="zavod['.$z.']" SIZE=5>';
			$pozn = '<INPUT TYPE="text" NAME="pozn['.$z.']" SIZE=25>';
			$pozn2 = '<INPUT TYPE="text" NAME="pozn2['.$z.']" SIZE=25>';
		}
/*
		$kat = '-';
		$pozn = '-';
		$pozn2 = '-';
*/
	}
	else
	{	// jednodenni
		$datum=Date2String($zaznam['datum']);

		$prihlasky=Date2String($zaznam['prihlasky1']);

		if ($race_is_old)
			$prihlasky_out = '<span class="TextAlertExpLight">'.$prihlasky.'</span>';
		else if ($zaznam['prihlasky1'] != 0 && GetTimeToReg($zaznam['prihlasky1']) == -1)
			$prihlasky_out = '<span class="TextAlert">'.$prihlasky.'</span>';
		else
			$prihlasky_out = $prihlasky;

		$z=$zaznam['id'];
		if ($race_is_old)
		{
			if (IsSet($zaz) && count($zaz) > 0 && in_array($z,$zaz))
			{
				$kat = $a_kat[$z];
				$pozn = $a_pozn[$z];
				$pozn2 = $a_pozn2[$z];
			}
			else
				$kat = '-';
		}
		else if (IsSet($zaz) && count($zaz) > 0 && in_array($z,$zaz))
		{
			$kat = '<INPUT TYPE="text" NAME="zavod['.$z.']" value="'.$a_kat[$z].'" SIZE=5>';
			$pozn = '<INPUT TYPE="text" NAME="pozn['.$z.']" value="'.$a_pozn[$z].'" SIZE=25>';
			$pozn2 = '<INPUT TYPE="text" NAME="pozn2['.$z.']" value="'.$a_pozn2[$z].'" SIZE=25>';
		}
		else
		{
			$kat = '<INPUT TYPE="text" NAME="zavod['.$z.']" SIZE=5>';
			$pozn = '<INPUT TYPE="text" NAME="pozn['.$z.']" SIZE=25>';
			$pozn2 = '<INPUT TYPE="text" NAME="pozn2['.$z.']" SIZE=25>';
		}
	}
	//----------------------------


	$row[] = $datum;
	$row[] = "<A href=\"javascript:open_win('./race_info_show.php?id_zav=".$zaznam['id']."','')\" class=\"adr_name\">".$zaznam['nazev']."</A>";
	$row[] = "<A HREF=\"javascript:open_win('./race_reg_view.php?id=".$zaznam['id']."','')\">".GetRaceTypeImg($zaznam['typ']).'</A>';
	$row[] = $prihlasky_out;
	$row[] = $kat;
	$row[] = $pozn;
	$row[] = $pozn2;

	echo $data_tbl->get_new_row_arr($row)."\n";
}
echo $data_tbl->get_footer()."\n";
?>

<BR>
<INPUT TYPE="submit" value='Proved zmeny'>
</FORM>
<BR>

<BR><hr><BR>
<A HREF="index.php?id=400&subid=2">Zpìt na seznam èlenù</A><BR>
<BR><hr><BR>
</CENTER>
</TD>
<TD width="2%"></TD>
</TR>
<TR><TD COLSPAN=4 ALIGN=CENTER>
<!-- Footer Begin -->
<?include "./footer.inc.php"?>
<!-- Footer End -->
</TD></TR>
</TABLE>

</BODY>
</HTML>
