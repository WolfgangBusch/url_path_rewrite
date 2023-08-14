<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
#
$addon=$this->getPackageId();
echo '
<div><b>Eingabe/Aktualisierung der zusätzlichen MetaInfos:</b></div>
<ul>
    <li>'.$addon::VNAME.' einer Kategorie [<code>'.$addon::REWRITER_DIR.'</code>]:
        &nbsp; Eingabe im Menü &nbsp;
        <a href="#"><i class="rex-icon rex-icon-edit"> ändern</i></a> &nbsp;
        der Kategorie.</li>
    <li>'.$addon::DNAME.' eines Artikels [<code>'.$addon::REWRITER_BASE.'</code>]:
        &nbsp; Eingabe in den Metadaten des Artikels.</li>
    <li>'.$addon::URLNAME.' eines Artikels [<code>'.$addon::REWRITER_URL.'</code>]:
        &nbsp; Wird in jedem Kontextmenüs des Artikels automatsch neu erzeugt,
        ist also auch vor der Eingabe von Artikelinhalten schon
        vorhanden.</li>
</ul>
<div><b>Anlegen einer neuen Kategorie:</b></div>
<ul>
    <li>Im Eingabemenü der Kategorie den Button &nbsp;
        <a href="#"><i class="rex-icon rex-icon-add"></i></a> &nbsp;
        klicken (ist nur vorhanden, wenn mindestens eine MetaInfo
        definiert ist).</li>
    <li>In das zusätzliche Eingabefeld eingeben: &nbsp; '.$addon::VNAME.'
        der Kategorie [<code>'.$addon::REWRITER_DIR.'</code>].<br>
        Default-Wert: &nbsp; der darüber eingetragene Kategoriename
        [<code>catname</code>] (falls leer: Kategorie-Id [<code>id</code>]).<br>
        Der '.$addon::VNAME.' wird automatisch für alle Sprachversionen
        übernommen.</li>
    <li>Mit dem Eintritt in das Kontextmenü eines Artikels dieser
        Kategorie wird der '.$addon::VNAME.' überprüft (erlaubte Zeichen,
        Eindeutigkeit) und ggf. korrigiert (automatisch für
        alle Sprachversionen), bevor er als Pfadanteil
        in den '.$addon::URLNAME.' aufgenommen wird.</li>
</ul>
<div><b>Anlegen eines neuen Artikels:</b></div>
<ul>
    <li>In den Metadaten in das Eingabefeld eingeben: '.$addon::DNAME.'
        des Artikels [<code>'.$addon::REWRITER_BASE.'</code>].<br>
        Default-Wert: <code>name.html</code> (normaler Artikel,
        falls leer: <code>id.html</code>) bzw. <code>index.html</code>
        (Startartikel).<br>
        Namenserweiterung bzw. Startartikelname können auch anders
        konfiguriert werden.</li>
    <li>Mit der Aktualisierung der Metadaten wird der eingegebene
        Wert überprüft (erlaubte Zeichen, Eindeutigkeit), ggf.
        korrigiert und parallel für alle Sprachversionen gespeichert.</li>
    <li>Der zugehörige '.$addon::URLNAME.' [<code>'.$addon::REWRITER_URL.'</code>]
        wird zu Anfang automatisch erzeugt und angepasst, sobald der
        '.$addon::DNAME.' geändert wird.</li>
</ul>
<div><b>Kopieren eines Artikels:</b></div>
<ul>
    <li>Für den kopierten Artikel werden automatisch ein '.$addon::DNAME.'
        [<code>'.$addon::REWRITER_BASE.'</code>] und ein zugehöriger
        '.$addon::URLNAME.' [<code>'.$addon::REWRITER_URL.'</code>] erzeugt.</li>
</ul>
<div><b>Verschieben eines Artikels:</b></div>
<ul>
    <li>Für den verschobenen Artikel bleibt der '.$addon::DNAME.'
        [<code>'.$addon::REWRITER_BASE.'</code>] erhalten.</li>
    <li>Im Falle eines Startartikels bleibt ebenso der '.$addon::VNAME.'
        [<code>'.$addon::REWRITER_DIR.'</code>] der Kategorie erhalten.</li>
    <li>Der '.$addon::URLNAME.' [<code>'.$addon::REWRITER_URL.'</code>]
        wird automatisch gemäß dem neuen &quot;Pfad&quot; ersetzt.</li>
    <li>Nach dem Verschieben eines Startartikels wird der '.$addon::URLNAME.'
        jedes Kindartikels erst durch Eintritt in dessen Kontextmenü
        entsprechend angepasst.</li>
</ul>
<div><b>Umwandeln eines Artikels in einen Startartikel:</b></div>
<ul>
    <li>Mit dem Eintritt in das Kontextmenü des neuen Startartikels bzw.
        des neuen Kindartikels (und ehemaligen Startartikels) werden
        jeweils automatisch ein '.$addon::DNAME.'
        [<code>'.$addon::REWRITER_BASE.'</code>] und der zugehörige
        '.$addon::URLNAME.' [<code>'.$addon::REWRITER_URL.'</code>]
        angelegt.</li>
</ul>';
?>
