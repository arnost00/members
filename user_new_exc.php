<? define("__HIDE_TEST__", "_KeAr_PHP_WEB_"); ?>
<?php /* adminova stranka - provedeni vlozeni clena */
@extract($_REQUEST);

require ('./connect.inc.php');
require ('./sess.inc.php');
require ('./modify_log.inc.php');

if (IsLoggedAdmin())
{
	db_Connect();

	if (!IsSet($fin)) $fin = 0;

	// --> filling of czech sort helping column
	include "./common.inc.php";
	$name2 = $prijmeni." ".$jmeno;
	switch ($g_czech_sort)
	{
		case 1 :	// cp1250 -> iso?
			$name2 = cp2iso($name2);
			break;
		case 2 :	//	 without changes
			break;
	}
	// <-- end
	if (!IsSet($hidden)) $hidden = 0;
	$datum = String2SQLDateDMY($datum);

	if (IsSet($update))
	{
		//if ($si_chip="") {$si_chip='NULL'}
		$result=MySQL_Query("UPDATE ".TBL_USER." SET prijmeni='$prijmeni', jmeno='$jmeno', datum='$datum', adresa='$adresa', mesto='$mesto', psc='$psc', tel_domu='$domu', tel_zam='$zam', tel_mobil='$mobil', email='$email', reg='$reg', si_chip='$si' , hidden='$hidden', sort_name='$name2', poh='$poh', lic='$lic', lic_mtbo='$lic_mtbo', lic_lob='$lic_lob', fin='$fin' WHERE id='$update'")
			or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result == FALSE)
			die ("Nepodaøilo se zmìnit údaje èlena.");
		SaveItemToModifyLog_Edit(TBL_USER,$jmeno.' '.$prijmeni.' ['.$reg.']');
	}
	else
	{
		$result=MySQL_Query("INSERT INTO ".TBL_USER." (prijmeni,jmeno,datum,adresa,mesto,psc,tel_domu,tel_zam,tel_mobil,email,reg,si_chip,hidden,sort_name,poh,lic,lic_mtbo,lic_lob,fin) VALUES ('$prijmeni','$jmeno','$datum','$adresa','$mesto','$psc','$domu','$zam','$mobil','$email','$reg','$si','$hidden','$name2','$poh','$lic','$lic_mtbo','$lic_lob','$fin')")
			or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result == FALSE)
			die ("Nepodaøilo se vložit èlena.");
		SaveItemToModifyLog_Add(TBL_USER,$jmeno.' '.$prijmeni.' ['.$reg.']');
	}
	header("location: ".$g_baseadr."index.php?id=300&subid=3");
}
else if (IsLoggedManager() || IsLoggedSmallManager())
{
	db_Connect();

	if (!IsSet($fin)) $fin = 0;

	// --> filling of czech sort helping column
	include "./common.inc.php";
	$name2 = $prijmeni." ".$jmeno;
	switch ($g_czech_sort)
	{
		case 1 :	// cp1250 -> iso?
			$name2 = cp2iso($name2);
			break;
		case 2 :	//	 without changes
			break;
	}
	// <-- end
	$hidden = 0;	// unhidden users only
	$datum = String2SQLDateDMY($datum);

	if (IsSet($update))
	{
		$result=MySQL_Query("UPDATE ".TBL_USER." SET prijmeni='$prijmeni', jmeno='$jmeno', datum='$datum', adresa='$adresa', mesto='$mesto', psc='$psc', tel_domu='$domu', tel_zam='$zam', tel_mobil='$mobil', email='$email', reg='$reg', si_chip='$si' , hidden='$hidden', sort_name='$name2', poh='$poh', lic='$lic', lic_mtbo='$lic_mtbo', lic_lob='$lic_lob', fin='$fin' WHERE id='$update'")
			or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result == FALSE)
			die ("Nepodaøilo se zmìnit údaje èlena.");
		SaveItemToModifyLog_Edit(TBL_USER,$jmeno.' '.$prijmeni.' ['.$reg.']');
	}
	else
	{
		$result=MySQL_Query("INSERT INTO ".TBL_USER." (prijmeni,jmeno,datum,adresa,mesto,psc,tel_domu,tel_zam,tel_mobil,email,reg,si_chip,hidden,sort_name,poh,lic,lic_mtbo,lic_lob,fin) VALUES ('$prijmeni','$jmeno','$datum','$adresa','$mesto','$psc','$domu','$zam','$mobil','$email','$reg','$si','$hidden','$name2','$poh','$lic','$lic_mtbo','$lic_lob','$fin')")
			or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result == FALSE)
			die ("Nepodaøilo se vložit èlena.");
		SaveItemToModifyLog_Add(TBL_USER,$jmeno.' '.$prijmeni.' ['.$reg.']');
	}
	if (IsSet($update) && $update == $usr->user_id)
		header("location: ".$g_baseadr."index.php?id=200&subid=3");
	else if (IsLoggedSmallManager())
		header("location: ".$g_baseadr."index.php?id=600&subid=1");
	else
		header("location: ".$g_baseadr."index.php?id=500&subid=1");
}
else if (IsLoggedUser())
{
	if (IsSet($update) && $update == $usr->user_id)
	{
		db_Connect();

		if (!IsSet($fin)) $fin = 0;

		// --> filling of czech sort helping column
		include "./common.inc.php";
		$name2 = $prijmeni." ".$jmeno;
		switch ($g_czech_sort)
		{
			case 1 :	// cp1250 -> iso?
				$name2 = cp2iso($name2);
				break;
			case 2 :	//	 without changes
				break;
		}
		// <-- end
		$hidden = 0;	// unhidden users only
		$datum = String2SQLDateDMY($datum);
		$result=MySQL_Query("UPDATE ".TBL_USER." SET prijmeni='$prijmeni', jmeno='$jmeno', datum='$datum', adresa='$adresa', mesto='$mesto', psc='$psc', tel_domu='$domu', tel_zam='$zam', tel_mobil='$mobil', email='$email', reg='$reg', si_chip='$si' , hidden='$hidden', sort_name='$name2', poh='$poh', lic_mtbo='$lic_mtbo', lic_lob='$lic_lob', fin='$fin' WHERE id='$update'")
			or die("Chyba pøi provádìní dotazu do databáze.");
		if ($result == FALSE)
			die ("Nepodaøilo se zmìnit údaje èlena.");
		SaveItemToModifyLog_Edit(TBL_USER,$jmeno.' '.$prijmeni.' ['.$reg.']');
	}
	header("location: ".$g_baseadr."index.php?id=200&subid=3");
}
else
{
	header("location:".$g_baseadr."error.php?code=21");
	exit;
}
?>