<?php
// global variables
$g_www_admin_id = 1;

define('_CURR_SESS_ID_','KeAr_SID_'.$g_shortcut.(($g_is_release) ? '' : '_Dbg'));

define('_MNG_BIG_INT_VALUE_',4);
define('_MNG_SMALL_INT_VALUE_',2);

define('_USER_GROUP_ID_',200);
define('_MANAGER_GROUP_ID_',500);
define('_SMALL_MANAGER_GROUP_ID_',600);
define('_REGISTRATOR_GROUP_ID_',400);
define('_SMALL_ADMIN_GROUP_ID_',700);
define('_ADMIN_GROUP_ID_',300);
define('_FINANCE_GROUP_ID_',800);

define('_VAR_USER_LOGIN','mbr_l_'.$g_shortcut.(($g_is_release) ? '' : '_dbg'));
define('_VAR_USER_PASS','mbr_p_'.$g_shortcut.(($g_is_release) ? '' : '_dbg'));
?>