<?php

require_once("./__api.php");
require_once("./__jwt.php");
require_once("./__db.php");

require_once("../common.inc.php");
require_once("../common_race2.inc.php");
require_once('../cfg/race_enums.php');
require_once('../cfg/session_enums.php');
require_once("../cfg/_cfg.php");

$action = require_action();

switch ($action) {
    case "list":
        $current_date = GetCurrentDate();

        $output = db_execute("SELECT * FROM " . TBL_RACE . " WHERE datum >= ? || datum2 >= ? ORDER BY datum, datum2, id", $current_date, $current_date);

        $result = [];
        while ($race = $output->fetch_assoc()) {
            $result[] = parse_race_row($race);
        }

        print_and_die();
        break;
    case "detail":
        $race_id = require_race_id();

        $output = db_execute("SELECT * FROM " . TBL_RACE . " WHERE id = ? LIMIT 1", $race_id);
        $output = $output->fetch_assoc();
        
        // the main data about the race is here
        $result = parse_race_row($output);

        // provide information about signed in users
        $output = db_execute("SELECT zavxus.*, user.* FROM " . TBL_ZAVXUS . " AS zavxus, " . TBL_USER . " AS user WHERE zavxus.id_user = user.id AND id_zavod = ?", $race_id);

        $result["everyone"] = [];

        while ($child = $output->fetch_assoc()) {
            $child_id = $child["id_user"];

            $formated_output = [
                "user_id" => $child_id,

                // user
                "name" => $child["jmeno"],
                "surname" => $child["prijmeni"],
                "registration_number" => $child["reg"],
                "chip_number" => $child["si_chip"],

                // zavxus
                "category" => $child["kat"],
                "note" => $child["pozn"],
                "note_internal" => $child["pozn_in"],
                "transport" => $child["transport"], // value can be 0, 1, 2
                "accommodation" => $child["ubytovani"], // value can be 0, 1, 2
            ];

            $result["everyone"][] = $formated_output;
        }

        print_and_die();
        break;
    case "relations":
        $race_id = require_race_id();
        $user_id = require_user_id(true);

        // select user_id (first) and its sheeps
        $output = db_execute("SELECT * FROM " . TBL_USER . " WHERE id = ? OR chief_id = ? ORDER BY CASE WHEN id = ? THEN 1 ELSE 2 END", $user_id, $user_id, $user_id);
        
        $result = [];

        while ($child = $output->fetch_assoc()) {
            $child_id = $child["id"];

            $zavxus = db_execute("SELECT zavxus.* FROM " . TBL_ZAVXUS . " AS zavxus, " . TBL_USER . " AS user WHERE zavxus.id_user = user.id AND user.id = ? AND zavxus.id_zavod = ? LIMIT 1", $child_id, $race_id);
            $zavxus = $zavxus->fetch_assoc();

            $formated_output = [
                "user_id" => $child_id,

                // user
                "name" => $child["jmeno"],
                "surname" => $child["prijmeni"],
                "registration_number" => $child["reg"],
                "chip_number" => $child["si_chip"],

                // zavxus
                "category" => @$zavxus["kat"],
                "note" => @$zavxus["pozn"],
                "note_internal" => @$zavxus["pozn_in"],
                "transport" => @$zavxus["transport"], // value can be 0, 1, 2, 3
                "sedadlel" => @$zavxus["sedadel"], // value can be -1 to count
                "accommodation" => @$zavxus["ubytovani"], // value can be 0, 1, 2

                "is_signed_in" => $zavxus != null,
            ];

            $result[] = $formated_output;
        }

        print_and_die();
        break;
    case "signin":
        $chief_id = require_user_id(true); // the id of who is signing someone in
        @$user_id = $_POST["user_id"]; // the id of who is being signed in
        if (!isset($user_id)) {
            $user_id = $chief_id; // fallback
        }
        $race_id = require_race_id();

        check_chief_access_to_user($chief_id, $user_id);

        // check that the required data is provided
        $required_data = [
            "category" => $_POST["category"],
            "note" => $_POST["note"],
            "note_internal" => $_POST["note_internal"],
            "transport" => $_POST["transport"],
            "sedadlel" => $_POST["sedadlel"],
            "accommodation" => $_POST["accommodation"],
        ];
        foreach ($required_data as $key => $value) {
            if (!isset($value)) {
                raise_and_die("$key is not set");
            }
        }
        extract($required_data);

        $transport = $transport ? 1 : 0;
        $accommodation = $accommodation ? 1 : 0;

        $entry_lock = is_entry_locked($user_id);
        if ($entry_lock) {
            raise_and_die("your account is locked");
        }

        $termin = get_termin_from_race($race_id);
        if ($termin == 0) {
            raise_and_die("the deadline for entry has been exeeded");
        }

        $output = db_execute("SELECT cancelled FROM " . TBL_RACE . " WHERE id = ?", $race_id);
        $output = $output->fetch_assoc();

        if ($output["cancelled"] == 1) {
            raise_and_die("the race is cancelled");
        }
        
        // check whether the user is already signed in
        $output = db_execute("SELECT * FROM " . TBL_ZAVXUS . " WHERE id_zavod = ? AND id_user = ? LIMIT 1", $race_id, $user_id);;
        $output = $output->fetch_assoc();

        if ($output == null) { // if not, create a new row with given values
            db_execute("INSERT INTO " . TBL_ZAVXUS . " (id_user, id_zavod, kat, pozn, pozn_in, termin, transport, sedadel, ubytovani) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $user_id, $race_id, $category, $note, $note_internal, $termin, $transport, $sedadel, $accommodation);
        } else { // else, update the row
            $id = $output["id"];
            db_execute("UPDATE " . TBL_ZAVXUS . " SET kat = ?, pozn = ?, pozn_in = ?, termin = ?, transport = ?, sedadel = ?, ubytovani = ? WHERE id = ?", $category, $note, $note_internal, $termin, $transport, $sedadel, $accommodation, $id);
        }
        
        print_and_die();
        break;
    case "signout":
        $chief_id = require_user_id(true); // the id of who is signing someone in
        @$user_id = $_POST["user_id"]; // the id of who is being signed in
        if (!isset($user_id)) {
            $user_id = $chief_id; // fallback
        }
        $race_id = require_race_id();

        check_chief_access_to_user($chief_id, $user_id);

        $entry_lock = is_entry_locked($user_id);
        if ($entry_lock) {
            raise_and_die("your account is locked");
        }
        
        $output = db_execute("SELECT cancelled FROM " . TBL_RACE . " WHERE id = ?", $race_id);
        $output = $output->fetch_assoc();

        if ($output["cancelled"] == 1) {
            raise_and_die("the race is cancelled");
        }
        
        db_execute("DELETE FROM " . TBL_ZAVXUS . " WHERE id_zavod = ? AND id_user = ?", $race_id, $user_id);
        
        print_and_die();
    case "past_races":
        $user_id = require_user_id();
        
        $output = db_execute("SELECT id_zavod FROM " . TBL_ZAVXUS . " WHERE id_user = ?", $user_id);

        $result = [];

        while ($child = $output->fetch_assoc()) {
            $result[] = $child;
        }

        print_and_die($query);

        break;
    default:
        raise_and_die("provided action is not implemented", 404);
        break;
}

function check_chief_access_to_user($chief_id, $user_id) { // ak je jeho ovecka, vracia true, inak raise_and_die
    if ($chief_id == $user_id) { // the chief has obviously access to itself
        return true;
    }
    
    $output = db_execute("SELECT policy_mng FROM " . TBL_ACCOUNT . " WHERE id_users = ? LIMIT 1", $chief_id);
    $output = $output->fetch_assoc();

    if ($output["policy_mng"] != _MNG_SMALL_INT_VALUE_) return raise_and_die("chief has to be a small manager", 403);

    $output = db_execute("SELECT id FROM " . TBL_USER . " WHERE id = ? AND chief_id = ? LIMIT 1", $user_id, $chief_id);
    $output = $output->fetch_assoc();

    if ($output == null) return raise_and_die("chief has no access to the user", 403);

    return true;
}

function parse_race_row($race) {
    // wants race row from "SELECT * FROM " . TBL_RACE
    $race_id = $race["id"];

    $dates = [ Date2ISO($race["datum"]) ]; // always provide date
    if ($race["vicedenni"]) $dates[] = Date2ISO($race["datum2"]); // add second date if exists

    $entries = [ Date2ISO($race["prihlasky1"]) ]; // always provide entry
    if ($race['prihlasky2'] != 0 && $race['prihlasky'] > 1 ) $entries[] = Date2ISO($race['prihlasky2']);
    if ($race['prihlasky3'] != 0 && $race['prihlasky'] > 2 ) $entries[] = Date2ISO($race['prihlasky3']);
    if ($race['prihlasky4'] != 0 && $race['prihlasky'] > 3 ) $entries[] = Date2ISO($race['prihlasky4']);
    if ($race['prihlasky5'] != 0 && $race['prihlasky'] > 4 ) $entries[] = Date2ISO($race['prihlasky5']);

    $transport = parse_transport($race['transport']);

    $accommodation = parse_accommodation($race['ubytovani']);

    // use enums to parse attributes
    $type = parse_type($race['typ0']);

    // parse rankings
    $rankings = parse_rankings($race["zebricek"]);
    
    // parse sport
    $sport = parse_sport($race["typ"]);

    // parse cancelled
    $cancelled = $race["cancelled"] == 1;

    // fix scheme
    $link = add_scheme($race['odkaz']);

    // explode returns [""] on empty list
    $categories = $race["kategorie"] == "" ? [] : explode(";", $race["kategorie"]);

    return [
        "race_id" => $race_id,
        // "is_registered" => $is_registered,
        "dates" => $dates,
        "entries" => $entries,
        'name' => $race['nazev'],
        'is_cancelled' => $cancelled,
        'club' => $race['oddil'],
        'link' => $link,
        'place' => $race['misto'],
        'type' => $type,
        'sport' => $sport,
        'rankings' => $rankings,
        'rank21' => $race['ranking'],
        'note' => $race['poznamka'],
        'transport' => $transport,
        'accommodation' => $accommodation,
        'categories' => $categories,
    ];
}

function parse_transport($transport) {
    // 0 = No; 1 = Yes; 2 = Auto Yes; 3 = Shared

    global $g_enable_race_transport;
    return $g_enable_race_transport ? $transport : 0;
}


function parse_accommodation($accommodation) {
    // 0 = No; 1 = Yes; 2 = Auto Yes;
    global $g_enable_race_accommodation;
    return $g_enable_race_accommodation ? $accommodation : 0;
}

function parse_type($type) {
    global $g_racetype0;
    return $g_racetype0[$type];
}

function parse_rankings($zebricek) {
    global $g_zebricek, $g_zebricek_cnt;

    $rankings = [];
    for($i=0; $i<$g_zebricek_cnt; $i++)
    {
        if ($g_zebricek[$i]['id'] & $zebricek)
        {
            $rankings[] = $g_zebricek[$i]['nm'];
        }
    }

    return $rankings;
}

function parse_sport($typ) {
    global $g_racetype, $g_racetype_cnt;

    for ($i=0; $i<$g_racetype_cnt; $i++)
    {
        if ($g_racetype[$i]["enum"] == $typ)
        {
            return $g_racetype[$i]["nm"];
        }
    }

    return null;
}

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

// https://stackoverflow.com/a/14701491/14900791
function add_scheme($url, $scheme = 'http://') {
    if ($url == "") return "";

    $url = ltrim($url, '/'); // handle relative protocols

    return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
}

?>
