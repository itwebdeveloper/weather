<?php
/**
 * Weather Application
 *
 * @package Weather-application
 */

define("ROOT_DIR", "/var/www/weather/");
define("INCLUDES_DIR", ROOT_DIR."includes/");
define("SMARTY_DIR", "vendor/smarty/smarty/libs/");
define("TEMPLATES_DIR", ROOT_DIR."smarty/");
define("TEMPLATE_DIR", "smarty/templates/dark/");
define("TEMPLATE_NAME", "dark");
define("TEMPLATE_EXT", ".tpl.html");

// put full path to Smarty.class.php
require(SMARTY_DIR.'Smarty.class.php');

$smarty = new Smarty();

$smarty->setTemplateDir(TEMPLATES_DIR.'templates/'.TEMPLATE_NAME);
$smarty->setCompileDir(TEMPLATES_DIR.'templates_c');
$smarty->setCacheDir(TEMPLATES_DIR.'cache');
$smarty->setConfigDir(TEMPLATES_DIR.'configs');

//$smarty->force_compile = true;
$smarty->debugging = true;
$smarty->caching = true;
$smarty->cache_lifetime = 120;

$smarty->assign('TEMPLATE_CSS_DIR', TEMPLATE_DIR.'css');
$smarty->assign('TEMPLATE_JS_DIR', TEMPLATE_DIR.'js');
$smarty->assign('TEMPLATE_IMAGES_DIR', TEMPLATE_DIR.'images');
$smarty->assign('TEMPLATE_EXT', TEMPLATE_EXT);

$smarty->assign('page', 'index');
$smarty->display('main'.TEMPLATE_EXT);