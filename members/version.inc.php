<?php if (!defined("__HIDE_TEST__")) exit; /* zamezeni samostatneho vykonani */ ?>
<?
if (!defined('SYSTEM_VERSION_INCLUDED'))
{
	define('SYSTEM_VERSION_INCLUDED', 1);

	define('SYSTEM_NAME','members');
	define('SYSTEM_AUTORS','Arnošt a Kenia');

	function GetCodeVersion()
	{
		return "v2.1.0.82 dbg";
	}

	function GetDevelopYears()
	{
		return "2002-2010";
	}

}	// define (SYSTEM_VERSION_INCLUDED)
?>