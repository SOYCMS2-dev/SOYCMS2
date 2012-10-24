<?php
/*
 * ここは公開側用の設定を記述
 */
define("PLUSUSER_ROOT_DIR", soy2_realpath(dirname(dirname(__FILE__))));

SOY2::imports("base.*", PLUSUSER_ROOT_DIR . "src/");
SOY2::imports("domain.*", PLUSUSER_ROOT_DIR . "src/");
SOY2::imports("logic.*", PLUSUSER_ROOT_DIR . "src/");
SOY2::imports("extensions.*", PLUSUSER_ROOT_DIR . "src/");

