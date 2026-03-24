/*
  Functions for finance tables
*/

function initCheckboxGroups(changePostprocessing = null) {

	// Handle click on "all" → toggle all "one"
	document.querySelectorAll("input[type=checkbox][data-role='all']").forEach(allBox => {
		allBox.addEventListener("click", function() {
			const checked = this.checked;

			this.closest(".checkbox-row").querySelectorAll("input[type=checkbox]")
				.forEach(box => {
					if ( box != this ) box.checked = checked;
				});
			if ( changePostprocessing ) updateRows(changePostprocessing);
		});
	});

	// Handle click on "one" → maybe update "all"
	document.querySelectorAll("input[type=checkbox][data-role='one']").forEach(oneBox => {
		oneBox.addEventListener("click", function() {
			const allBox = this.closest(".checkbox-row").querySelector("input[type=checkbox][data-role='all']");

			if (allBox) { // only if "all" exists
				if (this.checked) {
					// if all "one" are checked, "all" might be checked
					const allOnes = this.closest(".checkbox-row").querySelectorAll("input[type=checkbox][data-role='one']");
					const allChecked = Array.from(allOnes).every(cb => cb.checked);
					allBox.checked = allChecked;
				} else {
					// if one is unchecked, "all" must be unchecked
					allBox.checked = false;
				}
			}
			if ( changePostprocessing ) updateRows(changePostprocessing);
		});
	});
}

function setSelectedState(selector, state) {
    const symbols = {
        selected: "✔",
        wizard:   "🪄",
        default:  "📌"
    };

	selector.className = "state " + state;
    selector.textContent = symbols[state] ?? symbols.default;
}

function getCheckedIds (key) {

	const allCheckboxes = Array.from(document.querySelectorAll('input[type="checkbox"][data-key="' + key + '"][data-role="one"]'));
	const checkedValues = allCheckboxes.filter(cb => cb.checked).map(cb => cb.value);

	if ( checkedValues.length == 0 ) {
		return null;
	} else {
		return new Set( checkedValues );
	}

}

// modify all rows with selector, lambda function parameters are row, element with selector class and current state
function updateRowsByState(rowModifier) {
	const selectors = document.querySelectorAll('tr td .state');

	selectors.forEach(selector => {
		row = selector.closest("tr");
		const state = Array.from(selector.classList).find(c => c !== "state");
		rowModifier(row, selector, state);
	});
}

function updateRows(rowModifier) {

	// Collect all checkbox groups
	const groups = {};
	document.querySelectorAll("div.checkbox-row[data-key]").forEach(div => {
		const key = div.dataset.key; // e.g. "fintype", "kat", ...
		groups[key] = getCheckedIds(key); // Set or null
	});

	// Build selector: rows with at least one data-* attribute defined
	const selector = Object.keys(groups)
		.map(key => `[data-${key}]`)
		.join(",");

	const rows = document.querySelectorAll('tr' + selector);

	rows.forEach(row => {
		match = true;

		for (const [key, checkedTypes] of Object.entries(groups)) {
			if ( row.dataset[key] ) { // only if the row has this data-key attribute
				if ( !checkedTypes )  { match = false; break; } // no value selected for this key
				if ( !checkedTypes.has(row.dataset[key]) ) { match = false; break; } // not in the filter
			}
		}

		rowModifier(row, match);
	});
}

const financialState = new Map(); // change tracking for rollback

function addStateValue(row, colName, newVal) {
  if (!financialState.has(row)) {
    financialState.set(row, {});
  }
  const rowObj = financialState.get(row);

  rowObj[colName] = (rowObj[colName] || 0) + Number(newVal);
}

function swapStateValue(row, colName, newVal) {
  if (!financialState.has(row)) {
    financialState.set(row, {});
  }
  const rowObj = financialState.get(row);

  let prevValue = (rowObj[colName] || 0);

  // set new value
  rowObj[colName] = Number(newVal);

  // return the previous one
  return prevValue;
}

