<?php
chdir($_SERVER["PROJECT_ROOT"]);
$config = parse_ini_file("./env.ini");

require_once "util.php";