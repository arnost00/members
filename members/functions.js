<!--
/*	members - online prihlaskovy system	*/

def_width = 400;
def_height = 400;
def_race_url = '';

function set_default_size(width, height)
{
	def_width = width;
	def_height = height;
}

function set_default_race_url(url)
{
	def_race_url = url;
}

function open_win_ex(url,win_name,width, height)
{
	nwin = window.open(url, win_name, 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width='+width+', height='+height);
	nwin.focus();
}

function open_win(url,win_name)
{
	nwin = window.open(url, win_name, 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width='+def_width+', height='+def_height);
	nwin.focus();
}

function open_win2(url,win_name)
{
	nwin = window.open(url, win_name, 'toolbars=0, scrollbars=1, location=0, status=1, menubar=1, resizable=1, left=0, top=0, width='+def_width+', height='+def_height);
	nwin.focus();
}

function open_race_info(id)
{
	nwin = window.open(def_race_url+id, '', 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width=500, height=450');
	nwin.focus();
}

function close_popup()
{
	if (window.opener)
	{
		window.opener.focus();
	}
	window.close();
}

function close_win()
{
	window.close();
}

//-->