<?php

function xss_prevent($string)
{
    $xss_charmap = [
        "&" => "&amp;", // must be first
        "<" => "&lt;",
        ">" => "&gt;",
        "\"" => "&quot;",
        "'" => "&#39;",
        "javascript:" => "javascript&#58;" // prevent js execution in <a href="javascript:alert('XSS!')"></a>
    ];

    return str_replace(array_keys($xss_charmap), array_values($xss_charmap), $string);
}

?>