<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Januar 2021
 */
require_once __DIR__.'/lib/class.path_config.php';
require_once __DIR__.'/lib/class.path_url_rewrite.php';
rex_extension::register('URL_REWRITE',array('url_rewrite','rewrite'));
if(!rex::isBackend()) url_rewrite::set_current();
?>
