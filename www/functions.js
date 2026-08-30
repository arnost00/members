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
	nwin = window.open(def_race_url+id, '', 'toolbars=0, scrollbars=1, location=0, status=0, menubar=0, resizable=1, left=0, top=0, width=500, height=480');
	nwin.focus();
}

function open_url(url)
{
	window.open(url, "_self");
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
	alert("Číslo musí být kladné");
	return false;
}

function haveMoney(subject, subject_sum)
{
	num = parseInt(subject.value);
	sum = parseInt(subject_sum.value);
	if (num <= sum) return true;
	alert("Nemáte dostatek peněz pro převod.");
	return false;
}

function changeParameterValueInURL(currentUrl, parameter, value)
{
	var url = new URL(currentUrl);
	url.searchParams.set(parameter, value);
	return url.href;
}

function toggle_expand_by_group(group,el) {
    var lst = document.querySelectorAll('[data-group="' + group + '"]');
	var hidden = true;
	for(var i = 0; i < lst.length; ++i) {
		hidden = (lst[i].style.display == '');
        lst[i].style.display=hidden?'none':''
    }
	
	// toggle arrow
	const groupText = el.textContent;
    if (groupText.includes('▲') || groupText.includes('▼')) {
   		 el.textContent =
            hidden
                ? groupText.replace('▲', '▼')
                : groupText.replace('▼', '▲');
    }
}

function get_lazy_time_range_rows(el) {
    var heading = el.closest('tr[data-range-heading]');
    if (!heading || !heading.parentNode)
        return [];

    var range = el.getAttribute('data-range');
    return Array.prototype.filter.call(
        heading.parentNode.querySelectorAll('tr[data-group]'),
        function(row) { return row.getAttribute('data-group') === range; }
    );
}

function set_lazy_time_range_state(el, expanded) {
    var rows = get_lazy_time_range_rows(el);
    for (var i = 0; i < rows.length; ++i)
        rows[i].style.display = expanded ? '' : 'none';

    el.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    var arrow = el.querySelector('.time-range-arrow');
    if (arrow)
        arrow.textContent = expanded ? '▲' : '▼';
}

async function toggle_lazy_time_range(el) {
    if (el.getAttribute('data-loading') === '1')
        return;

    var expanded = el.getAttribute('aria-expanded') === 'true';
    if (expanded) {
        set_lazy_time_range_state(el, false);
        return;
    }

    if (el.getAttribute('data-loaded') === '1') {
        set_lazy_time_range_state(el, true);
        return;
    }

    var status = el.querySelector('.time-range-status');
    var url = el.getAttribute('data-expand-url');
    el.setAttribute('data-loading', '1');
    if (status) {
        status.classList.remove('error');
        status.textContent = 'Načítám…';
    }

    try {
        var response = await fetch(url, {
            credentials: 'same-origin',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        });
        if (!response.ok)
            throw new Error('HTTP '+response.status);

        var html = await response.text();
        var template = document.createElement('template');
        template.innerHTML = '<table><tbody>'+html+'</tbody></table>';
        var loadedRows = Array.prototype.slice.call(template.content.querySelectorAll('tbody > tr'));
        var heading = el.closest('tr[data-range-heading]');
        var insertionPoint = heading.nextSibling;
        for (var i = 0; i < loadedRows.length; ++i)
            heading.parentNode.insertBefore(loadedRows[i], insertionPoint);

        el.setAttribute('data-loaded', '1');
        if (status)
            status.textContent = '';
        set_lazy_time_range_state(el, true);
    } catch (error) {
        if (status) {
            status.classList.add('error');
            status.textContent = 'Načtení se nezdařilo';
        }
        set_lazy_time_range_state(el, false);
    } finally {
        el.removeAttribute('data-loading');
    }
}

function toggle_lazy_time_range_by_key(event, el) {
    if (event.key !== 'Enter' && event.key !== ' ')
        return;

    event.preventDefault();
    toggle_lazy_time_range(el);
}

// function toggleDisplayByToggleClass(cls) {
// 	let elems = document.getElementsByClassName(cls)
// 	Array.prototype.forEach.call(elems, function(el) {
// 		$( el ).toggleClass("hidden");
// 	});
// }

function toggleDisplayByData(key,value) {

	var lst = document.querySelectorAll('[' + key + '="' + value + '"]');

	for(var i = 0; i < lst.length; ++i) {
        (lst[i].style.display == '')?(lst[i].style.display='none'):(lst[i].style.display='');
	}
}

function selectAllRaceStagesForCategory(categoryInput)
{
	if (!categoryInput || categoryInput.value.trim() === '')
		return;

	var row = categoryInput.closest('tr');
	if (!row)
		return;

	var stages = row.querySelectorAll('input[type="checkbox"][name^="etapy["]');
	for (var i = 0; i < stages.length; i++)
	{
		if (stages[i].checked)
			return;
	}

	for (var i = 0; i < stages.length; i++)
		stages[i].checked = true;
}

function registerRaceCategoryStageSelection()
{
	var categoryInputs = document.querySelectorAll('input[name^="kateg["]');
	for (var i = 0; i < categoryInputs.length; i++)
	{
		categoryInputs[i].addEventListener('input', function(event) {
			selectAllRaceStagesForCategory(event.target);
		});
	}
}

