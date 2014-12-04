<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (!defined('SYSTEM_VERSION_INCLUDED'))
{
	define('SYSTEM_VERSION_INCLUDED', 1);

	define('SYSTEM_NAME','members');
	define('SYSTEM_AUTORS','Arnošt, Kenia a LuF');

	function GetCodeVersion()
	{
		//pro zmenu podverze staci tento soubor komitnout ;)
		$actualVersion = '$LastChangedRevision: 448 $';
		$actualVersion = explode(' ', $actualVersion);
		return "v2.5.4.$actualVersion[1] dbg";
	}

	function GetDevelopYears()
	{
		return "2002-2014";
	}
}
?>