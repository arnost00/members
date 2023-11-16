<?php

require_once("./__api.php");
require_once("./__jwt.php");
require_once("./__db.php");

require_once("../common.inc.php");

$action = require_action();

switch ($action) {
    case "managing_users":
        $user_id = require_user_id(true);

        $result = [];

        // make sure the chief is on the zero index
        $output = db_execute("SELECT id, jmeno, prijmeni, reg, si_chip FROM " . TBL_USER . " WHERE id = ? UNION SELECT id, jmeno, prijmeni, reg, si_chip FROM " . TBL_USER . " WHERE chief_id = ?", $user_id, $user_id);
        while ($user = $output->fetch_assoc()) {
            $result[] = [
                "user_id" => $user["id"],
                "name" => $user["jmeno"],
                "surname" => $user["prijmeni"],

                "register_number" => $user["reg"],
                "chip_number" => $user["si_chip"],

                "chief_id" => @$user["chief_id"], // allow null
                "chief_pay" => @$user["chief_pay"], // allow null
            ];
        }

        print_and_die();
        break;
    case "user_data":
        $user_id = require_user_id(true);

        $output = db_execute("SELECT * FROM " . TBL_USER . " WHERE id = ?", $user_id);
        $output = $output->fetch_assoc();

        $result = [
            "user_id" => $output["id"],
            // "login" => $output["login"],

            // "last_visit" => $output["last_visit"],
            
            "name" => $output["jmeno"],
            "surname" => $output["prijmeni"],
            "email" => $output["email"],
            "gender" => $output["poh"],
            "birth_date" => $output["datum"],
            "birth_number" => $output["rc"],
            "nationality" => $output["narodnost"],
            "address" => $output["adresa"],
            "city" => $output["mesto"],
            "postal_code" => $output["psc"],
            "phone" => $output["tel_mobil"],
            "phone_home" => $output["tel_domu"],
            "phone_work" => $output["tel_zam"],
            
            "registration_number" => $output["reg"],
            "chip_number" => $output["si_chip"],

            "chief_id" => $output["chief_id"],
            "chief_pay" => $output["chief_pay"],

            "licence_ob"=> $output["lic"],
            "licence_lob" => $output["lic_lob"],
            "licence_mtbo" => $output["lic_mtbo"],

            "is_hidden" => $output["hidden"] != "0",
            "is_entry_locked" => $output["entry_locked"] != "0",
            // "is_locked" => $output["locked"] != "0",

            // "policy_adm" => $output["policy_adm"],
            // "policy_fin" => $output["policy_fin"],
            // "policy_mng" => $output["policy_mng"],
            // "policy_news" => $output["policy_news"],
            // "policy_regs" => $output["policy_regs"],
        ];

        print_and_die();
        break;
    case "update_user_data":
        $user_id = require_user_id(true);

        $translate_items = [
            // "id" => 53,
            // "sort_name" => "Hönsch Juraj",
            // "rc" => "",
            
            "name" => "jmeno",
            "surname" => "prijmeni",
            "email" => "email",
            "gender" => "poh",
            "birth_date" => "datum",
            "birth_number" => "rc",
            "nationality" => "narodnost",
            "address" => "adresa",
            "city" => "mesto",
            "postal_code" => "psc",
            "phone" => "tel_mobil",
            "phone_home" => "tel_domu",
            "phone_work" => "tel_zam",
            
            "register_number" => "reg",
            "chip_number" => "si_chip",
            
            // "chief_id" => "chief_id",
            // "chief_pay" => "chief_pay",
            
            "licence_ob" => "lic",
            "licence_lob" => "lic_lob",
            "licence_mtbo" => "lic_mtbo",
            
            "is_hidden" => "hidden",
            // "entry_locked" => 0,
            
            // "finance_type" => 0,
            // "fin" => 0,
        ];

        foreach($translate_items as $key => $val) {
            if (!isset($_POST[$key])) {
                raise_and_die("$key is not set");
            }
        }

        $_POST["sort_name"] = $_POST["name"] . $_POST["surname"];

        // $_POST["is_hidden"] = $_POST["is_hidden"] ? 0 : 1;

        // jmeno = ?, prijmeni = ?, email = ?, poh = ?, datum = ?, narodnost = ?, adresa = ?, mesto = ?, psc = ?, tel_mobil = ?, tel_domu = ?, tel_zam = ?, reg = ?, si_chip = ?, lic = ?, lic_lob = ?, lic_mtob = ?, hidden = ?

        $query = "UPDATE " . TBL_USER . " SET ";
        $query.= join(", ", array_map(function ($item) {return "$item=?";}, array_values($translate_items)));
        $query.= " WHERE id=?";

        $items = array_map(function ($item) {return $_POST[$item];}, array_keys($translate_items));

        db_execute($query, ...[...$items, $user_id]);

        print_and_die();
        break;
    case "login":
        $username = @$_POST["username"];
        $password = @$_POST["password"];

        if (!isset($username) | !isset($password)) {
            raise_and_die("username or password are not set");
        }

        $output = db_execute("SELECT id_users, login, heslo, locked FROM " . TBL_ACCOUNT . " WHERE login = ? LIMIT 1", $username);
        $output = $output->fetch_assoc();

        if (!$output) {
            raise_and_die("invalid username", 401);
        }

        if (!password_verify(md5($password), $output["heslo"])){
            raise_and_die("invalid password", 401);
        }

        if ($output["locked"]) {
            raise_and_die("account is locked", 401);
        }

        $timestamp = GetCurrentDate();

        db_execute("UPDATE " . TBL_ACCOUNT . " SET last_visit = ? WHERE id_users = ?", $timestamp, $output["id_users"]);

        $result = [
            "token" => craft_api_token($output["id_users"])
        ];

        print_and_die();
        break;
    default:
        raise_and_die("provided action is not implemented", 404);
        break;
}
?>
