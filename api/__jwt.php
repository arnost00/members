<?php
require_once("./__api.php");

$supported_alg = "HS512";
$supported_typ = "JWT";

function generate_jwt($payload) {
    global $supported_alg, $supported_typ;

    $header = base64_url_encode(json_encode([
        "alg" => $supported_alg, 
        "typ" => $supported_typ,
    ]));
    
    $payload = base64_url_encode(json_encode($payload));
    
    $signature = sign_jwt($header, $payload);
    
    return "$header.$payload.$signature";
}

function sign_jwt($header, $payload) {
    // local variable key
    $key = base64_decode("JAJA7yOFFH3qSn4VZ+uv8oJqP/NmliOkGc9ljc1EeVqJlrqfJssPKNUEODBoLEZd5ySYWgr5lEhXVMaO4DpbXA==");
    return base64_url_encode(hash_hmac("sha512", "$header.$payload", $key, true));
}

function parse_jwt($token) {
    global $supported_alg, $supported_typ;
    
    [$header, $payload, $signature] = explode(".", $token, 3);

    $parsed_header = json_decode(base64_url_decode($header), true);
    if ($parsed_header["alg"] != $supported_alg) {
        raise_and_die("algorithm not supported");
        return false; // make sure function exits
    }

    if ($parsed_header["typ"] != $supported_typ) {
        raise_and_die("type not supported");
        return false; // make sure function exits
    }

    if (sign_jwt($header, $payload) != $signature) {
        raise_and_die("token is not valid");
        return false;
    }

    return json_decode(base64_url_decode($payload), true);
}

function require_token() {
    $token = @$_POST["token"];

    if (!isset($token)) {
        raise_and_die("token is required");
    }

    return parse_jwt($token);
}

/**
 * per https://stackoverflow.com/questions/2040240/php-function-to-generabte-v4-uuid/15875555#15875555
 */
function base64_url_encode($text) {
	return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
}

/* handle stripped paddings in base64 url decode, by @Jurakin */
function base64_url_decode($text) {
	return base64_decode(str_replace(['-', '_'], ['+', '/'], $text));
}
   
?>