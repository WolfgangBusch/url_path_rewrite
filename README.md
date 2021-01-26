# url_path_rewrite
<h4>path-basiertes URL-Rewrite für Redaxo 5</h4>
<ul>
    <li>Dieses AddOn ist eine Erweiterung des Standard-Rewriters und
        nutzt den <code>Extension Point URL_REWRITE</code>.</li>
    <li>Der Artikel-URL wird automatisch in der Form
        <code>category1/category2/.../categoryN/article</code>
        generiert und bildet so den Kategorien-Pfad eines Artikels ab.
        Für die zugehörigen Kategorie- und Artikelbezeichnungen werden
        Meta Infos eingerichtet und als zusätzliche Artikelparameter
        genutzt.</li>
    <li>Die Sprache einer Seite kann wahlweise im URL oder durch eine
        Session-Variable gekennzeichnet werden.</li>
    <li>Artikel können auch über den Redaxo-Standard-URL
        <code>index.php?article_id=ID&clang=CID</code> aufgerufen
        werden.</li>
    <li>Es sind keine besonderen RewriteRules erforderlich.</li>
</ul>
