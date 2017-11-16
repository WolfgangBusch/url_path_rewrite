# url_path_rewrite
<h3>path-basiertes URL-Rewrite für Redaxo 5</h3>
<ul>
    <li>Dieses AddOn ist eine Erweiterung des Standard-Rewriters und nutzt
        den <code>Extension Point URL_REWRITE</code>.</li>
    <li>Der Artikel-URL wird automatisch generiert in der Form
        <code>art_custom_url=cat_dirname1/cat_dirname2/.../art_basename</code>
        und bildet so den Kategorien-Pfad eines Artikels ab.
        Die zugehörigen Bezeichnungen werden als zusätzliche Meta Infos
        eingerichtet. Daher sollte das AddOn <code>metainfo</code>
        installiert sein.</li>
    <li>Die Sprache einer Seite kann wahlweise im URL oder durch eine
        Session-Variable gekennzeichnet werden.</li>
    <li>URL und Sprachkennzeichnung identifizieren einen Artikel im Frontend
        eindeutig. Eine Erweiterung des <code>Extension Point FE_OUTPUT</code>
        ermöglicht seine Ausgabe ohne besondere RewriteRules.</li>
    <li>Seiten können auch über den Redaxo-Standard-URL
        <code>index.php?article_id=ID&clang=CID</code> aufgerufen werden.</li>
</ul>
<div><b>Elemente der URL-Darstellung:</b></div>
<div>Für die Darstellung eines URLs werden
bei der Installation des AddOns die folgenden Meta Infos angelegt
(als Zeilen in der Tabelle <code>rex_metainfo_field</code> und als
Spalten in der Tabelle <code>rex_article</code>). Sie werden bei der
De-Installation nicht wieder entfernt.
<ul>
    <li><code>cat_dirname</code> : &nbsp; &quot;Verzeichnisname&quot; für jede
        Kategorie,<br/>
        der Wert wird im Menü <code style="color:green;">ändern</code>
        der Kategorie eingegeben, &nbsp;
        Default: Kategoriename (<code>catname</code>)</li>
    <li><code>art_basename</code> : &nbsp; &quot;Dateiname&quot; für jeden
        Artikel,<br/>
        der Wert wird im Menü <code style="color:green;">Metadaten</code>
        des Artikels eingegeben, &nbsp;
        Default: &quot;Artikelname.Namenserweiterung&quot;
        (<code>name.html</code>),<br/>
        für Kategorie-Startartikel wird der Artikelname durch
        &quot;index&quot; ersetzt (&quot;Dateiname&quot;: <code>index.html</code>)</li>
    <li><code>art_custom_url</code> : &nbsp;
        aus den obigen Daten generierter Artikel-URL (Custom URL, ohne
        vorangestellten &quot;/&quot;),<br/>
        ablesbar (<tt>readonly</tt>) im Eingabefeld des Menüs
        <code style="color:green;">Metadaten</code> des Artikels,<br/>
        den Wert (mit vorangestelltem &quot;/&quot;) liefert die
        Standardfunktion <code>rex_getUrl</code>,<br/>
        der Custom URL ist sprachunabhängig</li>
</ul>
Erlaubte Zeichen für die Meta Infos sind: Buchstaben, Ziffern, Punkt(.),
Minuszeichen(-), Unterstrich(_), <u>keine Umlaute</u>, <u>keine Leerzeichen</u>.
Startartikelname und Namenserweiterung können auch anders konfiguriert
werden.
</div>
<br/>
<div><b>Kennzeichnung der Sprache:</b></div>
<div style="padding-left:30px;">Die Kennzeichnung der Sprache erfolgt
mittels der definierten Sprachcodes, wahlweise durch eine Ergänzung des
Custom URL um
<ul style="padding-left:30px; margin-bottom:0px;">
    <li>den Sprachcode in der Form <code>en/cat_dirname1/...</code>
        oder</li>
    <li>einen URL-Parameter in der Form
        <code>.../art_basename?language=en</code> oder</li>
    <li>eine Session-Variable <code>$_SESSION['language']='en'</code>.
        Ein Sprachwechsel erfolgt hier mittels URL-Parameter im
        entsprechenden Link (vergl. vorige Zeile).</li>
</ul>
Die Art der Kennzeichnung ist konfigurierbar. Bei der Standardsprache
und damit auch bei einsprachigen Installationen entfällt sie ganz.</div>
