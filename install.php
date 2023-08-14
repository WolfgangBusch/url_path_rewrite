<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
#
# --- bei der Neuinstallation:
#     Erweiterung der MetaInfos in der Redaxo-Datenbank
url_path_config::install_metainfos();
#     Setzen einer Default-Konfiguration
url_path_config::first_config();
?>
