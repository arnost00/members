<?
	$file_name = dirname(__FILE__) . '/logs/.browser_log.txt';
	$fp = fopen( $file_name, 'a');
	
	$ipa = getenv ('REMOTE_ADDR');
	$www = getenv ('HTTP_USER_AGENT');
	$dt = '['.date("c").'] ';
	$str = $dt."\t".$ipa."\t".$www."\t".gethostbyaddr($ipa)."\r\n";
	fputs( $fp, $str);
	fclose($fp);
?>