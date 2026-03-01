

<link rel="stylesheet" type="text/css" href="css_finance.css" media="screen" />
<?php /* finance -  show exact race finance */

$query_all = "SELECT 
    u.id AS u_id, u.sort_name, u.reg, u.finance_type,
	f.id, f.amount, f.note,
	zu.id AS zu_id, zu.kat, zu.termin, zu.transport, zu.ubytovani, zu.participated, zu.add_by_fin
FROM ".TBL_USER." u
LEFT JOIN ".TBL_ZAVXUS." zu
       ON u.id = zu.id_user 
      AND zu.id_zavod = $race_id
LEFT JOIN ".TBL_FINANCE." f
       ON f.id_users_user = u.id
      AND f.id_zavod = $race_id
      AND f.storno IS NULL
WHERE u.hidden = '0'
ORDER BY zu_id is not null DESC, f.id is not null DESC, u.sort_name ASC";
@$vysledek_all=query_db($query_all);
if ($vysledek_all === FALSE )
{
	die('Chyba v databázi, kontaktuje administrátora.<br>');
}

//vytazeni informaci o zavode
DrawPageSubTitle('Vybraný závod');

@$vysledek_z=query_db('SELECT * FROM '.TBL_RACE." WHERE `id`='$race_id' LIMIT 1");
$zaznam_z = mysqli_fetch_array($vysledek_z);

$zebricek = ( $zaznam_z['zebricek'] ?? 0 );

//payement rules ordered for iteration default last, exact term first
$pay = [];
$sql = 'SELECT * FROM '.TBL_PAYRULES." WHERE (`typ`='" . $zaznam_z['typ'] .
 "' OR `typ` is null ) and ( `typ0`='" . $zaznam_z['typ0'] . "' or `typ0` is null)" .
" ORDER BY finance_type DESC"; // nulls last
 @$vysledek_pay=query_db($sql);

if ($vysledek_pay && $vysledek_pay->num_rows > 0) {
    while ($row = $vysledek_pay->fetch_assoc()) {

		$financeType = $row['finance_type'];
        $termin      = $row['termin'] ?? '';

        // Ensure nested arrays are initialized
        if (!isset($pay['rule'][$financeType])) {
            $pay['rule'][$financeType] = [];
        }

		if (!isset($pay['rule'][$financeType][$termin])) {
			$pay['rule'][$financeType][$termin] = [];
		}

		// Append a new triplet [zebricek, platba, druh_platby]
		$pay['rule'][$financeType][$termin][] = [
			'zebricek'   => isset($row['zebricek']) ? (int)$row['zebricek'] : null,
			'platba'     => isset($row['platba']) ? (int)$row['platba'] : null,
			'druh'       => $row['druh_platby'] ?? null,
			'uctovano'   => isset($row['uctovano']) ? (int)$row['uctovano'] : null,
			'id'   		 => isset($row['id']) ? (int)$row['id'] : null,
		];		
    }
}

// sort termin positive, negative and empty e.g. 1,2,3,-1,-2,''
foreach ($pay['rule'] as &$rulesByFinanceType) {
    uksort($rulesByFinanceType, function($a, $b) {
        // Handle empty values last
        if ($a === '' && $b !== '') return 1;
        if ($b === '' && $a !== '') return -1;

        // Convert to integers for numeric comparison
        $aInt = (int)$a;
        $bInt = (int)$b;

        // Positive values first, sorted ascending
        if ($aInt >= 0 && $bInt < 0) return -1;
        if ($aInt < 0 && $bInt >= 0) return 1;

        // Both positive → ascending
        if ($aInt > 0 && $bInt > 0) return $aInt <=> $bInt;

        // Both negative → descending
        if ($aInt < 0 && $bInt < 0) return $bInt <=> $aInt;

        // Fallback (e.g., both empty strings)
        return 0;
    });
}
unset($rulesByFinanceType);

$vysledek_pay?->free();

require_once ("./url.inc.php");
require_once ("./common_race.inc.php");

