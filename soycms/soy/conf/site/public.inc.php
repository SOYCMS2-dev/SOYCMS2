<?php
SOY2::import("admin.domain.SOYCMS_Site");
SOY2::import("admin.domain.SOYCMS_User");
SOY2::imports("site.domain.*");
SOY2::imports("site.public.base.*");
SOY2::imports("site.public.base.func.*");
SOY2::imports("site.public.base.class.*");
SOY2::imports("site.public.base.page.*");

SOY2::import("plugin.PluginManager");

SOY2PageController::init("SOYCMS_SiteController");
