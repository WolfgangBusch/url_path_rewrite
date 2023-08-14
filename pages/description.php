<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
#
$addon=$this->getPackageId();
$allow=$addon::allowed_chars();
$pos=strpos($allow,'Buchstaben')-1;
$allow=substr($allow,$pos);
echo '
<div><b>Elemente der URL-Darstellung:</b></div>
<div '.$addon::INDENT.'>Für die Darstellung eines URLs werden bei der
Installation des AddOns die folgenden MetaInfos angelegt, als Zeilen in
der Tabelle <code>rex_metainfo_field</code> und als Spalten in der Tabelle
<code>rex_article</code>. Sie werden bei der De-Installation nicht wieder
entfernt.
<ul '.$addon::MARGIN0.'>
    <li><code>'.$addon::REWRITER_DIR.'</code> : &nbsp; '.$addon::VNAME.' für
        jede Kategorie, Default: Kategoriename (<code>catname</code>)</li>
    <li><code>'.$addon::REWRITER_BASE.'</code> : &nbsp; '.$addon::DNAME.'
        für jeden Artikel, Default: Artikelname mit Namenserweiterung
        (<code>name.html</code>),<br>
        für Kategorie-Startartikel wird der Artikelname durch &quot;index&quot;
        ersetzt ('.$addon::DNAME.': <code>index.html</code>)</li>
    <li><code>'.$addon::REWRITER_URL.'</code> : &nbsp; aus den obigen
        Daten generierter Artikel-URL ('.$addon::URLNAME.', ohne führenden
        &quot;/&quot;),<br>
        ablesbar (<tt>readonly</tt>) in den Metadaten des Artikels,
        er ist für alle Sprachversionen gleich,<br>
        den Wert (mit führendem &quot;/&quot;) liefert die Standardfunktion
        <code>rex_getUrl(article_id)</code></li>
</ul>
Startartikelname und Namenserweiterung können auch anders konfiguriert
werden.<br>
Erlaubte Zeichen für die MetaInfos sind '.$allow.'</div>
<br>
<div><b>Kennzeichnung der Sprache:</b></div>
<div '.$addon::INDENT.'>Die Kennzeichnung der Sprache erfolgt mittels der
definierten Sprachcodes, wahlweise durch
<ul '.$addon::MARGIN0.'>
    <li>eine Erweiterung des angezeigten URLs um den voran gestellten Sprachcode
        (<code>/en/'.$addon::REWRITER_DIR.'1/...</code>) oder</li>
    <li>eine Erweiterung des angezeigten URLs um einen language-Parameter
        (<code>.../'.$addon::REWRITER_BASE.'?language=en</code>) oder</li>
    <li>eine Session-Variable <code>$_SESSION[\'language\']=\'en\'</code>,
        ein Sprachwechsel erfolgt hier mittels language-Parameter im
        Link (vergl. vorige Zeile).</li>
</ul>
Die Art der Kennzeichnung ist konfigurierbar. Bei der Standardsprache
und damit auch bei einsprachigen Installationen entfällt
sie ganz.</div>';
?>