RaceInfoTable($zaznam_z,'',false,false,true);

require_once ('./url.inc.php');

// informace z is
require_once ("./connectors.php");
// need to be defined for all races

$ext_id = $zaznam_z['ext_id'];
$connector = ConnectorFactory::create();

// definuj velikost oblasti podle mista klubu
$raceInClubRegions = 0x0; // no matching regions

if ( !empty ( $ext_id ) && $connector!== null ) {

    // Get race info by race ID
	$raceInfo = $connector->getRaceInfo($ext_id);

	// Get race payement by race ID
	$racePayement = $connector->getRacePayement($ext_id);
    if ( $racePayement == null ) {
		$racePayement = new RacePayement(0);
		echo " \u{26A0} neplatné ID závodu";
	} else {

		// Sort categories alphabetically
		ksort($racePayement->overview->categories);

		// // Add collapsible section for category counts with table formatting
		// echo '<br><br><div id="category_details" style="display:none;">';
		echo '<table cellspacing="5">';
		echo '<tr><th style="text-align:left;">Kategorie</th>';

		foreach ($racePayement->overview->categories as $category => $fees) {
			echo "<td>$category</td>";
		}

		echo '</tr><tr><th style="text-align:left;">Platba</th>';

		foreach ($racePayement->overview->categories as $category => $fees) {
			echo "<td style='text-align:center;'>";
//			foreach ( $racePayement->overview->feeTiers as $tier => $exist ) {
			for ( $tier = 1; $tier <= 3; $tier++ ) {
				// If not set, initialize from raceInfo (if available), otherwise null
				if ( !isset($fees[$tier])) {

					switch ( $tier ) {
						case 1 :
							$fees[$tier] = $raceInfo->startovne[$category] ?? null;
							break;
						case 2 :
							if ( $raceInfo->koeficient1 > 0 ) {
								$fees[2] = $fees[1] + $fees[1] * $raceInfo->koeficient1 / 100;
							} else { 
								echo '&nbsp<br/>';
								continue 2;
							}
							break;
						case 3 :
							if ( $raceInfo->koeficient2 > 0 ) {
								$fees[3] = $fees[1] +$fees[1] * $raceInfo->koeficient2 / 100;
							} else { 
								echo '&nbsp<br/>';
								continue 2;
							}
							break;
						default :
							$fees[$tier] = null;
					}
				}

				echo ($fees[$tier] ?? '-') . "<br/>";
			}
			echo "</td>";

			// for javascript rules processing
			$pay['startFee'][$category] = $fees; // default fee for category
		}

		echo "</tr></table>";

		if (!empty($racePayement->overview->services)) {

			echo '<table cellspacing="5">';
			echo '<tr><th style="text-align:left;">Služba</th>';

			foreach ($racePayement->overview->services as $name => $fees) {
				for ($i = 0; $i < count($fees); $i++) {
					echo "<td>$name</td>";
				}
			}

			echo '</tr><tr><th style="text-align:left;">Cena</th>';

			foreach ($racePayement->overview->services as $name => $fees) {
				foreach($fees as $fee => $count) {
					echo "<td>", $fee * $count, "</td>";
				}
			}
			echo '</tr><tr><th style="text-align:left;">Počet</th>';
			foreach ($racePayement->overview->services as $name => $fees) {
				foreach ($fees as $fee => $count) {
					echo "<td>$count</td>";
				}
			}
			echo '</tr><tr><th style="text-align:left;">Jednotková cena</th>';
			foreach ($racePayement->overview->services as $name => $fees) {
				foreach ($fees as $fee => $count) {
					echo "<td>$fee</td>";
				}
			}

			echo "</tr></table>";
		}

		if (  isset($raceInfo->oblasti) && count($raceInfo->oblasti) > 0 ) {
			if ( isset ($g_external_is_region_A ) && in_array($g_external_is_region_A, $raceInfo->oblasti)) {
				$raceInClubRegions |= 0x01; // republika
			}
			if ( isset ($g_external_is_region_B ) && in_array($g_external_is_region_B, $raceInfo->oblasti)) {
				$raceInClubRegions |= 0x06; // cechy nebo morava
			}
			if ( isset ($g_external_is_region_C ) && in_array($g_external_is_region_C, $raceInfo->oblasti)) {
				$raceInClubRegions |= 0x08; // mistni liga
			}
		}
	}
} else {
	$raceInfo = new RaceInfo(0);
	$racePayement = new RacePayement(0);	
}
$pay['regionFlag'] = $raceInClubRegions;

