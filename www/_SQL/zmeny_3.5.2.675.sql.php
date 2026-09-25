<?php
// Independent transport and accommodation deadlines.
$version_upd = '3.5.2.675';
require_once 'prepare.inc.php';
$sql[0] = 'ALTER TABLE `'.TBL_RACE.'`
    ADD COLUMN `transport_do` INT(11) NULL DEFAULT NULL AFTER `transport`,
    ADD COLUMN `ubytovani_do` INT(11) NULL DEFAULT NULL AFTER `ubytovani`';
require_once 'action.inc.php';
