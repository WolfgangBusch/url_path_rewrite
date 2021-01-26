<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Januar 2021
 */
#
class url_rewrite {
#
# --- boot.php functions
#     set_current()                     (*) identischer Code wie bei url_simple_rewrite
#        is_normurl()                   (*)
#        get_current_clangid($req_url)
#        get_current_artid($req_url,$clang_id)
# --- rewrite Struktur functions
#     rewrite($ep)                      (*)
#        set_url($article)
#        get_url($article)
#           is_normurl()                (*)
#           mode_url_clang($url,$clang_id)
# --- in set_url($article) aufgerufene functions
#           get_newest_dirname($article)
#           proof_name($proofstr,$param,$article)
#              unique_url($article,$metainfo,$value)
#              url_path_config::allowed_name($name)
#              url_path_config::allowed_chars()
#           mode_url_clang($url,$clang_id)
#
# --- boot.php functions
public static function set_current() {
   #   Setzen der Id des anzuzeigenden Artikels und der zugehoerigen Sprache
   #   in der boot.php. Die Daten werden aus $_SERVER['REQUEST_URI'] ermittelt.
   #   Wird kein zugehoeriger Artikel gefunden oder ist er offline, werden
   #   stattdessen die Daten des NotFound-Artikels gesetzt.
   #   benutzte functions:
   #      self::is_normurl()
   #      self::get_current_clangid($req_url)
   #      self::get_current_artid($req_url,$clang_id)
   #
   if(!self::is_normurl()):
     #     $_SERVER['REQUEST_URI'] liefert Custom URL (in boot.php ohne fuehrenden '/')
     $arr=explode('?',substr($_SERVER['REQUEST_URI'],1));
     $req_url=$arr[0];
     $clang_id=self::get_current_clangid($req_url);
     $art_id  =self::get_current_artid($req_url,$clang_id);
     else:
     #     $_SERVER['REQUEST_URI'] liefert Redaxo-Standard-URL
     $clang_id=intval(rex_get('clang'));
     if(!rex_clang::exists($clang_id)) $clang_id=rex_clang::getCurrentId();
     $art_id=intval(rex_get('article_id'));
     $article=rex_article::get($art_id,$clang_id);
     if($article==NULL):
       $art_id=rex_article::getNotfoundArticleId();
       else:
       if(!$article->isOnline()) $art_id=rex_article::getNotfoundArticleId();
       endif;
     endif;
   #
   # --- rex_article::getCurrentId() und rex_clang::getCurrentId() setzen
   rex_addon::get('structure')->setProperty('article_id',$art_id);
   rex_clang::setCurrentId($clang_id);
   }
public static function is_normurl() {
   #   Hat die aktuelle Seite einen Redaxo-Standard-URL ?
   #
   $arr=explode('?',substr($_SERVER['REQUEST_URI'],1));
   $url=$arr[0];
   $indphp=substr(rex_url::frontendController(),2);
   $urlend=substr($url,intval(strlen($url)-strlen($indphp)));
   $aid=rex_get('article_id');
   if($urlend==$indphp and !empty($aid)) return TRUE;
   return FALSE;
   }
public static function get_current_clangid($req_url) {
   #   Rueckgabe der Sprach-Id zum aufgerufenen konfigurierten URL.
   #   Default-Rueckgabe: 1
   #   $req_url           aufgerufener URL ohne fuehrenden '/' und ohne Parameter,
   #                      entsprechend $_SERVER['REQUEST_URI'] in boot.php
   #
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $mode   =rex_config::get(REWRITER,CLANG_MODE);
   #
   # --- Sprach-Code bestimmen
   $clang_val='';
   if(!empty(rex_get($parlang))) $clang_val=rex_get($parlang);
   #
   if($mode==1):
     $clang_code=$clang_val;
     if(empty($clang_val)):
       $arr=explode('/',$req_url);
       $clang_code=$arr[0];
       endif;
     endif;
   #
   if($mode==2):
     $clang_code=$clang_val;
     endif;
   #
   if($mode==3):
     $clang_code=$clang_val;
     if(rex_clang::count()>1):
       if(session_status()!=PHP_SESSION_ACTIVE) session_start();
       if(!empty($clang_code)):
         #     Session-Variable entsprechend neu setzen
         $_SESSION[$parlang]=$clang_code;
         else:
         #     Sprach-Code aus SESSION-Variabler bestimmen
         if(!empty($_SESSION[$parlang])):
           $clang_code=$_SESSION[$parlang];
           else:
           #     Sprach-Code und SESSION-Variable auf Standardsprache setzen
           $clang_code=rex_clang::get(1)->getCode();
           $_SESSION[$parlang]=$clang_code;
           endif;
         endif;
       endif;
     endif;
   #
   # --- Sprach-Id aus dem Sprach-Code bestimmen
   if(empty($clang_code)) return 1;
   foreach(rex_clang::getAll() as $key=>$lang):
          $cid=$lang->getId();
          $code=rex_clang::get($cid)->getCode();
          if($code==$clang_code) return $cid;
          endforeach;
   return 1;
   }
public static function get_current_artid($req_url,$clang_id) {
   #   Rueckgabe der Artikel-Id zum aufgerufenen URL ($_SERVER['REQUEST_URI']).
   #   Falls der Artikel offline ist oder nicht gefunden wird, wird stattdessen
   #   die Artikel-Id des Notfound-Artikels zurueck gegeben.
   #   Links auf Kategorie-Startartikel werden auch gefunden,
   #   wenn sie nicht mit '/index.html', sondern nur mit '/' enden.
   #   $req_url           aufgerufener URL ohne fuehrenden '/',
   #                      ohne fuehrendes 'en/' und ohne Parameter
   #   $clang_id          Sprach-Id des Artikels
   #
   $url=$req_url;
   #
   # --- ggf. URL um Sprach-Code erweitern ($mode=1)
   $mode=rex_config::get(REWRITER,CLANG_MODE);
   if($mode==1 and $clang_id>1):
     $clang_code=rex_clang::get($clang_id)->getCode();
     $url=substr($url,strlen($clang_code)+1);
     endif;
   #
   # --- Links auf Startartikel endend mit '/' ergaenzt zu '/index.html'
   if(substr($url,strlen($url)-1)=='/' or empty($url)):
     $ext=rex_config::get(REWRITER,DEFAULT_EXTENSION);
     $brr=explode(' ',$ext);
     $ext=$brr[0];
     if(!empty($ext)) $ext='.'.$ext;
     $url=$url.rex_config::get(REWRITER,DEFAULT_STARTNAME).$ext;
     endif;
   #
   # --- Artikel-Id aus rex_article, ermittelt aus (art_custom_url,clang_id)
   $sql=rex_sql::factory();
   $where=REWRITER_URL.'=\''.$url.'\' AND clang_id='.$clang_id;
   $query='SELECT * FROM rex_article WHERE '.$where;
   $rows=$sql->getArray($query);
   if(count($rows)>0 and $rows[0]['status']>0):
     $art_id=$rows[0]['id'];
     else:
     $art_id=rex_article::getNotfoundArticleId();
     endif;
   return $art_id;
   }
#
# --- rewrite Struktur functions
public static function rewrite($ep) {
   #   Rueckgabe eines URLs im konfigurierten Format (Custom URL mit fuehrendem '/'):
   #   - im Backend-Fall wird der URL im Content-Kontext des Artikels (edit,
   #     metainfo, functions) bestimmt. Er erscheint im Browser-Adressfeld.
   #   - im Frontend-Fall liefert der URL den Rueckgabewert der function rex_getUrl().
   #   $ep                  Objekt vom Typ rex_extension_point
   #   benutzte functions:
   #      self::set_url($article)
   #      self::get_url($article)
   #
   $par=$ep->getParams();
   $art_id  =$par['id'];     // Artikel-Id, alternativ: $ep->getParam('id')
   $clang_id=$par['clang'];  // Sprach-Id,  alternativ: $ep->getParam('clang')
   #
   # --- Aus Artikel-Id und Sprach-Id wird das Artikel-Objekt gewonnen.
   $article=rex_article::get($art_id,$clang_id);
   if($article==NULL) $article=rex_article::getNotfoundArticle($clang_id);
   #
   if(rex::isBackend()):
     #     Backend: Definieren und ggf. Speichern des Wunsch-URLs
     $url='/'.self::set_url($article);
     else:
     #     Frontend: Liefert den Wert der function rex_getUrl(...)
     $url='/'.self::get_url($article);
     endif;
   return $url;
   }
public static function set_url($article) {
   #   Rueckgabe des Custom URLs in der konfigurierten Form ohne fuehrenden '/'
   #   (auch im Falle des Standard-URLs). Alle dafuer benoetigten Artikel-Parameter
   #   werden aus dem Artikel-Objekt genommen.
   #   $article           Artikel-Objekt
   #   Der URL wird als Artikel-MetaInfo (REWRITER_URL) in der Tabelle
   #   rex_article abgelegt. Anschliessend wird der Artikel-Cache geloescht.
   #   Naehere Beschreibung:
   #   Die folgenden MetaInfo-Werte des Artikels werden in der Redaxo-Datenbank abgelegt,
   #   als Spalten der Tabelle rex_article und als Zeilen der Tabelle rex_metainfo_field.
   #      cat_dirname     Bezeichnung einer Kategorie (Wert der Konstante REWRITER_DIR):
   #                      = leer, falls der Artikel kein Startartikel ist
   #                      = Kategorie-Id, falls leer (bei einem Startartikel)
   #      art_basename    Bezeichnung des 'Dateinamens' eines Artikels
   #                      (Wert der Konstante REWRITER_BASE)
   #                      - bei einem Startartikel auf 'index.html' gesetzt
   #                      - falls leer, auf $art_id.html gesetzt
   #                      - bleibt erhalten, falls schon gesetzt
   #      art_custom_url  Custom URL eines Artikels in der Form 'aaa/bbb/ccc/ddd.html'
   #                      (Wert der Konstante REWRITER_URL)
   #                      Der Pfad wird aus den Verzeichnisnamen cat_dirname
   #                      aller Eltern zusammengesetzt und mit dem Dateinamen
   #                      art_basename des aktuellen Artikels abgeschlossen.
   #   benutzte functions:
   #      self::get_newest_dirname($article)
   #      self::proof_name($proofstr,$param,$article)
   #      self::mode_url_clang($url,$clang_id)
   #
   # --- Artikel-Parameter auslesen
   $art_id  =$article->getId();
   $clang_id=$article->getClang();
   $path    =$article->getPath();
   $par_id  =$article->getParentId();
   $basename=$article->getValue(REWRITER_BASE);
   if($article->isStartArticle()) $par_id=$art_id;
   #
   # --- Ueberpruefung des art_basename
   $arr=self::proof_name($basename,'name',$article);
   $baseneu=$arr[0];
   $mesb=$arr[1];
   #
   # --- Teil 1 des URLs aus dem Pfad zusammensetzen
   $arr=explode('|',$path);
   $url='';
   $dirname='';
   $mesd='';
   for($i=1;$i<count($arr)-2;$i=$i+1):
      $partpath=$arr[$i+1];
      $artic=rex_article::get($partpath);
      $dirname=$artic->getValue(REWRITER_DIR);
      # --- Ueberpruefung letztes (neuestes) cat_dirname
      if($i==count($arr)-3):
        if($par_id==$art_id):
          #     evtl. frisch gesetzten cat_dirname erwischen:
          $dirname=self::get_newest_dirname($article);
          $arr=self::proof_name($dirname,'catname',$article);
          else:
          if($par_id>0):
            $par_art=rex_article::get($par_id,$clang_id);
            $arr=self::proof_name($dirname,'catname',$par_art);
            endif;
          endif;
        $dirname=$arr[0];
        $mesd=$arr[1];
        endif;
      $url=$url.$dirname.'/';
      endfor;
   #
   # --- art_basename an den URL anfuegen
   $url=$url.$baseneu;
   #
   # --- Zusammenfassung der Warnungen
   $warning=$mesd;
   if(!empty($warning) and !empty($mesb)) $warning=$warning.'<br/>';
   if(!empty($mesb)) $warning=$warning.$mesb;
   #
   # --- Datenbank-UPDATEs
   $sql=rex_sql::factory();
   $quu='UPDATE rex_article SET '.REWRITER_URL. '=\''.$url.'\'     WHERE id='.$art_id;
   $qub='UPDATE rex_article SET '.REWRITER_BASE.'=\''.$baseneu.'\' WHERE id='.$art_id;
   $qud='UPDATE rex_article SET '.REWRITER_DIR. '=\''.$dirname.'\' WHERE id='.$par_id;
   $sql->setQuery($quu);
   $sql->setQuery($qub);
   if($par_id==$art_id) $sql->setQuery($qud);
   #
   # --- Artikel-Cache loeschen
   rex_article_cache::delete($art_id);
   if($par_id!=$art_id) rex_article_cache::delete($par_id);
   #
   # --- Sprach-Code ergaenzen
   $url=self::mode_url_clang($url,$clang_id);
   #
   # --- Warnungen/Erfolgsmeldungen ausgeben
   if(!empty($warning)) echo rex_view::warning($warning);
   return $url;
   }
public static function get_url($article) {
   #   Rueckgabe des URLs eines Artikels ohne fuehrenden '/' im Frontend-Fall.
   #   Dieser Wert liefert der function rex_getUrl(...) den Ergebniswert.
   #   $article           Artikel-Objekt
   #   Im Falle des Wunsch-URLs wird der Wert aus der Spalte REWRITER_URL
   #   der Tabelle rex_article ausgelesen.
   #   benutzte functions:
   #      self::is_normurl()
   #      self::mode_url_clang($url,$clang_id)
   #
   $art_id  =$article->getId();
   $clang_id=$article->getClang();
   if(self::is_normurl()):
     #     Redaxo-Standard-URL
     if(count(rex_clang::getAll())>1):
       $str='&clang='.$article->getClang();
       else:
       $str='';
       endif;
     $url='index.php?article_id='.$art_id.$str;
     else:
     #     Wunsch-URL
     $sql=rex_sql::factory();
     $where='id='.$art_id.' AND clang_id='.$clang_id;
     $query='SELECT * FROM rex_article WHERE '.$where;
     $rows=$sql->getArray($query);
     $url=$rows[0][REWRITER_URL];
     $url=self::mode_url_clang($url,$clang_id);
     endif;
   return $url;
   }
#
# --- in set_url($article) aufgerufene functions
public static function get_newest_dirname($article) {
   #   Rueckgabe des zuletzt erzeugten Werts fuer cat_dirname eines
   #   (Start-)Artikels zu einer Sprachversion; damit wird auch der Wert
   #   gefunden, der noch nicht in den art_custom_url eingebaut ist
   #   (leider ist das Update-Date fuer alle Sprachversionen immer gleich!).
   #   $article           gegebener (Start-)Artikel
   #
   # --- Daten der eingegebenen Sprachversion eines Artikels
   $art_id=$article->getId();
   $dirnam=$article->getValue(REWRITER_DIR);
   $dirurl=$article->getValue(REWRITER_URL);
   $arr=explode('/',$dirurl);
   #
   # --- Vergleich mit allen anderen Sprachversionen des Artikels
   $sql=rex_sql::factory();
   $query='SELECT * FROM rex_article WHERE id='.$art_id;
   $arts=$sql->getArray($query);
   for($i=0;$i<count($arts);$i=$i+1):
      $dir=$arts[$i][REWRITER_DIR];
      $url=$arts[$i][REWRITER_URL];
      $arr=explode('/',$url);
      if(count($arr)>=2):
        $par=$arr[count($arr)-2];
        if($par!=$dir) $dirnam=$dir;
        endif;
      endfor;
   return $dirnam;
   }
public static function proof_name($proofstr,$param,$article) {
   #   Ueberpruefung und Korrektur von art_basename bzw. cat_dirname:
   #   - nicht-leerer Name
   #   - nur erlaubte Zeichen im Namen
   #   - eindeutiger Name innerhalb der Kategorie
   #   zusaetzlich bei Korrektur von art_basename:
   #   - fehlende/falsche Namenserweiterung
   #   - art_basename='index.html' bei Startartikeln
   #   Rueckgabe eines Arrays, bestehend aus dem ggf. korrigierten
   #   $proofstr und dem ggf. nicht leeren Warn-String. Der Warn-String
   #   ist genau dann nicht-leer, wenn eingegebener und zurueck gegebener
   #   String verschieden sind.
   #   $proofstr          Wert von REWRITER_BASE bzw. REWRITER_DIR
   #   $param             ='name' bzw. 'catname' (Artikel-Parameter)
   #   $article           Artikel
   #   benutzte functions:
   #      self::unique_url($article,$metainfo,$value)
   #      url_path_config::allowed_name($name)
   #      url_path_config::allowed_chars()
   #
   $art_id=$article->getId();
   $start =$article->isStartArticle();
   $arr=explode(' ',$article->getValue($param));
   $artname=$arr[0];   // Leerzeichen sind ohnehin nicht erlaubt
   #
   $ext='';
   if($param=='name'):
     #
     # --- schon vorhandene Dateinamenserweiterung
     $brr=explode('.',$proofstr);
     $nz=count($brr)-1;
     $oldext=$brr[$nz];
     if($nz>=1):
       $oldext=$brr[$nz];
       else:
       $oldext='';
       endif;
     #
     # --- korrekte vorhandene bzw. erste vorgeschriebene Dateinamenserweiterung
     $defext=rex_config::get(REWRITER,DEFAULT_EXTENSION);
     $arrext=explode(' ',$defext);
     $ext='';
     $ext0='';
     if(!empty($arrext[0])):
       $ext0='.'.$arrext[0];
       for($i=0;$i<count($arrext);$i=$i+1):
          if($oldext==$arrext[$i]):
            $ext='.'.$oldext;
            break;
            endif;
          endfor;
       if(empty($ext)) $ext='.'.$arrext[0];
       endif;
     endif;
   #
   # --- Hilfsvariable
   if($param=='name'):
     $legname='&quot;Dateiname&quot;';
     $lername='Namensstamm';
     $metname=REWRITER_BASE;
     else:
     $legname='&quot;Verzeichnisname&quot;';
     $lername='Name';
     $metname=REWRITER_DIR;
     endif;
   $txtid=' (Artikel-Id='.$art_id.')';
   #
   $mes='';
   $string=$proofstr;
   #
   # --- art_basename=index.html bei Startartikeln
   if($param=='name' and $start==1):
     $std=rex_config::get(REWRITER,DEFAULT_STARTNAME).$ext0;
     if($string!=$std)
       $mes='Startartikel, '.$legname.' vorgeschrieben: <code>'.$std.'</code>';
     return array($std,$mes);
     endif;
   #
   # --- art_basename: fehlende/falsche Namenserweiterung
   if($param=='name' and $start!=1):
     $arr=explode('.',$string);
     $nz=count($arr)-1;
     $korr=FALSE;
     if($arr[$nz]!=substr($ext,1)):
       $string=$arr[0];
       if($nz>1) for($i=1;$i<$nz;$i=$i+1) $string=$string.'.'.$arr[$i];
         $string=$string.$ext;
         if(!empty($ext)) $korr=TRUE;
       endif;
     if($korr):
       $kortxt='falsche';
       if($nz<=0) $kortxt='leere';
       if(!empty($mes)) $mes=$mes.'<br/>';
       $mes=$mes.'<code>'.$kortxt.' Namenserweiterung</code> korrigiert, '.
          $legname.': <code>'.$string.'</code>';
       endif;
     endif;
   #
   # --- leerer Namensstamm
   $arr=explode('.',$string);
   if(empty($arr[0])):
     $string=$artname.$ext;
     if(!empty($mes)) $mes=$mes.'<br/>';
     $mes=$mes.'<code>leerer '.$lername.'</code> mit Default-Wert belegt: '.
        $legname.' <code>'.$string.'</code>';
     endif;
   #
   # --- nicht erlaubte Zeichen im Namen
   if(!url_path_config::allowed_name($string)):
     if(!empty($mes)) $mes=$mes.'<br/>';
     $text='<code>nicht erlaubte Zeichen</code> in '.$legname;
     $mes=$mes.$text.' <code>'.$string.'</code>, korrigiert zu: ';
     $string=$artname.$ext;
     $mes=$mes.'<code>'.$string.'</code>';
     if(!url_path_config::allowed_name($string)):
       $mes=$mes.'<br/>'.$text.' <code>'.$string.'</code>, korrigiert zu: ';
       $string=strval($art_id).$ext;
       $mes=$mes.'<code>'.$string.'</code>'.$txtid;
       endif;
     $mes=$mes.'<br/> &nbsp; &nbsp; erlaubte Zeichen: '.
        '<span style="color:green;">'.url_path_config::allowed_chars().'</span>';
     endif;
   #
   # --- Name nicht eindeutig
   if(!self::unique_url($article,$metname,$string)):
     if(!empty($mes)) $mes=$mes.'<br/>';
     $text=$legname.' <code>schon vergeben</code>: ';
     $mesneu=$text.'<code>'.$string.'</code>, ersetzt durch: ';
     $string=$artname.$ext;
     $mesneu=$mesneu.'<code>'.$string.'</code>';
     if(!self::unique_url($article,$metname,$string)):
       $mes=$mes.$text.'<code>'.$string.'</code>, ersetzt durch: ';
       $string=strval($art_id).$ext;
       $mes=$mes.'<code>'.$string.'</code>'.$txtid;
       else:
       $mes=$mesneu;
       endif;
     endif;
   return array($string,$mes);
   }
public static function unique_url($article,$metainfo,$value) {
   #   Pruefen, ob
   #   - der art_basename eines Artikels in seiner Kategorie unique ist oder
   #   - der cat_dirname einer Kategorie in der Eltern-Kategorie unique ist.
   #   $article           aktueller Artikel (Objekt)
   #   $metainfo          MetaInfo-Name (REWRITER_DIR oder REWRITER_BASE)
   #   $value             MetaInfo-Wert
   #   Rueckgabe:         TRUE/FALSE
   #   unique bedeutet dabei:
   #      Im betrachteten Bereich gibt es nur genau einen Artikel mit
   #      vorgegebenem $metainfo-Wert und vorgegebener Sprach-Id, sodass aus
   #      $metainfo-Wert und Sprach-Id genau ein zugehoeriger Artikel bestimmt
   #      werden kann. Da die verschiedenen Sprach-Versionen eines Artikels
   #      dieselbe Artikel-Id haben, bedeutet das:
   #   nicht unique ist ein Artikel (vergl. die entsprechende Abfrage unten),
   #      wenn ein anderer Artikel (mit anderer Artikel-Id) dieselbe Sprach-Id
   #      und denselben $metainfo-Wert hat.
   #
   $art_id  =$article->getId();
   if($art_id==rex_article::getSiteStartArticleId()) return TRUE;
   $clang_id=$article->getClang();
   $par_id  =$article->getParentId();
   #     Geschwister von 'Homepage' haben die Kategorie-Id 0
   if($par_id<=0) $par_id=rex_article::getSiteStartArticleId();
   #
   # --- alle Geschwister-Kategorien/-Artikel
   if($article->isStartArticle()):
     $articles=rex_category::get($par_id)->getChildren();
     else:
     $articles=rex_category::get($par_id)->getArticles();
     endif;
   #
   # --- Ueberpruefung des MetaInfo-Wertes
   foreach($articles as $art):
          $val=$art->getValue($metainfo);
          $id=$art->getId();
          $cid=$art->getClang();
          if($val==$value and $id!=$art_id and $cid==$clang_id) return FALSE;
          endforeach;
   return TRUE;
   }
#
# --- in set_url($article) und get_url($article) aufgerufene functions
public static function mode_url_clang($url,$clang_id) {
   #   Rueckgabe eines URLs (ohne fuehrenden '/'), der um die noetige
   #   Sprachkennzeichnung ergaenzt ist, inkl. Setzung der Session-Variable
   #   $url               Custom URL gemaess Tabelle rex_article
   #   $clang_id          Sprach-Id
   #
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $mode   =rex_config::get(REWRITER,CLANG_MODE);
   $urladd=$url;
   if($mode==1 and $clang_id>1)
     $urladd=rex_clang::get($clang_id)->getCode().'/'.$url;
   if($mode==2 and $clang_id>1)
     $urladd=$url.'?'.$parlang.'='.rex_clang::get($clang_id)->getCode();
   if($mode==3 and count(rex_clang::getAll())>1):
     if(session_status()!=PHP_SESSION_ACTIVE) session_start();
     $_SESSION[$parlang]=rex_clang::get($clang_id)->getCode();
     endif;
   return $urladd;
   }
}