function getOrisFee($reg): array {
    global $g_shortcut, $racePayement;

    $key = $g_shortcut . RegNumToStr($reg);

    if (isset($racePayement->participants[$key])) {
        $participant = $racePayement->participants[$key];
        return [
            'fee'  => $participant->fee,
            'tier' => $participant->feeTier
        ];
	}

    return ['fee' => '', 'tier' => 0];
}

function renderOrisFee(array $feeData): string {
    global $connector;

	if ( empty($feeData['fee'])) {
        return '';
	}

	$tierSuffix = '';
	$title = '';
	if  ( $feeData['tier'] > 1 ) {
		// later tier
		$tierSuffix = $feeData['tier'];
		$title = ' v ' . $tierSuffix . '. termínu';
	}
	if ( !empty($feeData['membersonly']) ) {
		// only in members, not in oris
		$tierSuffix .= 'P';
		$title = ' title="Jen v members ' . $title . ', ne v přihláškách v ' . $connector->getSystemName(). '"';
    } else {
		if  ( $feeData['tier'] > 1 ) {
			// later tier
			$title = ' title="Závodník přihlášen ' . $title . '"';
		}
	}

    if (!empty($tierSuffix) ) {
        return '<span class="TextAlert"' . $title . '>' . $feeData['fee'] . '/' . $tierSuffix . '</span>';
    }

    return $feeData['fee'];
}

function getOrisClass($reg) : string {
	global $g_shortcut;
	global $racePayement;
	$key = $g_shortcut . RegNumToStr($reg);
	return $racePayement->participants[$key]->classDesc ?? '';
}

require_once ('./common_fin.inc.php');
$enable_fin_types = IsFinanceTypeTblFilled();

$checkBoxRows = []; // rows of check boxes
$checkBoxRows['cat'] = new CheckboxRow ( 'Kategorie', 'cat' );
$checkBoxRows['as'] = $cbu = new CheckboxRow ( 'Účastník', 'as', false );
$cbu->addEntry('Přihlášen', 'Závodník byl přihlášen do závodu', null, true, true);
$cbu->addEntry('Neřihlášen s platbami', 'Závodník byl přidán do závodu', null, true, true);
$cbu->addEntry('Ostatní', 'Závodník nebyl přihlášen do závodu', null, false, true);
$checkBoxRows['transport'] = $cbt = new CheckboxRow ( 'Doprava', 'transport', false );
$cbt->addEntry('Ano', 'Závodník využil dopravu', 1, true, true);
$cbt->addEntry('Ne', 'Závodník nevyužil dopravu', 0, true, true);
$checkBoxRows['accommodation'] = $cba = new CheckboxRow ( 'Ubytování', 'accommodation', false );
$cba->addEntry('Ano', 'Závodník využil ubytování', 1, true, true);
$cba->addEntry('Ne', 'Závodník nevyužil ubytování', 0, true, true);
$checkBoxRows['participated'] = $cbp = new CheckboxRow ( 'Přidán v účasti', 'participated', false );
$cbp->addEntry('Ano', 'Závodník byl přidán', 1, true, true);
$cbp->addEntry('Ne', 'Závodník nebyl přidán', 0, true, true);
$checkBoxRows['addByFin'] = $cbabf = new CheckboxRow ( 'Přidán ve financích', 'addByFin', false );
$cbabf->addEntry('Ano', 'Závodník byl přidán', 1, true, true);
$cbabf->addEntry('Ne', 'Závodník nebyl přidán', 0, true, true);