function fillFinanceRow( row, perform, values, protocol=true ) {

	// update row values
	let addNote = [];
	let addAmount = 0;
	let noteField = null;
	let amountField = null;
	let clearRegex = new Set(); // list of keys removed from note

	const colTextMap = {
		entryFee:      { label: 'startovné',   flag: 0x1 },
		transport:     { label: 'doprava',     flag: 0x2 },
		accommodation: { label: 'ubytování',   flag: 0x4 }
	};

    // collect modifiable cells and their values
    let modifiedElements = {};
	row.querySelectorAll('[data-col]').forEach(cell => {

        const colName = cell.dataset.col;
        if (colName === 'amount') amountField = cell; // remember amount cell
        if (colName === 'note') noteField = cell; // remember note cell

		// Only update if no data-fill OR data-fill == "1"
		if ( cell.dataset.fill && cell.dataset.fill != 1 ) return;

        if (values.hasOwnProperty(colName)) {
            let newVal = values[colName];   
            modifiedElements[colName] = { cell: cell, value: newVal };
        }
    });

    if ( perform === 'payrule' ) {
        // default start fee if empty
        if ( Object.hasOwn ( modifiedElements, 'entryFee' ) ) {
          if ( !modifiedElements['entryFee'].value ) {
            modifiedElements['entryFee'].value = row.dataset.startFee ?? modifiedElements['entryFee'].value;
          }
        }           
        const finType = row.dataset.fintype ?? 0;	
        if ( row.dataset.as && finType !== 0 ) { // only prihlaseni s definovanym finType
            const startTier = row.dataset.startTier ?? '';
            const regionFlag = pay['regionFlag'];
            if ( protocol ) dumpString  = 'Applying payrules on ' + row.children[1].children[0].innerText + ' for fintype=' + finType + ', startTier=' + startTier + ', regionFlag=' + regionFlag;
            for (const [financeType, terms] of Object.entries(pay['rule'])) { //loop throught finance types
                if ( financeType !== finType && financeType !== '' ) continue; // only matching type or default
                for (const [termin, data] of Object.entries(terms)) { // loop through terms
                    if ( !( !startTier || termin == '' || termin == startTier || ( termin < 0 && startTier >= -termin ) ) ) continue; // only matching tier or default
                    // Loop through all triplets [zebricek, platba, typ_platby]
                    for (const payrule of data) {
                        if ( protocol ) dumpString += `\n  → zebříček=${payrule.zebricek}, platba=${payrule.platba}, typ_platby=${payrule.druh}, uctovano=${payrule.uctovano}, id=${payrule.id}`;
                        if ( payrule.zebricek && !(regionFlag & payrule.zebricek) ) continue; // skip defined unmatching zebricek
                        // match rebricek/region
                        if ( protocol ) dumpString += '  ✔ matched';
                        for (const [colName, element] of Object.entries(modifiedElements)) {
                            if ( ! Object.hasOwn(colTextMap, colName) ) continue; // only defined columns for payrules
                            let newVal = element.value;
                            let valueSet = false; // whether value was set by any rule

                            if ( payrule.uctovano && !(payrule.uctovano & colTextMap[colName].flag ) ) continue; // not to be charged
                            switch ( payrule.druh ) {
                                case 'R' :
                                    if ( colName === 'entryFee' ) {
                                        const cat = row.dataset.cat;
                                        // Kategorie nenalezena v platbach, nepocitat
                                        if (!pay?.startFee?.[cat]?.[1]) {
                                            if ( protocol ) dumpString += ` → do not calculate entryFee difference, category ${cat} not found`;
                                            break;
                                        }
                                        // rozdíl v ceně startovného
                                        if ( protocol ) dumpString += ` → calculating entryFee difference from ${pay['startFee'][cat][1]} to ${newVal}`;
                                        newVal = ( newVal - (pay['startFee'][cat][1] ?? newVal) );
                                        if ( newVal < 0 ) newVal = 0;
                                    }
                                case 'C' :
                                    newVal = Math.floor(newVal * payrule.platba / 100);
                                    valueSet = true;
                                    break;
                                case 'P' :
                                    newVal = payrule.platba;
                                    valueSet = true;
                                    break;
                                default:
                                    ;
                            }
                            if ( valueSet ) {
                                element.rulevalue = newVal;
                                if ( protocol )  dumpString += ` → evaluating ${newVal} for ${colName}`;
                            }
                        }
                    }
                }
            }
            for (const [colName, element] of Object.entries(modifiedElements)) {
                if ( ! Object.hasOwn(colTextMap, colName) ) continue; // only defined columns for payrules
                if ( Object.hasOwn(element, 'rulevalue') ) {
                    element.value = element.rulevalue;
                    delete element.rulevalue;
                    if ( protocol )  dumpString += ` → setting ${colName} to ${element.value}`;
                } else {
                    element.value = null; // not set by any rule → null
                    if ( protocol )  dumpString += ` → removing value for ${colName}`;
                }
            }
            if ( protocol ) console.log(dumpString);
        }
    }

    for (const [colName, element] of Object.entries(modifiedElements)) {
        const cell = element.cell;
        const newVal = element.value;

        let addVal = ''; // value in add format
        if ( newVal == null ) addVal = '';
        else if (!isNaN(newVal)) addVal = (Number(newVal) >= 0 ? "+" : "") + Number(newVal);
        else addVal = '/' + newVal;


        switch (perform) {
            case 'overwrite': // přepiš
                if ( newVal !== '' ) { // null or set							
                    if (cell.tagName === "INPUT" || cell.tagName === "TEXTAREA") {
                        cell.value = newVal;
                    } else {
                        if ( cell.dataset.fill ) {
                            cell.innerHTML = '✔<B>' + addVal + '</B>';
                        } else {
                            cell.innerHTML = '<B>' + newVal + '</B>';
                        }
                    }
                    if ( colTextMap[colName]) {						
                        if ( newVal ) {
                            // effective value
                            addNote.push(addVal + ' ' + colTextMap[colName].label);
                            addAmount += Number(newVal); // add new
                        }
                        addAmount -= swapStateValue ( row, colName, newVal ); // remove old and save new
                        clearRegex.add(colTextMap[colName].label); // mark remove from note
                    }
                }
                break;

            case 'insert': // vlož
            case 'payrule': // vlož podle pravidel
                if ( newVal ) {
                    let wasEmpty = false;
                    if (cell.tagName === "INPUT" || cell.tagName === "TEXTAREA") {
                        if ( !cell.value.trim() ) {
                            wasEmpty = true;
                            cell.value = newVal;
                        }								
                    } else {
                        if ( cell.dataset.fill ) {
                            if ( cell.textContent === '✔' ) {
                                wasEmpty = true;
                                cell.innerHTML = '✔<B>' + addVal + '</B>';
                            }
                        } else {
                            if ( !cell.textContent.trim() ) {
                                wasEmpty = true;
                                cell.innerHTML = '<B>' + newVal + '</B>';
                            }
                        }
                    }
                    if (wasEmpty && colTextMap[colName]) {
                        addNote.push(addVal + ' ' + colTextMap[colName].label);
                        addStateValue(row, colName, newVal); // save new
                        addAmount += Number(newVal); // add new
                        if ( perform === 'payrule' ) {
                            setSelectedState(row.querySelector('td .state'), 'wizard');
                        }
                    }
                }
                break;

            case 'add': // přidej
                if ( newVal ) {
                    if (cell.tagName === "INPUT" || cell.tagName === "TEXTAREA") {
                        if (colName === 'amount') {
                            if ( !isNaN ( cell.value ) ) cell.value = Number(cell.value) + Number ( newVal );
                        } else {
                            if (!cell.value.trim()) cell.value = newVal; else cell.value += addVal;
                        }
                    } else {
                        if (!cell.textContent.trim()) cell.innerHTML = '<B>' + newVal + '</B>'; else cell.innerHTML += '<B>' + addVal + '</B>';
                    }
                    if (colTextMap[colName]) {
                        addNote.push(addVal  + ' ' + colTextMap[colName].label);
                        addStateValue ( row, colName, newVal); // save added
                        addAmount += Number(newVal); // add added
                    }
                }
                break;

            default:
            // do nothing
        }
    }

	if ( noteField && clearRegex.size > 0 ) {
		let noteText = noteField.value;
		for (const value of clearRegex ) {
			const regex = new RegExp('[+-]?\\d+\\s*' + value, "g");
			noteText = noteText.replaceAll( regex, '' );
		}
		noteField.value = noteText;
	}
	if (noteField && addNote.length > 0) {
		noteField.value = noteField.value + addNote.join('');
	}
	if (amountField && addAmount) {
		amountField.value = Number(amountField.value) + addAmount;
	}

}

function fillTableFromInput(perform, event) {
	// prevent form submission
	event.preventDefault();

	// collect input values
	const values = {};
	document.querySelectorAll(".form-row [id^='in-']")
		.forEach(input => {
			const key = input.id.substring(3); // remove "in-"
			if (key.endsWith("-null")) {
				if (input.classList.contains("pinned")) {
					// null marker → set corresponding value to null
					const baseKey = key.slice(0, -5); // cut off "-null"
					values[baseKey] = null;
				}
			} else {
				// only set if not already set (to avoid overwriting null)
				if (!(key in values)) {
					values[key] = input.value;
				}
			}
		}
	);

	document.querySelectorAll("tr td .state")
		.forEach(input => {
			if (input.classList.contains("selected") || input.classList.contains("pinned") || perform === 'payrule') {
				row = input.closest("tr");
				fillFinanceRow(row,perform,values);
			}
		});
};
