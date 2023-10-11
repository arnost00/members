<?php
require_once("./__db.php");

require_once("../common_race2.inc.php");

function is_entry_locked($user_id) {
    $output = db_execute("SELECT entry_locked FROM " . TBL_USER . " WHERE id = ?", $user_id);
    $output = $output->fetch_assoc();

    if ($output === null) return false;

    return $output["entry_locked"];
}


function get_termin_from_race($race_id) {
    $output = db_execute("SELECT datum, prihlasky, prihlasky1, prihlasky2, prihlasky3, prihlasky4, prihlasky5 FROM " . TBL_RACE . " WHERE id = ?", $race_id);
    $output = $output->fetch_assoc();
    
    return raceterms::GetCurr4RegTerm($output);
}

?>