<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
#
# --- Entfernen der Konfigurationsdaten
rex_config::removeNamespace($this->getPackageId());
#
# --- Die eingerichteten Meta-Infos werden NICHT wieder entfernt
?>