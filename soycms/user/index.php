<?php
chdir(dirname(__FILE__));
define("SOYCMS_SCRIPT_FILENAME", __FILE__);
require("../soy/common.inc.php");
require("./webapp/config.php");
SOY2FancyURIController::run();