?>
<div class="update-categories">
<div class="sub-title">Naplní pole pro označené závodníky <span class="state selected">✔</span>,<span class="state pinned">📌</span>. Automatické vyplnění 🪄 vybere závodníky samočinně.</div>
<div class="checkbox-row" data-key="cat"></div>
<div class="checkbox-row" data-key="fintype"></div>
<div class="checkbox-row">
<div class="checkbox-row" data-key="as"></div>
<span style="width: 2em;">&nbsp;</span>
<div class="checkbox-row" data-key="transport"></div>
<span style="width: 2em;">&nbsp;</span>
<div class="checkbox-row" data-key="accommodation"></div>
<span style="width: 2em;">&nbsp;</span>
<div class="checkbox-row" data-key="participated"></div>
<span style="width: 2em;">&nbsp;</span>
<div class="checkbox-row" data-key="addByFin"></div>
</div>

<?php
if ($enable_fin_types) {

	// create checkbox definition with lookup

	$query = "SELECT * FROM ".TBL_FINANCE_TYPES.' ORDER BY id';
	@$fintypes=query_db($query);

	if ($fintypes === FALSE ) {}
	else {

		$cbr = new CheckboxRow ( 'Typ o.p.', 'fintype' );
		$cbr->addEntry('-', 'Není definováno', 0, true, false); // null value represented by -

		while ($zaznam=mysqli_fetch_array($fintypes))
		{
			$cbr->addEntry($zaznam['nazev'],$zaznam['popis'],$zaznam['id'],true,false);
		}
		$checkBoxRows['fintype'] = $cbr;
	}
}

/**
 * Render a form field with optional attributes and types.
 *
 * @param string $column The id/column name of the input
 * @param string $label  The visible label text
 * @param string $type   Input type (text, number, etc.)
 * @param string $options Optional additional HTML attributes
 */
function renderFormField(string $column, string $label, string $type = 'text', string $options = ''): string {
    return '<div class="form-field">'
         . '<label for="in-' . htmlspecialchars($column) . '">' . htmlspecialchars($label) . '</label>'
         . '<div><input type="' . htmlspecialchars($type) . '" id="in-' . htmlspecialchars($column) . '" ' . $options . ' />'
		 . '&nbsp;<span class="state unpinned" id="in-' . htmlspecialchars($column) . '-null" title="Vymazat hodnotu">✖</span></div>'
         . '</div>';
}

?>
<div class="form-row">
<?php
  echo renderFormField ( 'amount', 'Částka', 'number', 'size="6"');
  echo renderFormField ( 'note', 'Poznámka', 'text');
  echo renderFormField ( 'entryFee', 'Startovné', 'text', 'size="3" inputmode="numeric" pattern="\d*"');
  echo renderFormField ( 'transport', 'Doprava', 'text', 'size="3" inputmode="numeric" pattern="\d*"');
  echo renderFormField ( 'accommodation', 'Ubytování', 'text', 'size="3" inputmode="numeric" pattern="\d*"');
?>
  <div class="form-field">
	&nbsp;<br/>
	<button onclick="fillTableFromInput('overwrite',event)" title="Vložení hodnot do vybraných řádků">🔁</button><br/>
  </div>
  <div class="form-field">
	&nbsp;<br/>
	<button onclick="fillTableFromInput('insert',event)" title="Vložení hodnot pokud není vyplněna částka">📥</button><br/>
  </div>
  <div class="form-field">
	&nbsp;<br/>
	<button onclick="fillTableFromInput('add',event)" title="Přičtení hodnot, poznámky odděleny /">➕</button><br/>
  </div>
