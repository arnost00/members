<?php
define("__HIDE_TEST__", "_KeAr_PHP_WEB_");
@extract($_REQUEST);

require_once ('connect.inc.php');
require_once ('sess.inc.php');
require_once ('common.inc.php');
require_once ('common_fin.inc.php');
require_once('./cfg/_globals.php');

if (!IsLoggedFinance()) {
	header('location: '.$g_baseadr.'error.php?code=21');
	exit;
}

db_Connect();

// --- Sanitize / validate input ---
$payment_type = isset($_POST['payment_type']) ? trim($_POST['payment_type']) : '';
if ($payment_type === '') {
		header('location: '.$g_baseadr.'error.php?code=62'); // missing required field
		exit;
	}

$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
   
$local_payrule_keys = $g_payrule_keys; // make a shallow copy
$local_payrule_keys[] = ['finance_type', 'Finanční typ', 'i'];

// --- Build normalized key array ---
$sqlkeys = [];

foreach ($local_payrule_keys as [$key, $label, $type]) {
	$post_all = $_POST[$key . '_all'] ?? null;
	$post_arr = $_POST[$key] ?? [];

	if ($post_all) {
		$sqlkeys[$key] = null;
	} elseif ($key === 'termin' && is_array($post_arr) && count($post_arr) > 0 ) {
		$nums = array_map('intval', $post_arr);
		sort($nums);
		$first = reset($nums);
		// if full continuous range n..5 is selected -> -n
		if ($nums === range($first, 5)) {
			$sqlkeys[$key] = -$first;
		} else {
			$sqlkeys[$key] = $nums;
        }
	} elseif ( $key === 'zebricek' && is_array($post_arr) && count($post_arr) > 0 ) {
		// sum up bitwise flags
		$sqlkeys[$key] = array_sum($post_arr);
	} elseif ( $key === 'finance_type' && is_array($post_arr) && count($post_arr) > 0 ) {
		$sqlkeys[$key] = array_map('intval',$post_arr);
	} else {
		$sqlkeys[$key] = is_array($post_arr) && count($post_arr) > 0 ? $post_arr : null;
    }
}

// --- Check existing record (same key combination) ---
function find_existing_ids(array $keys, int $uctovano): array
{
	$conditions = [];
	$values = [];
	$types = '';
	foreach ($keys as $k => $v) {
		if (is_null($v)) {
			$conditions[] = "$k IS NULL";
        } else {
			$conditions[] = "$k = ?";
			$types .= is_int($v) ? 'i' : 's';
			$values[] = $v;
		}
	}
	$sql = "SELECT id, uctovano FROM " . TBL_PAYRULES . " WHERE " . implode(' AND ', $conditions);
	$stmt = db_prepare($sql);
	$res = db_select($stmt,$types,$values);

    $result = [];

	$has_entry = false;

	echo '<br> uctovano'; var_dump ( $uctovano );
    if ($res && count($res) > 0) {
        foreach ($res as $row) {
			echo '<br> row'; var_dump ( $row );
			$mask = -1; // delete record
			if ( !$has_entry ) {
				// there is no record yet
				if ( $row['uctovano'] == $uctovano ) {
					// exact one use it
					$mask = $uctovano;
					$has_entry = true;
				} elseif ( ( $row['uctovano'] | $uctovano ) == $uctovano ) {
					// no additional bits
					$mask = $uctovano;
					$has_entry = true;
				} else {
					// remaining bits
            		$mask = ((int)$row['uctovano']) & (~(int)$uctovano);
				}
			}
			echo 'Mask ' . $mask . ' ' . $has_entry;
            $result[$row['id']] = $mask;
        }
    }
	
	return $result;
}    

