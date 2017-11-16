<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
#
# --- Erweiterung der MetaInfos in der Redaxo-Datenbank
if(rex::isBackend()):
  require_once __DIR__.'/functions/function.general.php';
  require_once __DIR__.'/functions/function.install.php';
  path_rewrite_metainfos();
  endif;
?>
