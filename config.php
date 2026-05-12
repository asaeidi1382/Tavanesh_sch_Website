<?php

define('DB_HOST', '10.45.12.5:3306');
define('DB_NAME', 'tavanesh_school');
define('DB_USER', 'tavanesh-sch_ir_mms');
define('DB_PASS', '_Ya9&Rq69qrauXnn');

function getDB() {

    static $db = null;

    if ($db === null) {

        $db = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS
        );

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return $db;
}