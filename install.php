<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Januar 2021
 */
#
require_once __DIR__.'/lib/class.path_config.php';
#
# --- Erweiterung der MetaInfos in der Redaxo-Datenbank bei der Erstinstallation
url_path_config::install_metainfos();
#
# --- Setzen der Default-Konfiguration nach De-Installation
url_path_config::first_config();
?>
