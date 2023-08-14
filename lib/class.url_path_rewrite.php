<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
#
class url_path_rewrite {
#
# --- URL-Typ feststellen
#     is_normurl()                      (*) identischer Code wie bei url_simple_rewrite
# --- boot.php functions
#     set_current()                     (*) identischer Code wie bei url_simple_rewrite
#        is_normurl()                   (*)
#        get_current_clangid($req_url)
#        get_current_artid($req_url,$clang_id)
# --- rewrite Struktur functions
#     rewrite($ep)                      (*) identischer Code wie bei url_simple_rewrite
#        get_url($article)
#           is_normurl()                (*)
#           mode_url_clang($url,$clang_id)
#        set_url($article)
#           get_newest_dirname($article)
#           proof_name($proofstr,$param,$article)
#              unique_url($article,$metainfo,$value)
#              allowed_name($name)
#              allowed_chars()
#           mode_url_clang($url,$clang_id)
#
# --------------------------- Konstanten
const REWRITER         =__CLASS__;                             // Package-Id
const REWRITER_URL     ='art_custom_url';                      // MetaInfo
const REWRITER_BASE    ='art_basename';                        // MetaInfo
const REWRITER_DIR     ='cat_dirname';                         // MetaInfo
const DEFAULT_EXTENSION='default_extension';                   // Konfiguration
const DEFAULT_STARTNAME='default_startname';                   // Konfiguration
const CLANG_PARAMETER  ='clang_parameter';                     // Konfiguration
const CLANG_VALUE      ='language';                            // Konfiguration
const CLANG_MODE       ='clang_mode';                          // Konfiguration
const VNAME            ='&quot;Verzeichnisname&quot;';         // Beschreibung
const DNAME            ='&quot;Dateiname&quot;';               // Beschreibung
const URLNAME          ='&quot;Custom URL&quot;';              // Beschreibung
const INDENT           ='style="padding-left:20px;"';          // Style
const MARGIN0          ='style="margin:0;"';                   // Style
const INPUT_WIDTH      ='style="width:150px;"';                // Style
const BG_GREY          ='style="background-color:lightgrey;"'; // Style
const BG_INHERIT       ='style="background-color:inherit;"';   // Style
#
# --------------------------- URL-Typ feststellen
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
#
# --------------------------- boot.php functions
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
public static function get_current_clangid($req_url) {
   #   Rueckgabe der Sprach-Id zum aufgerufenen konfigurierten URL.
   #   Default-Rueckgabe: 1
   #   $req_url           aufgerufener URL ohne fuehrenden '/' und ohne Parameter,
   #                      entsprechend $_SERVER['REQUEST_URI'] in boot.php
   #
   $parlang=rex_config::get(self::REWRITER,self::CLANG_PARAMETER);
   $mode   =rex_config::get(self::REWRITER,self::CLANG_MODE);
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
   #   die Artikel-Id des Notfound-Artikels zurueck gegeben. Links auf
   #   Kategorie-Startartikel werden auch gefunden, wenn sie nicht mit
   #   '/index.html', sondern nur mit '/' enden.
   #   $req_url           aufgerufener URL ohne fuehrenden '/',
   #                      ohne fuehrendes 'en/' und ohne Parameter
   #   $clang_id          Sprach-Id des Artikels
   #
   $url=$req_url;
   #
   # --- ggf. URL um Sprach-Code erweitern ($mode=1)
   $mode=rex_config::get(self::REWRITER,self::CLANG_MODE);
   if($mode==1 and $clang_id>1):
     $clang_code=rex_clang::get($clang_id)->getCode();
     $url=substr($url,strlen($clang_code)+1);
     endif;
   #
   # --- Links auf Startartikel endend mit '/' ergaenzt zu '/index.html'
   if(substr($url,strlen($url)-1)=='/' or empty($url)):
     $ext=rex_config::get(self::REWRITER,self::DEFAULT_EXTENSION);
     $brr=explode(' ',$ext);
     $ext=$brr[0];
     if(!empty($ext)) $ext='.'.$ext;
     $url=$url.rex_config::get(self::REWRITER,self::DEFAULT_STARTNAME).$ext;
     endif;
   #
   # --- Artikel-Id aus rex_article, ermittelt aus (art_custom_url,clang_id)
   $sql=rex_sql::factory();
   $where=self::REWRITER_URL.'=\''.$url.'\' AND clang_id='.$clang_id;
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
# --------------------------- rewrite Struktur functions
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
public static function get_url($article) {
   #   Rueckgabe des URLs eines Artikels ohne fuehrenden '/' im Frontend-Fall.
   #   Dieser Wert liefert der function rex_getUrl(...) den Ergebniswert.
   #   $article           Artikel-Objekt
   #   Im Falle des Wunsch-URLs wird der Wert aus der Spalte self::REWRITER_URL
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
     $url=$rows[0][self::REWRITER_URL];
     $url=self::mode_url_clang($url,$clang_id);
     endif;
   return $url;
   }
public static function mode_url_clang($url,$clang_id) {
   #   Rueckgabe eines URLs (ohne fuehrenden '/'), der um die noetige
   #   Sprachkennzeichnung ergaenzt ist, ggf. inkl. Setzung der entsprechenden
   #   Session-Variable.
   #   $url               Custom URL gemaess Tabelle rex_article (ohne fuehrenden '/')
   #   $clang_id          Sprach-Id
   #
   $parlang=rex_config::get(self::REWRITER,self::CLANG_PARAMETER);
   $mode   =rex_config::get(self::REWRITER,self::CLANG_MODE);
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
public static function set_url($article) {
   #   Rueckgabe des Custom URLs in der konfigurierten Form ohne fuehrenden '/'
   #   (auch im Falle des Standard-URLs). Ausser im Falle des Standard-URLs werden
   #   zusaetzlich die Werte der folgenden MetaInfos des Artikels in den
   #   entsprechenden Spalten der Tabelle rex_article eingetragen. Anschliessend
   #   wird der Artikel-Cache geloescht.
   #      self::REWRITER_DIR ('cat_dirname', Bezeichnung des 'Verzeichnisnamens'
   #         einer Kategorie):
   #         - leer, falls der Artikel kein Startartikel ist
   #         - Kategorie-Id, falls leer (bei einem Startartikel)
   #      self::REWRITER_BASE ('art_basename', Bezeichnung des 'Dateinamens'
   #         eines Artikels):
   #         - bei einem Startartikel auf 'index.html' gesetzt
   #         - falls leer, auf '$article_id.html' gesetzt
   #         - bleibt erhalten, falls schon gesetzt
   #      self::REWRITER_URL ('art_custom_url', konfigurierter URL eines Artikels
   #         in der Form 'aaa/bbb/ccc/ddd.html'):
   #         Der Pfad wird aus den Verzeichnisnamen cat_dirname aller 'Vorfahren'
   #         zusammengesetzt und mit dem Dateinamen art_basename des aktuellen
   #         Artikels abgeschlossen.
   #   $article           Artikel-Objekt
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
   $basename=$article->getValue(self::REWRITER_BASE);
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
      $dirname=$artic->getValue(self::REWRITER_DIR);
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
   if(!empty($warning) and !empty($mesb)) $warning=$warning.'<br>';
   if(!empty($mesb)) $warning=$warning.$mesb;
   #
   # --- Datenbank-UPDATEs
   $sql=rex_sql::factory();
   $quu='UPDATE rex_article SET '.self::REWRITER_URL. '=\''.$url.'\'     WHERE id='.$art_id;
   $qub='UPDATE rex_article SET '.self::REWRITER_BASE.'=\''.$baseneu.'\' WHERE id='.$art_id;
   $qud='UPDATE rex_article SET '.self::REWRITER_DIR. '=\''.$dirname.'\' WHERE id='.$par_id;
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
public static function get_newest_dirname($article) {
   #   Rueckgabe des zuletzt erzeugten Werts fuer cat_dirname eines
   #   (Start-)Artikels zu einer Sprachversion. Damit wird auch der Wert
   #   gefunden, der noch nicht in den Custom URL eingebaut ist
   #   (leider ist das Update-Date fuer alle Sprachversionen immer gleich!).
   #   $article           gegebener (Start-)Artikel
   #
   # --- Daten der eingegebenen Sprachversion eines Artikels
   $art_id=$article->getId();
   $dirnam=$article->getValue(self::REWRITER_DIR);
   $dirurl=$article->getValue(self::REWRITER_URL);
   $arr=explode('/',$dirurl);
   #
   # --- Vergleich mit allen anderen Sprachversionen des Artikels
   $sql=rex_sql::factory();
   $query='SELECT * FROM rex_article WHERE id='.$art_id;
   $arts=$sql->getArray($query);
   for($i=0;$i<count($arts);$i=$i+1):
      $dir=$arts[$i][self::REWRITER_DIR];
      $url=$arts[$i][self::REWRITER_URL];
      $arr=explode('/',$url);
      if(count($arr)>=2):
        $par=$arr[count($arr)-2];
        if($par!=$dir) $dirnam=$dir;
        endif;
      endfor;
   return $dirnam;
   }
public static function proof_name($proofstr,$param,$article) {
   #   Ueberpruefung und ggf. Korrektur des 'Dateinamens' eines Artikels bzw.
   #   des 'Verzeichnisnamens' einer Kategorie:
   #   - nicht-leerer Name
   #   - nur erlaubte Zeichen im Namen
   #   - eindeutiger Name innerhalb der Kategorie
   #   zusaetzlich bei Korrektur des 'Dateinamens':
   #   - fehlende/falsche Namenserweiterung
   #   - Dateiname 'index.html' bei Startartikeln
   #   Rueckgabe eines Arrays, bestehend aus dem ggf. korrigierten 'Dateinamens'
   #   bzw. 'Verzeichnisnamens' und einem ggf. nicht leeren Warn-String. Der
   #   Warn-String ist genau dann nicht-leer, wenn eingegebener und zurueck
   #   gegebener String verschieden sind.
   #   $proofstr          Wert von self::REWRITER_BASE bzw. self::REWRITER_DIR
   #   $param             ='name' / 'catname' (Artikel-Parameter)
   #   $article           Artikel
   #   benutzte functions:
   #      self::unique_url($article,$metainfo,$value)
   #      self::allowed_name($name)
   #      self::allowed_chars()
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
     $defext=rex_config::get(self::REWRITER,self::DEFAULT_EXTENSION);
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
     $metname=self::REWRITER_BASE;
     else:
     $legname='&quot;Verzeichnisname&quot;';
     $lername='Name';
     $metname=self::REWRITER_DIR;
     endif;
   $txtid=' (Artikel-Id='.$art_id.')';
   #
   $mes='';
   $string=$proofstr;
   #
   # --- art_basename=index.html bei Startartikeln
   if($param=='name' and $start==1):
     $std=rex_config::get(self::REWRITER,self::DEFAULT_STARTNAME).$ext0;
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
       if(!empty($mes)) $mes=$mes.'<br>';
       $mes=$mes.'<code>'.$kortxt.' Namenserweiterung</code> korrigiert, '.
          $legname.': <code>'.$string.'</code>';
       endif;
     endif;
   #
   # --- leerer Namensstamm
   $arr=explode('.',$string);
   if(empty($arr[0])):
     $string=$artname.$ext;
     if(!empty($mes)) $mes=$mes.'<br>';
     $mes=$mes.'<code>leerer '.$lername.'</code> mit Default-Wert belegt: '.
        $legname.' <code>'.$string.'</code>';
     endif;
   #
   # --- nicht erlaubte Zeichen im Namen
   if(!self::allowed_name($string)):
     if(!empty($mes)) $mes=$mes.'<br>';
     $text='<code>nicht erlaubte Zeichen</code> in '.$legname;
     $mes=$mes.$text.' <code>'.$string.'</code>, korrigiert zu: ';
     $string=$artname.$ext;
     $mes=$mes.'<code>'.$string.'</code>';
     if(!self::allowed_name($string)):
       $mes=$mes.'<br>'.$text.' <code>'.$string.'</code>, korrigiert zu: ';
       $string=strval($art_id).$ext;
       $mes=$mes.'<code>'.$string.'</code>'.$txtid;
       endif;
     $mes=$mes.'<div '.self::INDENT.'>'.self::allowed_chars().'</div>';
     endif;
   #
   # --- Name nicht eindeutig
   if(!self::unique_url($article,$metname,$string)):
     if(!empty($mes)) $mes=$mes.'<br>';
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
   #   - der 'Dateiname' eines Artikels in seiner Kategorie unique ist oder
   #   - der 'Verzeichnisname' einer Kategorie in der Eltern-Kategorie unique ist.
   #   $article           aktueller Artikel (Objekt)
   #   $metainfo          MetaInfo-Name (self::REWRITER_DIR oder self::REWRITER_BASE)
   #   $value             MetaInfo-Wert
   #   Rueckgabe:         TRUE/FALSE
   #   unique bedeutet dabei:
   #      In der betrachteten Kategorie gibt es nur genau einen Artikel mit
   #      vorgegebenem 'Dateinamen' bzw. genau eine Kategorie mit vorgegebenem
   #      'Verzeichnisnamen', sodass aus dem $metainfo-Wert genau ein zugehoeriger
   #      Artikel bzw. genau eine zugehoerige Kategorie bestimmt werden kann.
   #   nicht unique ist ein Artikel bzw. eine Kategorie (vergl. die entsprechende
   #      Abfrage unten), wenn ein anderer Artikel bzw. eine andere Kategorie
   #      (mit anderer Artikel-Id) denselben $metainfo-Wert hat.
   #
   $art_id  =$article->getId();
   if($art_id==rex_article::getSiteStartArticleId()) return TRUE;
   #
   $par_id  =$article->getParentId();
   #     Geschwister des Site-Startartikels haben die Kategorie-Id 0
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
          if($val==$value and $id!=$art_id) return FALSE;
          endforeach;
   return TRUE;
   }
public static function allowed_chars() {
   #   Rueckgabe eines Info-Strings ueber erlaubte Zeichen in den MetaInfos:
   #   - alle grossen und kleinen Buchstaben ausser irgendwelchen Umlauten
   #   - alle Ziffern
   #   - Punkt (.), Minuszeichen (-), Unterstrich (_)
   #
   return 'Erlaubte Zeichen sind Buchstaben, Ziffern, Punkt(.), Minuszeichen(-), Unterstrich(_). '.
      '<i>Nicht erlaubt sind u.a. Umlaute oder Leerzeichen.</i>';
   }
public static function allowed_name($string) {
   #   Ueberpruefen, ob ein gegebener String einem gueltigen Namen fuer einen
   #   art_basename entspricht. Ein gueltiger Name ist nicht leer und besteht
   #   ausschliesslich aus diesen Zeichen:
   #   - alle grossen und kleinen Buchstaben ausser irgendwelchen Umlauten
   #     ASCII-Nummern: chr(65)=A, ..., chr(90)=Z, chr(97)=a, ..., chr(122)=z
   #   - alle Ziffern
   #   - Punkt (.), Minuszeichen (-), Unterstrich (_)
   #
   if(strlen($string)<=0) return FALSE;
   for($i=1;$i<=strlen($string);$i=$i+1):
      $k=$i-1;
      $zeichen=substr($string,$k,1);
      $g=FALSE;
      #     Punkt (.), Minuszeichen (-), Unterstrich (_)
      if($zeichen=='.') $g=TRUE;
      if($zeichen=='-') $g=TRUE;
      if($zeichen=='_') $g=TRUE;
      #     Ziffern
      for($j=0;$j<=9;$j=$j+1):
         if($zeichen==strval($j)):
           $g=TRUE;
           break;
           endif;
         endfor;
      #     Grossbuchstaben
      for($j=65;$j<=90;$j=$j+1):
         if($zeichen==chr($j)):
           $g=TRUE;
           break;
           endif;
         endfor;
      #     Kleinbuchstaben
      for($j=97;$j<=122;$j=$j+1):
         if($zeichen==chr($j)):
           $g=TRUE;
           break;
           endif;
         endfor;
      if(!$g) return FALSE;
      endfor;
   return TRUE;
   }
}
