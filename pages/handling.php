<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
#
$attr="Metadaten";
$dname="&quot;Dateiname&quot;";
$vname="&quot;Verzeichnisname&quot;";
$urlbez="Custom URL";
#
$string='
<div><b>Eingabe/Aktualisierung der zus�tzlichen Meta Infos:</b></div>
<ul>
    <li>'.$vname.' einer Kategorie [<code>'.REWRITER_DIR.'</code>]:
        Eingabe im Men� <code style="color:green;">�ndern</code>
        der Kategorie.</li>
    <li>'.$dname.' eines Artikels [<code>'.REWRITER_BASE.'</code>]:
        Eingabe im Men� <code style="color:green;">'.$attr.'</code>
        des Artikels.</li>
    <li>'.$urlbez.' eines Artikels [<code>'.REWRITER_URL.'</code>]:
        Wird in jedem Kontextmen�s des Artikels automatsch neu
        erzeugt, ist also auch vor der Eingabe von Artikelinhalten
        schon vorhanden. Dabei wird auch �berpr�ft, ob das Paar
        URL/Sprachkennzeichnung eindeutig ist.</li>
</ul>
<div><b>Anlegen einer neuen Kategorie:</b></div>
<ul>
    <li>Das Men� <code style="color:green;">
        <i class="rex-icon rex-icon-add"></i></code> �ffnen (ist nur
        vorhanden, wenn Meta Infos der Kategorie definiert sind).</li>
    <li>In das Eingabefeld '.$vname.' der Kategorie
        [<code>'.REWRITER_DIR.'</code>] eingegeben.<br/>
        Default-Wert: der dar�ber eingetragene Kategoriename
        [<code>catname</code>]
        (falls leer: Kategorie-Id [<code>id</code>]).</li>
    <li>Mit dem Eintritt in das Kontextmen� eines Artikels dieser
        Kategorie wird der '.$vname.' im Elternartikel �bernommen,
        und zwar f�r alle Sprachversionen.</li>
</ul>
<div><b>Anlegen eines neuen Artikels:</b></div>
<ul>
    <li>Im Eingabefeld den '.$dname.' [<code>'.REWRITER_BASE.'</code>]
        eingeben.<br/>
        Default-Wert: <code>name.html</code> (normaler Artikel,
        falls leer: <code>id.html</code>) bzw. <code>index.html</code>
        (Startartikel).<br/>
        Namenserweiterung bzw. Startartikelname k�nnen auch anders
        konfiguriert werden.</li>
    <li>Der zugeh�rige '.$urlbez.' [<code>'.REWRITER_URL.'</code>] wird
        durch Wechsel in ein anderes Kontextmen� des Artikels (z.B.
        durch R�ckkehr in den Editiermodus) automatisch erzeugt,
        und zwar f�r alle Sprachversionen.</li>
</ul>
<div><b>Kopieren eines Artikels:</b></div>
<ul>
    <li>F�r den kopierten Artikel werden automatisch ein '.$dname.'
        [<code>'.REWRITER_BASE.'</code>] und ein zugeh�riger '.$urlbez.'
        [<code>'.REWRITER_URL.'</code>] erzeugt.</li>
</ul>
<div><b>Verschieben eines Artikels:</b></div>
<ul>
    <li>F�r den verschobenen Artikel bleibt der '.$dname.'
        [<code>'.REWRITER_BASE.'</code>] erhalten.</li>
    <li>Im Falle eines Startartikels bleibt ebenso der '.$vname.'
        [<code>'.REWRITER_DIR.'</code>] der Kategorie erhalten.</li>
    <li>Der '.$urlbez.' [<code>'.REWRITER_URL.'</code>] wird automatisch
        gem�� dem neuen &quot;Pfad&quot; ersetzt.</li>
</ul>
<div><b>Umwandeln eines Artikels in einen Startartikel:</b></div>
<ul>
    <li>Mit dem Eintritt in das Kontextmen� des neuen Startartikels bzw.
        des neuen Kindartikels (und ehemaligen Startartikels) werden
        jeweils automatisch ein '.$dname.' [<code>'.REWRITER_BASE.'</code>]
        und der zugeh�rige '.$urlbez.' [<code>'.REWRITER_URL.'</code>]
        angelegt.</li>
</ul>
';
echo utf8_encode($string);
?>
