<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
require_once __DIR__.'/lib/class.url_path_config.php';
require_once __DIR__.'/lib/class.url_path_rewrite.php';
$my_package=$this->getPackageId();
rex_extension::register('URL_REWRITE',array($my_package,'rewrite'));
if(!rex::isBackend()) $my_package::set_current();
?>
