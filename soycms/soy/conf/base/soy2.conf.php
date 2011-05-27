<?php
SOY2HTMLConfig::CacheDir(SOYCMS_ROOT_DIR . "tmp/");
SOY2HTMLConfig::LayoutDir(SOYCMS_COMMON_DIR . "template/_layout/");

SOY2HTMLPlugin::addPlugin("lang","LanguageConvertPlugin");
SOY2HTMLPlugin::addPlugin("display","DisplayPlugin");
SOY2HTMLPlugin::addPlugin("link","FancyURLLinkPlugin");
SOY2HTMLPlugin::addPlugin("src","FancyURLSrcPlugin");

SOY2DAOConfig::EntityDir(SOY2::RootDir());
SOY2DAOConfig::DaoDir(SOY2::RootDir());
SOY2DAOConfig::DaoCacheDir(SOYCMS_ROOT_DIR . "tmp/");
SOY2DAOConfig::setOption("connection_failure","throw");
