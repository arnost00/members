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
define('_VAR_LOGIN_RETURN','mbr_return_to');
define('_SESSION_LOGIN_RETURN','login_return_to');

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

/**
 * Validate a post-login destination and return it relative to the members root.
 * Only explicitly listed display pages and their read-only parameters are accepted.
 */
function ValidateLoginReturnUrl($url)
{
	global $g_baseadr;

	$allowed_pages = array(
		'index.php' => array('id', 'subid'),
		'claim.php' => array('payment_id'),
		'us_race_regon.php' => array('id_zav', 'id_us'),
	);

	if (!is_string($url) || $url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x1F\x7F\\\\]/', $url))
		return null;

	$parts = @parse_url($url);
	if ($parts === false || isset($parts['scheme']) || isset($parts['host']) || isset($parts['user']) || isset($parts['pass']))
		return null;

	$path = $parts['path'] ?? '';
	if (substr($path, 0, 2) === '//')
		return null;

	$base_path = parse_url($g_baseadr, PHP_URL_PATH);
	$base_path = rtrim(is_string($base_path) ? $base_path : '', '/').'/';
	if (substr($path, 0, 1) === '/')
	{
		if ($base_path !== '/' && strpos($path, $base_path) !== 0)
			return null;
		$path = ($base_path === '/') ? substr($path, 1) : substr($path, strlen($base_path));
	}
	else if (strpos($path, './') === 0)
		$path = substr($path, 2);

	if ($path === '' || strpos($path, '/') !== false || strpos($path, '..') !== false || !preg_match('/^[A-Za-z0-9_]+\.php$/', $path))
		return null;

	if (!array_key_exists($path, $allowed_pages))
		return null;

	$query = $parts['query'] ?? '';
	if ($query !== '')
	{
		parse_str($query, $query_params);
		foreach (array_keys($query_params) as $query_param)
			if (!in_array($query_param, $allowed_pages[$path], true))
				return null;
	}

	return $path.(($query !== '') ? '?'.$query : '');
}

function SetLoginReturnUrl($url)
{
	$validated = ValidateLoginReturnUrl($url);
	if ($validated === null)
	{
		unset($_SESSION[_SESSION_LOGIN_RETURN]);
		return null;
	}
	$_SESSION[_SESSION_LOGIN_RETURN] = $validated;
	return $validated;
}

function GetLoginReturnUrl()
{
	if (!isset($_SESSION[_SESSION_LOGIN_RETURN]))
		return null;
	$validated = ValidateLoginReturnUrl($_SESSION[_SESSION_LOGIN_RETURN]);
	if ($validated === null)
		unset($_SESSION[_SESSION_LOGIN_RETURN]);
	return $validated;
}

function ConsumeLoginReturnUrl()
{
	$url = GetLoginReturnUrl();
	unset($_SESSION[_SESSION_LOGIN_RETURN]);
	return $url;
}

function ClearLoginReturnUrl()
{
	unset($_SESSION[_SESSION_LOGIN_RETURN]);
}

function CaptureCurrentLoginReturnUrl()
{
	if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET')
		return null;
	return SetLoginReturnUrl($_SERVER['REQUEST_URI'] ?? '');
}

function RedirectAnonymousToLogin()
{
	global $g_baseadr;
	if (IsLogged())
		return;
	if (CaptureCurrentLoginReturnUrl() === null)
	{
		header('location: '.$g_baseadr.'error.php?code=21');
		exit;
	}
	header('location: '.$g_baseadr);
	exit;
}

/**
 * Require an authenticated user with the supplied page permission.
 * Anonymous GET requests resume after login; authenticated users without the
 * permission retain the existing access-denied response.
 */
function RequirePageAccess($has_access)
{
	global $g_baseadr;
	if ($has_access)
		return;

	RedirectAnonymousToLogin();
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
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
