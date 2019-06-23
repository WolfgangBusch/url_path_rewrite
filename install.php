<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2019
 */
#
require_once __DIR__.'/lib/class.path_config.php';
#
# --- Erweiterung der MetaInfos in der Redaxo-Datenbank
if(rex::isBackend()) url_path_config::install_metainfos();
#
# --- Setzen der Konfigurationsdaten (direkt nach der Installation)
$defconf=url_path_config::default_config();
$key=array_keys($defconf);
$first=TRUE;
for($i=0;$i<count($key);$i=$i+1)
   if(!empty(rex_config::get(REWRITER,$key[$i]))) $first=FALSE;
if($first)
  for($i=0;$i<count($key);$i=$i+1) rex_config::set(REWRITER,$key[$i],$defconf[$key[$i]]);
?>
