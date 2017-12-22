# url_path_rewrite
<h4>Version 1.1.1</h4>
<ul>
    <li>Es werden jetzt im Artikel-Kontext keine rex_view::success-Meldungen
        mit dem art_custom_url mehr ausgegeben, sondern nur noch
        rex_view::warning-Meldungen.</li>
</ul>
<h4>Version 1.1.0</h4>
<ul>
    <li>Links auf Kategorie-Startartikel in der Form ".../" f�hren jetzt nicht
        mehr auf den NotFound-Artikel, sondern werden wie der entsprechende
        Link in der Form ".../index.html" angezeigt.</li>
</ul>
<h4>Version 1.0.5</h4>
<ul>
    <li>Der englische Sprachzweig ist jetzt richtig angelegt (Datei
        en_gb.lang im Ordner lang). Eine �bersetzung der gesamten
        Beschreibung fehlt weiterhin.</li>
</ul>
<h4>Version 1.0.4</h4>
<ul>
    <li>Die Software ist nat�rlich gem�� MIT-Lizenz frei nutzbar, nachlesbar
        in der zus�tzlichen Datei LICENSE.md.</li>
    <li>Die ungenutzte Datei uninstall.php entf�llt jetzt.</li>
    <li>Der englische Sprachzweig ist angelegt (Datei en_en.lang im Ordner
        lang). Eine �bersetzung der gesamten Beschreibung fehlt noch.</li>
</ul>
<h4>Version 1.0.3</h4>
<ul>
    <li>2 neue Utility-Funktionen zur Nutzung im Frontend: ein Men� zur
        Sprachauswahl im aktuellen Artikel und ein Auswahlmen� zum Wechsel
        zwischen konfigurierter URL-Form und Redaxo-Standard-URL, wobei
        die gew�hlte Sprache beibehalten wird.</li>
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
    <li>Jetzt sind nebeneinander mehrere Dateinamenserweiterungen m�glich,
        also z.B. nicht nur .html, sondern daneben auch .php oder .css oder ...</li>
</ul>
