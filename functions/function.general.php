<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
define("REWRITER",         $this->getPackageId()); // Package-Id
define("REWRITER_URL",     "art_custom_url");      // Meta Info
define("REWRITER_BASE",    "art_basename");        // Meta Info
define("REWRITER_DIR",     "cat_dirname");         // Meta Info
define("DEFAULT_EXTENSION","default_extension");   // Konfiguration
define("DEFAULT_STARTNAME","default_startname");   // Konfiguration
define("CLANG_PARAMETER",  "clang_parameter");     // Konfiguration
define("CLANG_MODE",       "clang_mode");          // Konfiguration
#
function path_rewrite_default_config() {
   #   Rueckgabe der Default-Konfigurations-Parameter
   #
   $defconf=array(
      DEFAULT_EXTENSION=>"html php css",
      DEFAULT_STARTNAME=>"index",
      CLANG_PARAMETER  =>"language",
      CLANG_MODE       =>3);     // ***
   # ***
   #   Art der Sprachkennzeichnung eines Artikels im Frontend
   #   moegliche Darstellungsformen des URLs:
   #      Standardsprache:   URL-String=Stamm-URL
   #                         (kein Sprachhinweis im URL, keine Session-Variable)
   #      Sonstige Sprachen: der URL-String enthaelt den Sprach-Code:
   #         mode=1:  URL=/code/Stamm-URL
   #             =2:  URL=Stamm-URL?parlang=code
   #                         oder eine Session-Variable enthaelt den Sprachcode:
   #             =3:  URL=Stamm-URL, Session-Variable: parlang=code
   #                  Aenderung der Session-Variablen durch URL-Parameter parlang=code
   return $defconf;
   }
function path_rewrite_sql_action($sql,$query) {
   #   Ausfuehrung einer SQL-Aktion mittels setQuery()
   #   ggf. Ausgabe einer Fehlermeldung
   #   $sql               SQL-Handle
   #   $query             SQL-Aktion
   #
   try {
        $sql->setQuery($query);
        $error="";
         } catch(rex_sql_exception $e) {
        $error=$e->getMessage();
        }
   if(!empty($error)) echo rex_view::error($error);
   }
