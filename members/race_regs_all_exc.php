<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?
@extract($_REQUEST);

//TBD: podpora entry_locked

require_once ("./connect.inc.php");
require_once ("./sess.inc.php");
if (!IsLoggedRegistrator() && !IsLoggedManager()&& !IsLoggedSmallManager())
{
	header("location: ".$g_baseadr."error.php?code=21");
	exit;
}

require_once ("./common.inc.php");
require_once ("./common_race.inc.php");

$gr_id = (IsSet($gr_id) && is_numeric($gr_id)) ? (int)$gr_id : 0;
$id = (IsSet($id) && is_numeric($id)) ? (int)$id : 0;

db_Connect();

$sub_query = (IsLoggedRegistrator() || IsLoggedManager()) ? '' : ' AND '.TBL_USER.'.chief_id = '.$usr->user_id.' OR '.TBL_USER.'.id = '.$usr->user_id;

$query = 'SELECT '.TBL_USER.'.id, kat, termin FROM '.TBL_USER.' LEFT JOIN '.TBL_ZAVXUS.' ON '.TBL_USER.'.id = '.TBL_ZAVXUS.'.id_user AND '.TBL_ZAVXUS.'.id_zavod='.$id.' WHERE '.TBL_USER.'.hidden = 0'.$sub_query;

@$vysledek=MySQL_Query($query);

@$vysledek_z=MySQL_Query("SELECT * FROM ".TBL_RACE." WHERE id=$id");
$zaznam_z = MySQL_Fetch_Array($vysledek_z);

$is_registrator_on = IsCalledByRegistrator($gr_id);
$is_termin_edit_on = $is_registrator_on && ($zaznam_z['prihlasky'] > 1);
$is_spol_dopr_on = ($zaznam_z["transport"]==1);

$termin = raceterms::GetCurr4RegTerm($zaznam_z);

while ($zaznamZ=MySQL_Fetch_Array($vysledek))
{
	$user=$zaznamZ["id"];
	if (IsSet($kateg[$user]))
	{
		$kat = correct_sql_string($kateg[$user]);
		$poz = correct_sql_string($pozn[$user]);
		$poz2 = correct_sql_string($pozn2[$user]);
		$cterm = $termin;
		$trans = ($is_spol_dopr_on && IsSet($transport[$user])) ? 1 : null;
		if($is_registrator_on)
		{
			if($is_termin_edit_on && $term[$user] != 0)
				$cterm = (int)$term[$user];
		}
		if ($cterm == 0)
			$cterm = 1;
		
		if ($zaznamZ['kat'] != NULL)
		{	// jiz prihlasen
			if ($kat == "")
			{	// del
//				echo "DEL";
				$result=MySQL_Query("DELETE FROM ".TBL_ZAVXUS." WHERE id_zavod = '$id' AND id_user = '$user'")
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");
			}
			else
			{	// update
//				echo "UPD";
				$kat=correct_sql_string($kat);
				$poz=correct_sql_string($poz);
				$poz2=correct_sql_string($poz2);
				$cterm=correct_sql_string($cterm);
			
				$result=MySQL_Query("UPDATE ".TBL_ZAVXUS." SET kat='$kat', pozn='$poz', pozn_in='$poz2', termin='$cterm', transport='$trans' WHERE id_zavod = '$id' AND id_user = '$user'")
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");
			}
		}
		else
		{
			if ($kat != "")
			{	// new
//				echo "NEW";
				$kat=correct_sql_string($kat);
				$poz=correct_sql_string($poz);
				$poz2=correct_sql_string($poz2);
				$cterm=correct_sql_string($cterm);
			
				$result=MySQL_Query("INSERT INTO ".TBL_ZAVXUS." (id_user, id_zavod, kat, pozn, pozn_in, termin, transport) VALUES ('$user','$id','$kat','$poz','$poz2','$cterm','$trans')")
					or die("Chyba při provádění dotazu do databáze.");
				if ($result == FALSE)
					die ("Nepodařilo se změnit přihlášku člena.");
			}
			// jinak stale neprihlasen
		}
//		echo " -".$kat." u clena ".$user.", t:".$cterm." a s pozn.: '".$poz."'<BR>";
	}
}
?>
<SCRIPT LANGUAGE="JavaScript">
<!--
	window.opener.focus();
	window.close();
//-->
</SCRIPT>
