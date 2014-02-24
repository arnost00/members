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

function checkAll( field, flag )
{
	var elements = document.getElementById(field).getElementsByTagName('input');
	if(!elements)
		return;

	for (i = 0; i < elements.length; i++)
	{
		if ( elements[i].type == 'checkbox' )
			elements[i].checked = flag ;
	}
}

function isValidDate(subject)
{
	// Idea for new code taken from :
	// Original JavaScript code by Chirp Internet: www.chirp.com.au
	// Please acknowledge use of this code by including this header.
 
	var minYear = 1902;

	// regular expression to match required date format
	re = /^(\d{1,2})[\- \/.](\d{1,2})[\- \/.](\d{4})$/;

	if(regs = subject.match(re))
	{
		if(regs[1] < 1 || regs[1] > 31)
			return false;
		else if(regs[2] < 1 || regs[2] > 12)
			return false;
		else if(regs[3] < minYear)
			return false;
		else
			return true;
	} 
	return false;

/*  old code ... not function for X.X.XXXX .. only for XX.XX.XXXX
  if (x =subject.match(/^(?:(0[1-9]|[12][0-9]|3[01]|?[1-9]])[\- \/.](0[1-9]|1[012]|[1-9])[\- \/.](19|20)[0-9]{2})$/)) // DD-MM-YYYY
  { 
    return true;
  }
  else
  {
    return false;
  }
*/  
}

function isValidLogin(subject)
{
  if (subject.match(/^[[a-zA-Z/._-][a-zA-Z0-9/._-]*$/)) // prvni znak neni cislo
  { 
    return true;
  }
  else
  {
    return false;
  }
}

function isPositiveNumber(subject)
{
	num = parseInt(subject.value);
	if (num > 0) return true;
	alert("��slo mus� b�t kladn�");
	return false;
}

//-->