function path_rewrite_allowed_name($string) {
   #   Ueberpruefen, ob ein gegebener String einem gueltigen Namen fuer einen
   #   art_basename entspricht. Ein gueltiger Name ist nicht leer und besteht
   #   ausschliesslich aus diesen Zeichen:
   #   - alle grossen und kleinen Buchstaben ausser irgendwelchen Umlauten
   #   - alle Ziffern
   #   - Punkt (.), Minuszeichen (-), Unterstrich (_)
   #   ASCII-Nummern: chr(65)=A, chr(90)=Z, chr(97)=a, chr(122)=z
   #
   if(strlen($string)<=0) return FALSE;
   for($i=1;$i<=strlen($string);$i=$i+1):
      $k=$i-1;
      $zeichen=substr($string,$k,1);
      $g=FALSE;
      if($zeichen==".") $g=TRUE;
      if($zeichen=="-") $g=TRUE;
      if($zeichen=="_") $g=TRUE;
      for($j=0;$j<=9;$j=$j+1):
         if($zeichen==strval($j)):
           $g=TRUE;
           break;
           endif;
         endfor;
      for($j=65;$j<=90;$j=$j+1):
         if($zeichen==chr($j)):
           $g=TRUE;
           break;
           endif;
         endfor;
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
function path_rewrite_allowed_chars() {
   #   Rueckgabe eines Info-Strings ueber erlaubte Zeichen in den Meta Infos:
   #   - alle grossen und kleinen Buchstaben ausser irgendwelchen Umlauten
   #   - alle Ziffern
   #   - Punkt (.), Minuszeichen (-), Unterstrich (_)
   #
   return "Buchstaben, Ziffern, Punkt(.), Minuszeichen(-), ".
      "Unterstrich(_), <u>keine Umlaute</u>, <u>keine Leerzeichen</u>";
   }
function path_rewrite_rand_string($nz) {
   #   Rueckgabe eines Zufalls-Strings aus Kleinbuchstaben
   #   $nz                Laenge des ufalls-Strings
   #
   $zuf="";
   for($i=1;$i<=$nz;$i=$i+1) $zuf=$zuf.chr(mt_rand(97,122));
   return $zuf;
   }
function path_rewrite_clangid_from_clangcode($clang_code) {
   #   Bestimmung und Rueckgabe der Sprach-Id zu einem
   #   gemaess Redaxo definierten Sprach-Code
   #   $clang_code        Sprach-Code
   #                      falls leer wird die Standardsprache angenommen
   #
   if(empty($clang_code)) $clang_code=rex_clang::get(1)->getCode();
   foreach(rex_clang::getAll() as $key=>$lang):
          $id=$lang->getId();
          $code=rex_clang::get($id)->getCode();
          if($code==$clang_code) $clang_id=$id;
          endforeach;
   if($clang_id<=0) $clang_id=1;
   return $clang_id;
   }
function unique_url($article,$metainfo,$value) {
   #   Pruefen, ob
   #   der art_basename eines Artikels in seiner Kategorie unique ist oder
   #   der cat_dirname einer Kategorie in der Eltern-Kategorie unique ist.
   #   $article           aktueller Artikel (Objekt)
   #   $metainfo          MetaInfo-Name (cat_dirname oder art_basename)
   #   $value             MetaInfo-Wert
   #   Rueckgabe:         TRUE/FALSE
   #   UNIQUE bedeutet dabei:
   #      Im betrachteten Bereich gibt es nur genau einen Artikel mit
   #      vorgegebenem $metainfo-Wert und vorgegebener Sprach-Id, sodass aus
   #      $metainfo-Wert und Sprach-Id genau ein zugehoeriger Artikel bestimmt
   #      werden kann. Da die verschiedenen Sprach-Versionen eines Artikels
   #      dieselbe Artikel-Id haben, bedeutet das:
   #   NICHT UNIQUE ist ein Artikel (vergl. die entsprechende Abfrage unten),
   #      wenn ein anderer Artikel (mit anderer Artikel-Id) dieselbe Sprach-Id
   #      und denselben $metainfo-Wert hat.
   #
   $art_id  =$article->getId();
   if($art_id==rex_article::getSiteStartArticleId()) return TRUE;
   $clang_id=$article->getValue("clang_id");
   $par_id  =$article->getParentId();
   #     Geschwister von "Homepage" haben die Kategorie-Id "0"
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
          $val=$art->getValue("$metainfo");
          $id=$art->getId();
          $cid=$art->getValue("clang_id");
          if($val==$value and $id!=$art_id and $cid==$clang_id) return FALSE;
          endforeach;
   return TRUE;
   }
function param_normurl() {
   #   Rueckgabe des URL und der URL-Parameter bei einem URL in Normalform
   #   in Form eines assoziativen Arrays:
   #   $param             assoziatives Array der URL-Parameter
   #      [url]           URL ohne Parameter
   #      [article_id]    Artikel-Id
   #      [clang]         Sprach-Id
   #   Falls keine URL-Normalform vorliegt wird nur der URL ohne Parameter
   #   zurueck gegeben (Array-Count = 1).
   #
   $var0="url";
   $var1="article_id";
   $var2="clang";
   $arr=explode("?",substr($_SERVER["REQUEST_URI"],1));
   $url=$arr[0];
   $aid=rex_get($var1);
   $cid=rex_get($var2);
   if(!empty($aid)):
     if(!empty($cid)):
       $param=array($var0=>$url, $var1=>$aid, $var2=>$cid);
       else:
       $param=array($var0=>$url, $var1=>$aid);
       endif;
     else:
     $param=array($var0=>$url);
     endif;
   return $param;
   }
function article_from_normurl($param) {
   #   Rueckgabe des Artikel-Objekts zu einem URL in Normalform.
   #   Falls der Artikel nicht existiert, wird der NotFound-Artikel
   #   zurueck gegeben.
   #   $param             assoziatives Array der URL-Parameter
   #      [url]           URL ohne Parameter
   #      [article_id]    Artikel-Id
   #      [clang]         Sprach-Id
   #   Falls keine URL-Normalform vorliegt wird nur der URL ohne Parameter
   #   zurueck gegeben (Array-Count = 1)
   #
   if(count($param)>=2):
     $key=array_keys($param);
     $art_id  =$param[$key[1]];
     $clang_id=$param[$key[2]];
     $article=rex_article::get($art_id,$clang_id);
     else:
     $article=NULL;
     endif;
   if($article==NULL) $article=rex_article::getNotfoundArticle();
   return $article;
   }
?>
