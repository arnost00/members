<?php

require_once("./__api.php");
require_once("./__jwt.php");
require_once("./__utils.php");
require_once("./__parse.php");
require_once("./__db.php");

require_once("../common.inc.php");
require_once('../cfg/enums.php');

$action = require_action();

switch ($action) {
    case "list":
        $current_date = GetCurrentDate();

        $output = db_execute("SELECT * FROM " . TBL_RACE . " WHERE datum >= ? || datum2 >= ? ORDER BY datum, datum2, id", $current_date, $current_date);

        $result["data"] = [];
        while ($race = $output->fetch_assoc()) {
            $result["data"][] = parse_race_row($race);
        }

        print_and_die();

        break;
    case "detail":
        // additional data to list
        $race_id = require_race_id();
        $user_id = require_user_id();

        $output = db_execute("SELECT * FROM " . TBL_RACE . " WHERE id = ? LIMIT 1", $race_id);
        $output = $output->fetch_assoc();
        
        $result["data"] = parse_race_row($output);

        $output = db_execute("SELECT zavxus.*, user.jmeno, user.prijmeni FROM "  . TBL_ZAVXUS . " as zavxus, " . TBL_USER . " as user WHERE zavxus.id_user = user.id AND id_zavod = ?", $race_id);

        $result["data"]["everyone"] = [];
        $result["data"]["myself"] = [];
        $result["data"]["am_i_signed"] = false;

        while ($child = $output->fetch_assoc()) {
            $child_id = $child["id_user"];

            $formated_output = [
                "user_id" => $child_id,
                "name" => $child["jmeno"],
                "surname" => $child["prijmeni"],
                "category" => $child["kat"],
                "note" => $child["pozn"],
                "note_internal" => $child["pozn_in"],
                "transport" => $child["transport"], // value can be 0, 1, 2
                "accommodation" => $child["ubytovani"], // value can be 0, 1, 2
            ];

            if ($child_id == $user_id) {
                $result["data"]["am_i_signed"] = true;
                $result["data"]["myself"] = $formated_output;
            }

            $result["data"]["everyone"][] = $formated_output;
        }

        print_and_die();
    case "signin":
        $user_id = require_user_id(true);
        $race_id = require_race_id();

        // check that the required data is provided
        $required_data = [
            "category" => $_POST["category"],
            "note" => $_POST["note"],
            "note_internal" => $_POST["note_internal"],
            "transport" => $_POST["transport"],
            "accommodation" => $_POST["accommodation"],
        ];
        foreach ($required_data as $key => $value) {
            if (!isset($value)) {
                raise_and_die("$key is not set");
            }
        }
        extract($required_data);

        // $category = correct_sql_string($category);
        // $note = correct_sql_string($note);
        // $note_internal = correct_sql_string($note_internal);
        $transport = $transport ? 1 : 0;
        $accommodation = $accommodation ? 1 : 0;

        $entry_lock = is_entry_locked($user_id);
        if ($entry_lock) {
            raise_and_die("entry is locked");
        }

        $termin = get_termin_from_race($race_id);
        if ($termin == 0) {
            raise_and_die("invalid termin number");
        }
        
        // check whether the race already exists
        $output =  db_execute("SELECT id FROM " . TBL_ZAVXUS . " WHERE id_zavod = ? and id_user = ?", $race_id, $user_id);
        $output = $output->fetch_assoc();

        if ($output == null) { // if not, create a new row with corresponding values
            db_execute("INSERT INTO " . TBL_ZAVXUS . " (id_user, id_zavod, kat, pozn, pozn_in, termin, transport, ubytovani) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", $user_id, $race_id, $category, $note, $note_internal, $termin, $transport, $accommodation);
        } else { // if yes, update the row
            $id = $output["id"];
            db_execute("UPDATE " . TBL_ZAVXUS . " SET kat = ?, pozn = ?, pozn_in = ?, termin = ?, transport = ?, ubytovani = ? WHERE id = ?", $category, $note, $note_internal, $termin, $transport, $accommodation, $id);
        }
        
        print_and_die();
        
        break;
    case "signout":
        $user_id = require_user_id(true);
        $race_id = require_race_id();

        $entry_lock = is_entry_locked($user_id);
        
        if ($entry_lock) {
            raise_and_die("entry is locked");
        }
        
        db_execute("DELETE FROM " . TBL_ZAVXUS . " WHERE id_zavod = ? AND id_user = ?", $race_id, $user_id);
        
        print_and_die();
    case "user_past_races":
        $user_id = require_user_id();
        
        $output = db_execute("SELECT id_zavod FROM " . TBL_ZAVXUS . " WHERE id_user = ?", $user_id);

        $result["data"] = [];

        while ($child = $output->fetch_assoc()) {
            $result["data"][] = $child;
        }

        print_and_die($query);

        break;
    default:
        raise_and_die("provided action is not implemented");
        break;
}

function parse_race_row($race) {
    // wants race row from "SELECT * FROM " . TBL_RACE
    $race_id = $race["id"];

    $dates = [ Date2ISO($race["datum"]) ]; // always provide date
    if ($race["vicedenni"]) $dates[] = Date2ISO($race["datum2"]); // add second date if exists

    $entries = [ Date2ISO($race["prihlasky1"]) ]; // always provide entry
    if ($race['prihlasky2'] != 0 && $race['prihlasky'] > 1 ) $dates[] = Date2ISO($race['prihlasky2']);
    if ($race['prihlasky3'] != 0 && $race['prihlasky'] > 2 ) $dates[] = Date2ISO($race['prihlasky3']);
    if ($race['prihlasky4'] != 0 && $race['prihlasky'] > 3 ) $dates[] = Date2ISO($race['prihlasky4']);
    if ($race['prihlasky5'] != 0 && $race['prihlasky'] > 4 ) $dates[] = Date2ISO($race['prihlasky5']);

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

    $categories = explode(";", $race["kategorie"]);

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

// https://stackoverflow.com/a/14701491/14900791
function add_scheme($url, $scheme = 'http://') {
    $url = ltrim($url, '/'); // handle relative protocols

    return parse_url($url, PHP_URL_SCHEME) === null ? $scheme . $url : $url;
}

?>
