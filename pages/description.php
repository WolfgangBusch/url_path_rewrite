<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
#
$attr="Metadaten";
$vname="&quot;Verzeichnisname&quot;";
$dname="&quot;Dateiname&quot;";
$urlbez="Custom URL";
#
$string='
<div><b>Elemente der URL-Darstellung:</b></div>
<div style="padding-left:30px;">Für die Darstellung eines URLs werden
bei der Installation des AddOns die folgenden Meta Infos angelegt (als
Zeilen in der Tabelle <code>rex_metainfo_field</code> und als Spalten
in der Tabelle <code>rex_article</code>). Sie sind für alle Sprachen
gleich. Sie werden bei der De-Installation nicht wieder entfernt.
<ul style="padding-left:30px; margin-bottom:0px;">
    <li><code>'.REWRITER_DIR.'</code> : &nbsp; '.$vname.' für jede
        Kategorie,<br/>
        der Wert wird im Menü <code style="color:green;">ändern</code>
        der Kategorie eingegeben, &nbsp;
        Default: Kategoriename (<code>catname</code>)</li>
    <li><code>'.REWRITER_BASE.'</code> : &nbsp; '.$dname.' für jeden
        Artikel,<br/>
        der Wert wird im Menü <code style="color:green;">'.$attr.'</code>
        des Artikels eingegeben, &nbsp;
        Default: &quot;Artikelname.Namenserweiterung&quot;
        (<code>name.html</code>),<br/>
        für Kategorie-Startartikel wird der Artikelname durch
        &quot;index&quot; ersetzt ('.$dname.': <code>index.html</code>)</li>
    <li><code>'.REWRITER_URL.'</code> : &nbsp;
        aus den obigen Daten generierter Artikel-URL ('.$urlbez.', ohne
        vorangestellten &quot;/&quot;),<br/>
        ablesbar (<tt>readonly</tt>) im Eingabefeld des Menüs
        <code style="color:green;">'.$attr.'</code> des Artikels,<br/>
        den Wert (mit vorangestelltem &quot;/&quot;) liefert die
        Standardfunktion <code>rex_getUrl</code></li>
</ul>
Erlaubte Zeichen für die Meta Infos sind: '.path_rewrite_allowed_chars().'.
Startartikelname und Namenserweiterung können auch anders konfiguriert
werden.
</div>
<br/>
<div><b>Kennzeichnung der Sprache:</b></div>
<div style="padding-left:30px;">Die Kennzeichnung der Sprache erfolgt
mittels der definierten Sprachcodes, wahlweise durch eine Ergänzung des
Custom URL um
<ul style="padding-left:30px; margin-bottom:0px;">
    <li>den Sprachcode in der Form <code>en/'.REWRITER_DIR.'1/...</code>
        oder</li>
    <li>einen URL-Parameter in der Form
        <code>.../'.REWRITER_BASE.'?language=en</code> oder</li>
    <li>eine Session-Variable <code>$_SESSION[\'language\']=\'en\'</code>.
        Ein Sprachwechsel erfolgt hier mittels URL-Parameter im
        entsprechenden Link (vergl. vorige Zeile).</li>
</ul>
Die Art der Kennzeichnung ist konfigurierbar. Bei der Standardsprache
und damit auch bei einsprachigen Installationen entfällt sie ganz.</div>
';
echo utf8_encode($string);
?>
