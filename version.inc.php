<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?

define('SYSTEM_NAME','members');
define('SYSTEM_AUTORS','Arnošt, Kenia a LuF');

function GetCodeVersion()
{
	//pro zmenu podverze staci tento soubor komitnout ;)
	$actualVersion = '$LastChangedRevision: 531 $';
	$actualVersion = explode(' ', $actualVersion);
	return "v3.0.15.$actualVersion[1] dbg";
}

function GetDevelopYears()
{
	return "2002-2018";
}

?>