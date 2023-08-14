# url_path_rewrite
<h4>path-basiertes URL-Rewrite für Redaxo 5</h4>

<div>Dieses AddOn ist eine Erweiterung des Standard-Rewriters und
nutzt den <code>Extension Point URL_REWRITE</code>.</div>

<div>Der Artikel-URL wird automatisch in der Form
<code>category1/category2/.../categoryN/article</code> generiert
und bildet so den Kategorien-Pfad eines Artikels ab. Für die
zugehörigen Kategorie- und Artikelbezeichnungen werden MetaInfos
eingerichtet und als zusätzliche Artikelparameter genutzt.</div>

<div>Die Sprache einer Seite kann wahlweise im URL oder durch eine
Session-Variable gekennzeichnet werden.</div>

<div>Artikel können auch über den Redaxo-Standard-URL
<code>index.php?article_id=ID&clang=CID</code> aufgerufen werden.</div>

<div>In der .htaccess-Datei sind keine besonderen RewriteRules
erforderlich.</div>