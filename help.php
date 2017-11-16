<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
*/
$dirname =REWRITER_DIR;
$basename=REWRITER_BASE;
$custurl =REWRITER_URL;
$string='
<ul style="padding-left:30px; line-height:15pt; margin-bottom:0px;">
    <li>Dieses AddOn ist eine Erweiterung des Standard-Rewriters und nutzt
        den <code>Extension Point URL_REWRITE</code>.</li>
    <li>Der Artikel-URL wird automatisch generiert in der Form
        <code>'.$custurl.'='.$dirname.'1/'.$dirname.'2/.../'.$basename.'</code>
        und bildet so den Kategorien-Pfad eines Artikels ab.
        Die zugehörigen Bezeichnungen werden als zusätzliche Meta Infos
        eingerichtet. Daher sollte das AddOn <code>metainfo</code>
        installiert sein.</li>
    <li>Die Sprache einer Seite kann wahlweise im URL oder durch eine
        Session-Variablen gekennzeichnet werden.</li>
    <li>URL und Sprachkennzeichnung identifizieren einen Artikel im Frontend
        eindeutig. Eine Erweiterung des <code>Extension Point FE_OUTPUT</code>
        ermöglicht seine Ausgabe ohne besondere RewriteRules.</li>
    <li>Seiten können auch über den Redaxo-Standard-URL
        <code>index.php?article_id=ID&clang=CID</code> aufgerufen werden.</li>
</ul>
<br/>
';
echo utf8_encode($string);
?>
