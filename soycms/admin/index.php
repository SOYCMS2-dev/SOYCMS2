<?php
chdir(dirname(__FILE__));
define("SOYCMS_SCRIPT_FILENAME", __FILE__);
require("../soy/common.inc.php");
include(SOYCMS_COMMON_DIR . "conf/admin/admin.inc.php");
SOY2FancyURIController::run();


