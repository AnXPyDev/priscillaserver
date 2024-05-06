<?php
chdir(dirname(__FILE__));
$config = parse_ini_file("./env.ini");
set_include_path("./vendor");

require_once "util.php";