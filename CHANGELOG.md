# url_path_rewrite
<h4>Version 2.3</h4>
<ul>
    <li>Die Dateien im Ordner lib und die Klassennamen darin sind umbenannt.</li>
    <li>Konstanten werden nicht mehr per 'define(...)' vereinbart, sondern
        als Klassen-Konstanten definiert.</li>
    <li>Unter bestimmten Bedingungen scheiterte die Installationsprozedur.
        Der Fehler ist behoben.</li>
</ul>
<h4>Version 2.2</h4>
<ul>
    <li>Im Quellcode sind jetzt einige überflüssige Teile entfernt.
        U.a. ist der Ordner functions entfallen. Die dort enthaltenen
        Funktionen sind für das AddOn nicht notwendig.</li>
</ul>
<h4>Version 2.1</h4>
<ul>
    <li>Aus systematischen Gründen wurde wieder eine Datei 'help.php' eingefügt.</li>
</ul>
<h4>Version 2.0</h4>
<ul>
    <li>Der Code ist vollständig überarbeitet und mit 'error_reporting(E_ALL);'
        überprüft.</li>
    <li>Der Extension Point FE_OUTPUT wird nicht benötigt und daher nicht
        mehr benutzt.</li>
    <li>Der gesamte Source-Code ist auf UTF-8 umgestellt.</li>
    <li>Alle im AddOn verwendeten Functions sind auf zwei Klassen im Ordner
        lib verteilt. Hinzu kommen zwei Utility-Funktionen im Ordner functions.</li>
</ul>
<h4>Version 1.1.1</h4>
<ul>
    <li>Es werden jetzt im Artikel-Kontext keine rex_view::success-Meldungen
        mit dem art_custom_url mehr ausgegeben, sondern nur noch
        rex_view::warning-Meldungen.</li>
</ul>
<h4>Version 1.1.0</h4>
<ul>
    <li>Links auf Kategorie-Startartikel in der Form ".../" führen jetzt nicht
        mehr auf den NotFound-Artikel, sondern werden wie der entsprechende
        Link in der Form ".../index.html" angezeigt.</li>
</ul>
<h4>Version 1.0.5</h4>
<ul>
    <li>Der englische Sprachzweig ist jetzt richtig angelegt (Datei
        en_gb.lang im Ordner lang). Eine Übersetzung der gesamten
        Beschreibung fehlt weiterhin.</li>
</ul>
<h4>Version 1.0.4</h4>
<ul>
    <li>Die Software ist natürlich gemäß MIT-Lizenz frei nutzbar, nachlesbar
        in der zusätzlichen Datei LICENSE.md.</li>
    <li>Die ungenutzte Datei uninstall.php entfällt jetzt.</li>
    <li>Der englische Sprachzweig ist angelegt (Datei en_en.lang im Ordner
        lang). Eine Übersetzung der gesamten Beschreibung fehlt noch.</li>
</ul>
<h4>Version 1.0.3</h4>
<ul>
    <li>2 neue Utility-Funktionen zur Nutzung im Frontend: ein Menü zur
        Sprachauswahl im aktuellen Artikel und ein Auswahlmenü zum Wechsel
        zwischen konfigurierter URL-Form und Redaxo-Standard-URL, wobei
        die gewählte Sprache beibehalten wird.</li>
</ul>
<h4>Version 1.0.2</h4>
<ul>
    <li>An etlichen Stellen werden jetzt anstelle der Methode getValue("value") die
        Redaxo 5-spezifischen Methoden getClang(), getParentId(), IsStartArticle(),
        getTemplateId(), IsStartArticle() eingesetzt.</li>
</ul>
<h4>Version 1.0.1</h4>
<ul>
    <li>Bei leerer Dateinamenserweiterung wird nicht jedesmal wieder eine Warnung
        ausgegeben. Das AddOn kann ja ohne Dateinamenserweiterung konfiguriert sein.</li>
    <li>Jetzt sind nebeneinander mehrere Dateinamenserweiterungen möglich,
        also z.B. nicht nur .html, sondern daneben auch .php oder .css oder ...</li>
</ul>
