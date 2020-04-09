<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version April 2020
 */
#
$attr='Metadaten';
$vname='&quot;Verzeichnisname&quot;';
$dname='&quot;Dateiname&quot;';
$urlbez='Custom URL';
$stx='style="padding-left:20px;"';
$sty='style="margin-bottom:0px;"';
#
$string='
<div><b>Elemente der URL-Darstellung:</b></div>
<div '.$stx.'>Für die Darstellung eines URLs werden bei der Installation
des AddOns die folgenden Meta Infos angelegt, als Zeilen in der Tabelle
<code>rex_metainfo_field</code> und als Spalten in der Tabelle
<code>rex_article</code>. Sie werden bei der De-Installation nicht wieder
entfernt.
<ul '.$sty.'>
    <li><code>'.REWRITER_DIR.'</code> : &nbsp; '.$vname.' für jede
        Kategorie, Default: Kategoriename (<code>catname</code>)</li>
    <li><code>'.REWRITER_BASE.'</code> : &nbsp; '.$dname.' für jeden
        Artikel, Default: Artikelname mit Namenserweiterung
        (<code>name.html</code>), für Kategorie-Startartikel wird der
        Artikelname durch &quot;index&quot; ersetzt ('.$dname.':
        <code>index.html</code>)</li>
    <li><code>'.REWRITER_URL.'</code> : &nbsp; aus den obigen Daten
        generierter Artikel-URL ('.$urlbez.', ohne führenden &quot;/&quot;),
        ablesbar (<tt>readonly</tt>) in den Metadaten des Artikels,
        den Wert (mit führendem &quot;/&quot;) liefert die Standardfunktion
        <code>rex_getUrl(article_id)</code>, er ist sprach-unabhängig</li>
</ul>
Erlaubte Zeichen für die Meta Infos sind: '.url_path_config::allowed_chars().'.
Startartikelname und Namenserweiterung können auch anders konfiguriert
werden.
</div>
<br/>
<div><b>Kennzeichnung der Sprache:</b></div>
<div '.$stx.'>Die Kennzeichnung der Sprache erfolgt mittels der definierten
Sprachcodes, wahlweise durch
<ul '.$sty.'>
    <li>eine Erweiterung des Custom URL um den Sprachcode in der Form
        <code>en/'.REWRITER_DIR.'1/...</code> oder</li>
    <li>eine Erweiterung des Custom URL um einen Parameter in der Form
        <code>.../'.REWRITER_BASE.'?language=en</code> oder</li>
    <li>eine Session-Variable <code>$_SESSION[\'language\']=\'en\'</code>,
        ein Sprachwechsel erfolgt hier mittels URL-Parameter im
        entsprechenden Link (vergl. vorige Zeile).</li>
</ul>
Die Art der Kennzeichnung ist konfigurierbar. Bei der Standardsprache
und damit auch bei einsprachigen Installationen entfällt
sie ganz.</div>
';
echo $string;
?>
