<?
	$file_name = '.browser_log.txt';
	$fp = fopen( $file_name, 'a');
	
	$ipa = getenv ('REMOTE_ADDR');
	$www = getenv ('HTTP_USER_AGENT');
	$cd = getdate();
	$scd = $cd["mday"].".".$cd["mon"].".".$cd["year"]." - ".$cd["hours"].":".$cd["minutes"].".".$cd["seconds"];
	$str = $ipa."\t".$scd."\t".$www."\t".gethostbyaddr($ipa)."\r\n";
	fputs( $fp, $str);
	fclose($fp);
?>