<?php
chdir(dirname(__FILE__));
define("SOYCMS_SCRIPT_FILENAME", __FILE__);
require("../soy/common.inc.php");
include("filemanager/config.inc.php");
SOY2PageController::run();


