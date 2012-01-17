<?

if (!defined('_URL_INCLUDED')) {
	define('_URL_INCLUDED', 1);

	function cononize_url($url,$scheme = 0, $pass = 0)
	{	// scheme = 'http' || 'ftp' || ...
		$url_arr = parse_url ($url);
		$str = '';
		if ($scheme > 0)
		{
			$str .= ((IsSet($url_arr['scheme'])) ? $url_arr['scheme'] : 'http').'://';
		}
		if ($pass > 0 && IsSet($url_arr['user']) && IsSet($url_arr['pass']))
			$str .= $url_arr['user'].':'.$url_arr['pass'].'@';
		if (IsSet($url_arr['host']))
			$str .= $url_arr['host'];
		if (IsSet($url_arr['path']))
			$str .= $url_arr['path'];
		if (IsSet($url_arr['query']))
			$str .= '?'.$url_arr['query'];
		return $str;
	}

	function html_viewable_url($url)
	{
		$url = str_replace('&','&amp;',$url);
		return $url;
	}
}	// endif
?>