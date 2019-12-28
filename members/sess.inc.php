<?

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

class sess
{
	var $logged;		// flag
	var $policy_news;
	var $policy_reg;
	var $policy_mng;
	var $policy_sadmin;
	var $policy_admin;
	var $policy_fin;
	var $user_id;		// id v "users"
	var $account_id;	// id v "accounts"
	var $cross_id;		// id v "usxus"

	function __construct()
	{
		$this->logged = 0;
	}
}
// Pro zamezeni chybovych hlasek na nekterych serverech pridano '@'.
@session_name(_CURR_SESS_ID_);
@session_start();
if (!IsSet($_SESSION['usr']))
{
	session_name(_CURR_SESS_ID_);
//	session_register("usr"); -- deprecated
	$usr = new sess; 
	$usr->logged=0;
	$usr->policy_news=0;
	$usr->policy_reg=0;
	$usr->policy_mng=0;
	$usr->policy_sadmin=0;
	$usr->policy_admin=0;
	$usr->policy_fin=0;
	$usr->user_id=0;
	$usr->account_id=0;
	$_SESSION['usr'] = $usr;
}
else
{
	$usr = $_SESSION['usr'];
}

function IsLoggedAdmin ()	// je prihlasen admin
{
	global $usr;
	return ($usr->logged && $usr->policy_admin) ? 1 : 0;
}

function IsLoggedFinance ()	// je prihlasen financnik
{
	global $usr;
	return ($usr->logged && $usr->policy_fin) ? 1 : 0;
}

function IsLoggedSmallAdmin ()	// je prihlasen maly admin
{
	global $usr;
	return ($usr->logged && $usr->policy_sadmin) ? 1 : 0;
}

function IsLoggedManager ()	// je prihlasen "trener"
{
	global $usr;
	return ($usr->logged && $usr->policy_mng == _MNG_BIG_INT_VALUE_) ? 1 : 0;
}

function IsLoggedSmallManager ()	// je prihlasen "trener" - vudce smecky
{
	global $usr;
	return ($usr->logged && $usr->policy_mng == _MNG_SMALL_INT_VALUE_) ? 1 : 0;
}

function IsLoggedRegistrator ()	// je prihlasen "prihlasovatel"
{
	global $usr;
	return ($usr->logged && $usr->policy_reg) ? 1 : 0;
}

function IsLoggedEditor ()	// je prihlasen editor novinek
{
	global $usr;
	return ($usr->logged && $usr->policy_news) ? 1 : 0;
}

function IsLoggedUser ()	// je prihlasen clen
{
	global $usr;
	return ($usr->logged && $usr->user_id > 0) ? 1 : 0;
}
function IsLogged ()	// je nekdo prihlasen
{
	global $usr;
	return ($usr->logged) ? 1 : 0;
}

function IsCalledByRegistrator ($gr_id)	// vola "prihlasovatel"
{
	return (IsLoggedRegistrator()) ? (($gr_id == _REGISTRATOR_GROUP_ID_) ? 1 : 0) : 0;
}

function IsCalledByManager ($gr_id)	// vola "trener"
{
	return (IsLoggedManager()) ? (($gr_id == _MANAGER_GROUP_ID_) ? 1 : 0) : 0;
}

function IsCalledBySmallManager ($gr_id)	// vola "trener"
{
	return (IsLoggedSmallManager()) ? (($gr_id == _SMALL_MANAGER_GROUP_ID_) ? 1 : 0) : 0;
}

function IsCalledByAdmin ($gr_id)	// vola admin
{
	return (IsLoggedAdmin()) ? (($gr_id == _ADMIN_GROUP_ID_) ? 1 : 0) : 0;
}

?>