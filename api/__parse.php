<?php

require_once("../cfg/enums.php");
require_once("../cfg/_cfg.php");

function parse_transport($transport) {
    // 0 = No; 1 = Yes; 2 = Auto Yes;

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

?>