// Build combinations of all key values
$combinations = [[]];
foreach ($local_payrule_keys as [$key, $label, $type]) {

	$vals = $sqlkeys[$key];

	if ($vals === null) {
		$vals = [null];
	} elseif (is_array($vals)) {
		$vals = $vals;
	} else {
		$vals = [$vals];
	}

	// build Cartesian product
	$newCombinations = [];
	foreach ($combinations as $combo) {
		foreach ($vals as $v) {
			$newCombinations[] = array_merge($combo, [$key => $v]);
		}
	}
	$combinations = $newCombinations;
}

var_dump ( $combinations );
// --- Determine existing / new record ---
$uctovano = isset($_POST['uctovano']) ? array_sum($_POST['uctovano']) : 0;

// --- For each combination: update or insert ---
foreach ($combinations as $combo) {

	echo '<br>Combo '; var_dump ( $combo );
	$existing_ids = find_existing_ids($combo, $uctovano);
	echo '<br>existing_ids '; var_dump ($existing_ids);

	$has_entry = false;

	foreach ($existing_ids as $existing_id => $existing_uctovano) {
		
		echo '<br> existing'; var_dump ($existing_id); var_dump($existing_uctovano);
		// manage existing
		if ($existing_id) {
			if ( $existing_uctovano == -1 ) {
				// delete record
				$sql = "DELETE FROM " . TBL_PAYRULES . " WHERE id = ?";
				$stmt = db_prepare($sql);
				echo '<br>' . $sql . ' id=' . $existing_id;
				$result = db_exec($stmt, 's', [$existing_id]);
			} elseif ( ((int)$existing_uctovano & ~(int)$uctovano ) != 0 ) {
				// restrict existing $uctovano list
				$sql = "UPDATE " . TBL_PAYRULES . " 
						SET uctovano = ? 
						WHERE id = ?";
				$stmt = db_prepare($sql);
				echo '<br>' . $sql . ' id=' . $existing_id . ' ' . $existing_uctovano;
				$result = db_exec($stmt, 'si', [$existing_uctovano, $existing_id]);
			} else {
				// --- Update existing ---
				$has_entry = true;
				$sql = "UPDATE " . TBL_PAYRULES . " 
						SET druh_platby = ?, platba = ?, uctovano = ? 
						WHERE id = ?";
				$stmt = db_prepare($sql);

				echo '<br>' . $sql . ' id=' . $existing_id . ' ' . $payment_type . ' ' . $amount . ' ' . $existing_uctovano;
				$result = db_exec($stmt, 'siii', [$payment_type, $amount, $existing_uctovano, $existing_id]);
			}

			if ($result === FALSE)
				die("Nepodařilo se uložit záznam o definici platby.");
		} 
	}
	
	if ( !$has_entry ) {
		// --- Insert new ---
		$fields = [];
		$placeholders = [];
		$values = [];
		$types = '';

		foreach ($local_payrule_keys as [$key, $label, $type]) {
			$fields[] = $key;
			$placeholders[] = '?';
			$v = $combo[$key] ?? null;
			$values[] = $v;
			$types .= $type;
		}

		// add fixed fields
		$fields[] = 'druh_platby';
		$fields[] = 'platba';
		$fields[] = 'uctovano';
		$placeholders[] = '?';
		$placeholders[] = '?';
		$placeholders[] = '?';
		$values[] = $payment_type;
		$values[] = $amount;
		$values[] = $uctovano;
		$types .= 'sii';

		$sql = "INSERT INTO " . TBL_PAYRULES . " (" . implode(',', $fields) . ")
			VALUES (" . implode(',', $placeholders) . ")";

		$stmt = db_prepare($sql);

		echo '<br>' . $sql . ' ' .$types. ' '; var_dump ( $values );
		$result = db_exec($stmt, $types, $values);

		var_dump($result);

		if ($result == FALSE)
			die("Nepodařilo se vytvořit záznam o definici platby.");
	}
}

//header('location: '.$g_baseadr.'index.php?id='._FINANCE_GROUP_ID_.'&subid=4');
exit;
?>
