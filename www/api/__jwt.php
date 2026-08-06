<?php
require_once("./__api.php");
require_once("../cfg/_cfg.php");
require_once("../lib/jwt.php");

use Jwt\JWT;
use Jwt\JWTException;

function _init_api_token() {
    global $g_jwt_secret_key;

    return new JWT(base64_decode($g_jwt_secret_key), "HS512");
}

function craft_api_token($user_id) {
    try {
        return _init_api_token()->encode([
            "user_id" => $user_id,
            // "exp" => time() + 3600,
        ]);
    } catch (JWTException $e) {
        return raise_and_die($e->getMessage(), 401);
    }
}

function check_api_token($token) {
    try {
        return _init_api_token()->decode($token);
    } catch (JWTException $e) {
        return raise_and_die($e->getMessage(), 401);
    }
}

function require_token() {
    $token = @$_POST["token"];

    if (!isset($token)) {
        raise_and_die("token is required", 401);
    }

    return check_api_token($token);
}   
?>
