<?php
/**
 * Weather Application
 *
 * @package Weather-application
 */

require_once(__DIR__ .'/config.php');

$smarty->assign('page', 'index');
$smarty->display('main'.TEMPLATE_EXT);