<div class="form-field">
	&nbsp;<br/>
	<button onclick="fillTableFromInput('payrule',event)" title="Vyplň platby podle pravidel">🪄</button>
  </div>  
	<span id="wizardInfoBtn"
		title="Wizard naplní prázdná pole podle pravidel u typu příspěvku. Kliknutím na toto &#9432; se vygeneruje tato stránka i s objasněním uplatněných pravidel u jmen závodníků."
		style="cursor:pointer; margin-top:6px;font-size:1.1em;"
	>&#9432; <!-- (i) info icon --></span>
<div class="form-field" style="margin-left: 10em">
	&nbsp;<br/><button 
	onclick="updateRowsByState((row, marker, state) => {
		if (state === 'selected') setSelectedState ( marker, 'pinned'); })" 
	title="Připnutí vybraných řádků" style="white-space: nowrap">✔=&gt;<span class="state pinned">📌</span></button>
  </div>
<div class="form-field">
   &nbsp;<br/><button 	onclick="updateRowsByState((row, marker, state) => {
		if (state !== 'unpinned') setSelectedState ( marker, 'unpinned'); })"
	 title="Odepnout všechny řádky" style="white-space: nowrap">*=&gt;<span class="state unpinned">📌</span></button><br/>
</div>
</div>
</div>

