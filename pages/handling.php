<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Januar 2021
 */
#
$attr='Metadaten';
$dname='&quot;Dateiname&quot;';
$vname='&quot;Verzeichnisname&quot;';
$urlbez='Custom URL';
$stg='style="color:green;"';
$sth='style="color:green; border:solid 1px green;"';
#
$string='
<div><b>Eingabe/Aktualisierung der zusätzlichen Meta Infos:</b></div>
<ul>
    <li>'.$vname.' einer Kategorie [<code>'.REWRITER_DIR.'</code>]:
        Eingabe im Menü <code '.$stg.'><big>
        <i class="rex-icon fa-pencil-square-o"> ändern</i></big></code>
        der Kategorie.</li>
    <li>'.$dname.' eines Artikels [<code>'.REWRITER_BASE.'</code>]:
        Eingabe in den '.$attr.' des Artikels.</li>
    <li>'.$urlbez.' eines Artikels [<code>'.REWRITER_URL.'</code>]:
        Wird in jedem Kontextmenüs des Artikels automatsch neu erzeugt,
        ist also auch vor der Eingabe von Artikelinhalten schon
        vorhanden.</li>
</ul>
<div><b>Anlegen einer neuen Kategorie:</b></div>
<ul>
    <li>Im Eingabemenü der Kategorie den Button <code '.$sth.'>
        <i class="rex-icon rex-icon-add"></i></code> klicken
        (ist nur vorhanden, weil mindestens eine Meta Info
        definiert ist).</li>
    <li>In das zusätzliche Eingabefeld eingeben: '.$vname.' der
        Kategorie [<code>'.REWRITER_DIR.'</code>].<br/>
        Default-Wert: der darüber eingetragene Kategoriename
        [<code>catname</code>]
        (falls leer: Kategorie-Id [<code>id</code>]).<br/>
        Der '.$vname.' wird automatisch für alle Sprachversionen
        übernommen.</li>
    <li>Mit dem Eintritt in das Kontextmenü eines Artikels dieser
        Kategorie wird der '.$vname.' überprüft (erlaubte Zeichen,
        Eindeutigkeit) und ggf. korrigiert (automatisch für
        alle Sprachversionen), bevor er als Pfadanteil
        in den Custom URL aufgenommen wird.</li>
</ul>
<div><b>Anlegen eines neuen Artikels:</b></div>
<ul>
    <li>In den '.$attr.' in das Eingabefeld eingeben: '.$dname.'
        des Artikels [<code>'.REWRITER_BASE.'</code>].<br/>
        Default-Wert: <code>name.html</code> (normaler Artikel,
        falls leer: <code>id.html</code>) bzw. <code>index.html</code>
        (Startartikel).<br/>
        Namenserweiterung bzw. Startartikelname können auch anders
        konfiguriert werden.</li>
    <li>Mit der Aktualisierung der '.$attr.' wird der eingegebene
        Wert überprüft (erlaubte Zeichen, Eindeutigkeit), ggf.
        korrigiert und parallel für alle Sprachversionen gespeichert.</li>
    <li>Der zugehörige '.$urlbez.' [<code>'.REWRITER_URL.'</code>]
        wird zu Anfang automatisch erzeugt und angepasst, sobald
        der '.$dname.' geändert wird.</li>
</ul>
<div><b>Kopieren eines Artikels:</b></div>
<ul>
    <li>Für den kopierten Artikel werden automatisch ein '.$dname.'
        [<code>'.REWRITER_BASE.'</code>] und ein zugehöriger '.$urlbez.'
        [<code>'.REWRITER_URL.'</code>] erzeugt.</li>
</ul>
<div><b>Verschieben eines Artikels:</b></div>
<ul>
    <li>Für den verschobenen Artikel bleibt der '.$dname.'
        [<code>'.REWRITER_BASE.'</code>] erhalten.</li>
    <li>Im Falle eines Startartikels bleibt ebenso der '.$vname.'
        [<code>'.REWRITER_DIR.'</code>] der Kategorie erhalten.</li>
    <li>Der '.$urlbez.' [<code>'.REWRITER_URL.'</code>] wird automatisch
        gemäß dem neuen &quot;Pfad&quot; ersetzt.</li>
    <li>Nach dem Verschieben eines Startartikels wird der '.$urlbez.'
        jedes Kindartikels erst durch Eintritt in dessen Kontextmenü
        entsprechend angepasst.</li>
</ul>
<div><b>Umwandeln eines Artikels in einen Startartikel:</b></div>
<ul>
    <li>Mit dem Eintritt in das Kontextmenü des neuen Startartikels bzw.
        des neuen Kindartikels (und ehemaligen Startartikels) werden
        jeweils automatisch ein '.$dname.' [<code>'.REWRITER_BASE.'</code>]
        und der zugehörige '.$urlbez.' [<code>'.REWRITER_URL.'</code>]
        angelegt.</li>
</ul>
';
echo $string;
?>
