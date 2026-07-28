<?php
if (!defined('BASE_PATH')) {
    $base = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"]));
    define("BASE_PATH", $base === "/" ? "" : $base);
}