<script>
const pay = <?php echo json_encode($pay, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>;

function markSelected (row, match) {
  const span = row.querySelector("td .state");
  if (!span) return;

  // if not pinned, force state = selected/unpinned
  if (!span.classList.contains("pinned")) {
	setSelectedState ( span, match ? "selected" : "unpinned" );
  }
};

</script>

<? 

echo "<form method=\"post\" action=\"?payment=pay&race_id=$race_id\">";

DrawPageSubTitle('Závodníci v závodě');
$data_tbl = new html_table_mc();
$col = 0;
$data_tbl->set_header_col($col++,'&nbsp;',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Jméno',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Částka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Poznámka',ALIGN_LEFT);
$data_tbl->set_header_col($col++,'Kategorie',ALIGN_CENTER);
if ($enable_fin_types)
	$data_tbl->set_header_col_with_help($col++,'Typ o.p.',ALIGN_CENTER,'Typ oddílových příspěvků');
$data_tbl->set_header_col($col++,'Možnosti',ALIGN_CENTER);
if ( !empty ( $ext_id ) && $connector!== null ) {
	$data_tbl->set_header_col_with_help($col++,'Oris',ALIGN_LEFT,'Platba z orisu/termín');
}
$data_tbl->set_header_col_with_help($col++,'Sta.',ALIGN_LEFT,'Startovné');
if ($g_enable_race_transport)
	$data_tbl->set_header_col_with_help($col++,'Dop.',ALIGN_CENTER,'Společná doprava');
if ($g_enable_race_accommodation)
	$data_tbl->set_header_col_with_help($col++,'Ubyt.',ALIGN_CENTER, 'Společné ubytování');
$data_tbl->set_header_col_with_help($col++,'Účast',ALIGN_CENTER, 'A = účast, F = přidán');

echo $data_tbl->get_css()."\n";
echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$sum_plus_amount = 0;
$sum_minus_amount = 0;
$i = 1;

$zaznam=null; // inicializace for hand over between loops

echo $data_tbl->get_subheader_row("Přihlášení")."\n";

while ($zaznam=mysqli_fetch_assoc($vysledek_all))
{
	if ( !isset($zaznam['zu_id'])) {
		break; // 
	}

	$kat = $zaznam['kat'];
	if ( empty ($kat ) && !empty ( $ext_id ) && $connector!== null ) {
		$kat = getOrisClass($zaznam['reg']);
	}

	$kat_id = $checkBoxRows['cat']->addEntry($kat,null,$kat,false,true);
	if ( !empty($kat_id) ) {
		$attrs['data-cat'] = $kat_id;
	}
	
	$id = $zaznam['id'];

	$regFees = null;
	if ( !empty ( $ext_id ) && $connector!== null ) {
		// startovne z Orisu
		$regFees = getOrisFee($zaznam['reg']);
		if ( ( !isset ( $regFees ) || !$regFees['fee'] ) && 
			isset($zaznam['kat']) && isset($raceInfo->startovne[$zaznam['kat']]) ) {
			// z definice zavodu, jen pokud ma startujici kategorii				
			$regFees['membersonly'] = true;
			$regFees['fee'] = $raceInfo->startovne[$zaznam['kat']];
			$regFees['tier'] = $zaznam['termin'];
			if ( $zaznam['termin'] == 2 && $raceInfo->koeficient1 > 0 ) {
				$regFees['fee'] += $regFees['fee'] * $raceInfo->koeficient1 / 100;
			} else if ( $zaznam['termin'] == 3 && $raceInfo->koeficient2 > 0 ) {
				$regFees['fee'] += $regFees['fee'] * $raceInfo->koeficient2 / 100;
			}
		}
	}		

	$row = array();
	$row[] = '<span class="state unpinned">📌</span>';
	$row[] = "<A href=\"javascript:open_win_ex('./view_address.php?id=".$zaznam["u_id"]."','',500,540)\" class=\"adr_name\">".$zaznam['sort_name']."</A>";
	
	$amount = $zaznam['amount'];
	$amount>0?$sum_plus_amount+=$amount:$sum_minus_amount+=$amount;
	
	$input_amount = '<input class="amount" type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" size="5" maxlength="10" data-col="amount" data-init="'.$amount.'" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input class="note" type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" size="40" maxlength="200" data-col="note" data-init="'.$note.'" />';
	$row[] = $input_note;

	$row[] = '<input type="text" class="cat" id="cat'.$i.'" name="cat'.$i.'" size="6" maxlength="10" value="'.$kat.'" />';
	if ($enable_fin_types) {
		$fintype = $zaznam['finance_type'] ?? '0'; 
		$row[] = $checkBoxRows['fintype']->getLabel($fintype) ?? '-';
	}

	$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['u_id'].'\',\'\')">Platby</A>';
	$row_text .= '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>'; 
	$row[] = $row_text;

	if ( isset ( $regFees ) ) {
		// render startovne z Orisu
		$row[] = renderOrisFee($regFees);
	}

	// startovne
	$row[] = '<span data-col="entryFee" data-init="0"></span>';

	if ($g_enable_race_transport)
	{
		$trans=$zaznam['transport']==1?"&#x2714;":"&nbsp;";
		$row[] = "<span data-col='transport' data-init='0' data-fill='".(($zaznam['transport']==1)?"1":"0")."'>".$trans."</span>";
	}
	if ($g_enable_race_accommodation)
	{
		$ubyt=$zaznam['ubytovani']==1?"&#x2714;":"&nbsp;";
		$row[] = "<span data-col='accommodation' data-init='0' data-fill='".(($zaznam['ubytovani']==1)?"1":"0")."'>".$ubyt."</span>";
	}
	$row[] = ($zaznam['participated'] ? 'A' : '').($zaznam['add_by_fin'] ? 'F' : '');

	$attrs = [ 'class' => 'cat', 'data-cat' => $kat_id, 
	    'data-participated' => $zaznam['participated']??0,
		'data-addByFin' => $zaznam['add_by_fin']??0,
		'data-fintype' => $zaznam['finance_type']??0,
		'data-transport' => $zaznam['transport']??0,
		'data-accommodation' => $zaznam['ubytovani']??0,
		'data-as' => '0' ]; // participant

	// financial wizard attributes
	if ( isset ( $regFees ) && $regFees['fee'] ) {
		$attrs['data-start-fee'] = $regFees['fee'];
		$attrs['data-start-tier'] = $regFees['tier'];
		if ( !empty($regFees['membersonly']) ) {
			$attrs['data-start-members'] = 1;
		}
	}

	echo $data_tbl->get_new_row_arr($row, $attrs)."\n";
	$i++;
}
if ($i == 1)
{	// zadny zavodnik prihlasen
	echo $data_tbl->get_info_row('Není nikdo přihlášen.')."\n";
}
$i0 = $i;
//---------------------------------------------------
echo $data_tbl->get_subheader_row("Nepřihlášení s platbami")."\n";
do  {

	if( $zaznam === null || !isset($zaznam['id']) ) {
		break; // no more records or no payment
	}

	$attrs = ['data-fintype' => $zaznam['finance_type']??0,
		'data-as' => '1',
		'data-transport' => 0,
		'data-accommodation' => 0 ]
		; // other payer

	$kat = '';
	if ( !empty ( $ext_id ) && $connector!== null ) {
		$kat = getOrisClass($zaznam['reg']);
		$kat_id = $checkBoxRows['cat']->addEntry($kat,null,$kat,false,true);
		$attrs['data-cat'] = $kat_id;
	}

	$id = $zaznam['u_id'];
	
	$row = array();
	$row[] = '<span class="state unpinned">📌</span>';
	$row[] = "<A href=\"javascript:open_win('./view_address.php?id=".$zaznam["u_id"]."','')\" class=\"adr_name\">".$zaznam['sort_name']."</A>";

	$amount = $zaznam['amount'];
	$amount>0?$sum_plus_amount+=$amount:$sum_minus_amount+=$amount;

	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" size="5" maxlength="10" data-col="amount" data-init="'.$amount.'" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" size="40" maxlength="200" data-col="note" data-init="'.$note.'" />';
	$row[] = $input_note;

	$row[] = $kat;

	if ($enable_fin_types) {
		$fintype = $zaznam['finance_type'] ?? 0; 
		$row[] = $checkBoxRows['fintype']->getLabel($fintype) ?? '-';
	}
	
	$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['u_id'].'\',\'\')">Platby</A>';
	$row_text .= '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	$row[] = $row_text;

	$regFees = null;
	if ( !empty ( $ext_id ) && $connector!== null ) {
		// startovne z Orisu
		$regFees = getOrisFee($zaznam['reg']);
		$row[] = renderOrisFee( $regFees );
	}

	// startovne
	$row[] = '<span data-col="entryFee" data-init="0"></span>';	

	// financial wizard attributes
	if ( isset ( $regFees ) && $regFees['fee'] ) {
		$attrs['data-start-fee'] = $regFees['fee'];
		$attrs['data-start-tier'] = $regFees['tier'];
	}

	echo $data_tbl->get_new_row_arr($row, $attrs)."\n";

	$i++;
} while ($zaznam=mysqli_fetch_assoc($vysledek_all) );


if (($i - $i0) == 0)
{	// zadny zavodnik s vkladem
	echo $data_tbl->get_info_row('Není nikdo jen s platbou.')."\n";
}

echo $data_tbl->get_footer()."\n";

echo "<div style=\"text-align:right; margin-right:3%\"><b><font>Částka celkem: ".($sum_minus_amount+$sum_plus_amount)."</font></b> <font size=-5> | plus: ".$sum_plus_amount." | mínus: ".$sum_minus_amount."</font></div>";

echo '<br><input type="submit" value="Změnit platby"/>';
echo '</form>';

echo "<form method=\"post\" action=\"?payment=pay&race_id=$race_id\">";

DrawPageSubTitle('Ostatní závodníci');

// reuse the same table $data_tbl


echo $data_tbl->get_header()."\n";
echo $data_tbl->get_header_row()."\n";

$i = 1;
do {
	if( $zaznam === null ) {
		break; // no more records
	}

	$attrs = [ 'data-fintype' => $zaznam['finance_type'] ?? 0,
		'data-as' => '2',
		'data-transport' => 0,
		'data-accommodation' => 0
		]; // other non-payer

	$kat = '';
	if ( !empty ( $ext_id ) && $connector!== null ) {
		$kat = getOrisClass($zaznam['reg']);
		$kat_id = $checkBoxRows['cat']->addEntry($kat,null,$kat,false,true);
		$attrs['data-cat'] = $kat_id;
	}

	$id = $zaznam['id'];
	
	$row = array();
	$row[] = '<span class="state unpinned">📌</span>';
	$row[] = "<A href=\"javascript:open_win('./view_address.php?id=".$zaznam["u_id"]."','')\" class=\"adr_name\">".$zaznam['sort_name']."</A>";
	
	$amount = $zaznam['amount'];
	$input_amount = '<input type="number" id="am'.$i.'" name="am'.$i.'" value="'.$amount.'" size="5" maxlength="10" data-col="amount" data-init="'.$amount.'" />';
	$row[] = $input_amount;
	
	$note = $zaznam['note'];
	$input_note = '<input type="text" id="nt'.$i.'" name="nt'.$i.'" value="'.$note.'" size="40" maxlength="200" data-col="note" data-init="'.$note.'" />';
	$row[] = $input_note;
	
	$row[] = $kat;

	if ($enable_fin_types) {
		$fintype = $zaznam['finance_type'] ?? 0; 
		$row[] = $checkBoxRows['fintype']->getLabel($fintype) ?? '-';
	}
	
	$row_text = '<A HREF="javascript:open_win(\'./user_finance_view.php?user_id='.$zaznam['u_id'].'\',\'\')">Platby</A>';
	$row_text .= '<input type="hidden" id="userid'.$i.'" name="userid'.$i.'" value="'.$zaznam["u_id"].'"/><input type="hidden" id="paymentid'.$i.'" name="paymentid'.$i.'" value="'.$zaznam["id"].'"/>';
	$row[] = $row_text;

	$regFees = null;
	if ( !empty ( $ext_id ) && $connector!== null ) {
		// startovne z Orisu
		$regFees = getOrisFee($zaznam['reg']);
		$row[] = renderOrisFee( $regFees );
	}

	// startovne
	$row[] = '<span data-col="entryFee" data-init="0"></span>';	

	// financial wizard attributes
	if ( isset ( $regFees ) && $regFees['fee'] ) {
		$attrs['data-start-fee'] = $regFees['fee'];
		$attrs['data-start-tier'] = $regFees['tier'];
	}

	echo $data_tbl->get_new_row_arr($row,$attrs)."\n";
	$i++;
} while ($zaznam=mysqli_fetch_assoc($vysledek_all) );

if ($i == 1)
{	// neni nikdo neprihlasen
	echo $data_tbl->get_info_row('Není nikdo kdo by nebyl přihlášen.')."\n";
}

echo $data_tbl->get_footer()."\n";

?>
<div class="link-top"><a href="#top">Nahoru ...</a></div>
<input type="submit" value="Vytvořit nové platby">
</form>

<script>

document.addEventListener("DOMContentLoaded", () => {
	
// vlozeni vsech checkboxu do pripravenych divu
<?php

	foreach ($checkBoxRows as $key => $checkBoxRow) {
		echo 'var ckbx = document.querySelector("div.checkbox-row[data-key='.$key.']");'."\n";
		echo 'ckbx.innerHTML = '. json_encode($checkBoxRow->render(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ";\n";
	}

?>
	// init the all buttons
	initCheckboxGroups(markSelected);

	// make rows pinnable
	document.querySelectorAll("td .state").forEach(span => {

	span.addEventListener("click", function () {
		if (this.classList.contains("unpinned")) {
		// unpinned → pinned
		this.className = "state pinned";
		} else {
		// selected/pinned → unpinned
		this.className = "state unpinned";
		this.textContent = "📌";
		}
	});
	});

	// make values sweepable, use the same class as pinned-unpinned
	document.querySelectorAll(".form-field .state").forEach(span => {

	span.addEventListener("click", function () {
		const cell = document.getElementById(this.id.slice(0, -5));
		if (this.classList.contains("unpinned")) {
			// uncrossed → crossed
			this.className = "state pinned";
			if (cell) { cell.value = ''; cell.disabled = true; }
		} else {
			// crossed → ucrossed
			this.className = "state unpinned";
			if (cell) { cell.disabled = false; }
		}
	});
	});
 });

</script>
