<?php
/**
 * URL-Rewrite Addon
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version April 2020
 */
#
$stx='style="padding-left:20px;"';
#
$string='
<div><b>Setzen eines Wunsch-URLs</b></div>
<div '.$stx.'>Ein URL hat in Redaxo die Standardform
<code>index.php?article_id=ID&amp;clang=CID</code> mit Verweis auf
die Artikel-Id und die Sprach-Id. In der Regel soll stattdessen
jeder Artikel einen &quot;Wunsch-URL&quot; erhalten, der Hinweise
gibt auf Artikelinhalt, Themenkategorie, Site-Struktur o. Ä.
Zur Realisierung wird eine Funktion definiert, die den gewünschten
URL am <code>Extension Point URL_REWRITE</code> zurück gibt. Im
Backend wird diese Funktion nur im Content-Kontext eines Artikels
(edit, metainfo, functions) aufgerufen, im Frontend nur innerhalb
der Funktion <code>rex_getUrl($article_id,$clang_id)</code>.
Letztere liefert dem Redakteur (z. B. in Templates oder Modulen)
den URL eines Artikels und die Anzeige im Browser-Adressfeld.</div>
<br/>
<div><b>Rewrite-Mechanismus</b></div>
<div '.$stx.'>Ein Link auf einen Artikel wird durch eine
Umleitungsregel
<div '.$stx.'>
<tt>RewriteRule ^(.*)$ index.php?%{QUERY_STRING} [L]</tt>
&nbsp; &nbsp; &nbsp; (Datei <tt>.htaccess</tt>)</div>
an das Redaxo CMS übergeben. Links auf Dateien oder Verzeichnisse
erfolgen ohne Umleitung. Die Umleitung führt zunächst auf den
Site-Startartikel. Damit stattdessen der gewünschte Artikel
angezeigt wird, muss die aktuelle Artikel-Id
<code>rex_article::getCurrentId()</code> mit der Id des Artikels
überschrieben werden. Bei mehrsprachigen Installationen muss
auch die aktuelle Sprach-Id <code>rex_clang::getCurrentId()</code>
durch die Sprach-Id des Artikels ersetzt werden. Artikel-Id
und Sprach-Id sind aus dem URL des anzuzeigenden Artikels, d. h.
aus der Variablen <code>$_SERVER[\'REQUEST_URI\']</code>, zu
ermitteln.</div>
';
echo $string;
?>
