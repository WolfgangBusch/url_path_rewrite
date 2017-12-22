<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Dezember 2017
 */
#
# --- Setzen der Konfigurationsdaten
$defconf=path_rewrite_default_config();
$key=array_keys($defconf);
$fi=TRUE;
for($i=0;$i<count($key);$i=$i+1)
   if(!empty(rex_config::get(REWRITER,$key[$i]))) $fi=FALSE;
#     direkt nach der Installation
if($fi) for($i=0;$i<count($key);$i=$i+1) rex_config::set(REWRITER,$key[$i],$defconf[$key[$i]]);
#
class url_rewrite {
   #
function rewrite($params) {
   #   ###################################################################
   #   IDENTISCHE FUNCTION WIE IN:   url_simple_rewrite
   #   UNTERSCHIEDLICH NUR:          set_url($article) / get_url($article)
   #   ###################################################################
   #   $params            URL_REWRITE-Parameter (Objekt)
   #   Es muss ein Wunsch-URL zurueck gegeben werden (mit fuehrendem "/"):
   #   - im Backend-Fall  wird der URL jeweils im Content-Kontext des Artikels
   #                      (edit, metainfo, functions) bestimmt.
   #                      Er erscheint im Browser-Adressfeld.
   #   - im Frontend-Fall wird der URL im Falle des Aufrufs von rex_getUrl()
   #                      bestimmt. Er ist der Rueckgabewerte von rex_getUrl(..).
   #   aufgerufene functions:
   #      param_normurl()
   #      self::set_url($article)
   #      self::get_url($article)
   #
   $par=$params->getParams();
   $art_id  =$par[id];
   $clang_id=$par[clang];
   $arrpar  =$par[params];
   $separ   =$par[separator];
   #
   #   Aus Artikel-Id und Sprach-Id wird das Artikel-Objekt gewonnen.
   $article=rex_article::get($art_id,$clang_id);
   if($article==NULL) $article=rex_article::getNotfoundArticle();
   #
   if(rex::isBackend()):
     # --- Backend: Definieren und ggf. Speichern des Wunsch-URLs
     $url="/".self::set_url($article);
     else:
     # --- Frontend
     $arr=param_normurl();
     if(count($arr)>=2):
       #   Normalform-URL
       if(count(rex_clang::getAll())>1):
         $str="&clang=".$clang_id=$article->getClang();
         else:
         $str="";
         endif;
       $url="/index.php?article_id=".$art_id.$str;
       else:
       #   Wunsch-URL
       $url="/".self::get_url($article);
       endif;
     endif;
   #
   # --- Die URL-Parameter werden angehaengt.
   $parstr=rex_string::buildQuery($arrpar,$separ);
   if(!empty($parstr)):
     $sep="?";
     if(strpos($url,$sep)>0) $sep="&";
     $url=$url.$sep.$parstr;
     endif;
   return $url;
   }
function set_url($article) {
   #   Rueckgabe des Wunsch-URLs eines Artikels ohne fuehrenden "/"
   #   im Backend-Fall. Alle dafuer benoetigten Artikel-Parameter
   #   werden aus dem Artikel-Objekt genommen.
   #   Der URL wird als Artikel-MetaInfo in der Tabelle rex_article
   #   abgelegt. Anschliessend wird der Artikel-Cache geloescht.
   #   $article           Artikel
   #   Naehere Beschreibung:
   #   Die folgenden MetaInfo-Werte des Artikels werden in der Redaxo-Datenbank abgelegt,
   #   als Spalten der Tabelle rex_article und als Zeilen der Tabelle rex_metainfo_field.
   #      cat_dirname     Bezeichnung einer Kategorie (Wert der Konstante REWRITER_DIR)
   #                      = leer, falls der Artikel kein Startartikel ist
   #                      = Kategorie-Id, falls leer (bei einem Startartikel)
   #      art_basename    Bezeichnung des "Dateinamens" eines Artikels
   #                      (Wert der Konstante REWRITER_BASE)
   #                      - bei einem Startartikel auf "index.html" gesetzt
   #                      - falls leer, auf "$art_id.html" gesetzt
   #                      - bleibt erhalten, falls schon gesetzt
   #      art_custom_url  URL eines Artikels in der Form "aaa/bbb/ccc/ddd.html"
   #                      (Wert der Konstante REWRITER_URL)
   #                      Der Pfad wird aus den Verzeichnisnamen cat_dirname
   #                      aller Eltern zusammengesetzt und mit dem Dateinamen
   #                      art_basename des aktuellen Artikels abgeschlossen.
   #   benutzte Functions:
   #      self::proof_name($proofstr,$warning,$param,$article)
   #      self::set_clang($url,$clang_id)
   #      self::mode_url_clang($url,$clang_id)
   #      self::mode_clang_info($url,$clang_id)
   #      path_rewrite_sql_action($sql,$query)
   #
   # --- Artikel-Parameter auslesen
   $path    =$article->getPath();
   $art_id  =$article->getId();
   $clang_id=$article->getClang();
   $par_id  =$article->getParentId();
   $basename=$article->getValue(REWRITER_BASE);
   if($article->isStartArticle()) $par_id=$art_id;
   #
   # --- Ueberpruefung des art_basename
   $arr=self::proof_name($basename,"name",$article);
   $basename=$arr[0];
   $mesb=$arr[1];
   #
   # --- Teil 1 des URLs aus dem Pfad zusammensetzen
   $arr=explode("|",$path);
   $url="";
   for($i=1;$i<count($arr)-2;$i=$i+1):
      $partpath=$arr[$i+1];
      $artic=rex_article::get($partpath);
      $dirname=$artic->getValue(REWRITER_DIR);
      # --- Ueberpruefung letztes (neuestes) cat_dirname
      if($i==count($arr)-3):
        if($par_id==$art_id):
          #     evtl. frisch gesetzten cat_dirname erwischen:
          $dirname=self::get_newest_dirname($article);
          $arr=self::proof_name($dirname,"catname",$article);
          else:
          $art=rex_article::get($par_id,$clang_id);
          $arr=self::proof_name($dirname,"catname",$art);
          endif;
        $dirname=$arr[0];
        $mesd=$arr[1];
        endif;
      $url=$url.$dirname."/";
      endfor;
   #
   # --- art_basename an den URL anfuegen
   $url=$url.$basename;
   #
   # --- Zusammenfassung der Warnungen
   $warning=$mesd;
   if(!empty($warning) and !empty($mesb)) $warning=$warning."\n<br/>";
   if(!empty($mesb)) $warning=$warning.$mesb;
   #
   # --- Datenbank-UPDATEs
   $sql=rex_sql::factory();
   $quu="UPDATE rex_article SET ".REWRITER_URL. "='".$url."'      WHERE id=".$art_id;
   $qub="UPDATE rex_article SET ".REWRITER_BASE."='".$basename."' WHERE id=".$art_id;
   $qud="UPDATE rex_article SET ".REWRITER_DIR. "='".$dirname."'  WHERE id=".$par_id;
   path_rewrite_sql_action($sql,$quu);
   path_rewrite_sql_action($sql,$qub);
   if($par_id==$art_id) path_rewrite_sql_action($sql,$qud);
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
function get_newest_dirname($article) {
   #   Rueckgabe des zuletzt erzeugten Werts fuer cat_dirname eines
   #   (Start-)Artikels zu einer Sprachversion; damit wird auch der Wert
   #   gefunden, der noch nicht in den art_custom_url eingebaut ist
   #   (leider ist das Update-Date fuer alle Sprachversionen immer gleich!)
   #
   # --- Daten der eingegebenen Sprachversion eines Artikels
   $art_id=$article->getId();
   $dirnam=$article->getValue(REWRITER_DIR);
   $dirurl=$article->getValue(REWRITER_URL);
   $arr=explode("/",$dirurl);
   $param=$arr[count($arr)-2];
   #
   # --- Vergleich mit allen anderen Sprachversionen des Artikels
   $sql=rex_sql::factory();
   $query="SELECT * FROM rex_article WHERE id=".$art_id;
   $rows=$sql->getArray($query);
   for($i=0;$i<count($rows);$i=$i+1):
      $dir=$rows[$i][REWRITER_DIR];
      $url=$rows[$i][REWRITER_URL];
      $arr=explode("/",$url);
      $par=$arr[count($arr)-2];
      if($par!=$dir) $dirnam=$dir;
      endfor;
   return $dirnam;
   }
function proof_name($proofstr,$param,$article) {
   #   Ueberpruefung und Korrektur von art_basename bzw. cat_dirname:
   #   - nicht-leerer Name
   #   - nur erlaubte Zeichen im Namen
   #   - eindeutiger Name innerhalb der Kategorie
   #   zusaetzlich bei Korrektur von art_basename:
   #   - fehlende/falsche Namenserweiterung
   #   - art_basename="index.html" bei Startartikeln
   #   Rueckgabe eines Arrays, bestehend aus dem korrigierten $proofstr
   #   und dem ggf. nicht leeren Warn-String
   #   $proofstr          Wert von art_basename bzw. cat_dirname
   #   $article           Artikel
   #   $param             ="name"/"catname" (Artikel-Parameter)
   #   benutzte Functions:
   #      path_rewrite_allowed_name($name)
   #      path_rewrite_allowed_chars()
   #      path_rewrite_rand_string($nz)
   #      unique_url($article,$metainfo,$value)
   #
   $art_id=$article->getId();
   $start =$article->isStartArticle();
   $arr=explode(" ",$article->getValue($param));
   $artname=$arr[0];   // Leerzeichen sind ohnehin nicht erlaubt
   #
   $ext="";
   if($param=="name"):
     #
     # --- schon vorhandene Dateinamenserweiterung
     $brr=explode(".",$proofstr);
     $nz=count($brr)-1;
     $oldext=$brr[$nz];
     if($nz>=1):
       $oldext=$brr[$nz];
       else:
       $oldext="";
       endif;
     #
     # --- korrekte vorhandene bzw. erste vorgeschriebene Dateinamenserweiterung
     $defext=rex_config::get(REWRITER,DEFAULT_EXTENSION);
     $arrext=explode(" ",$defext);
     $ext="";
     if(!empty($arrext[0])):
       for($i=0;$i<count($arrext);$i=$i+1):
          if($oldext==$arrext[$i]):
            $ext=".".$oldext;
            break;
            endif;
          endfor;
       if(empty($ext)) $ext=".".$arrext[0];
       endif;
     endif;
   #
   # --- Hilfsvariable
   if($param=="name"):
     $legname="&quot;Dateiname&quot;";
     $lername="Namensstamm";
     $metname=REWRITER_BASE;
     else:
     $legname="&quot;Verzeichnisname&quot;";
     $lername="Name";
     $metname=REWRITER_DIR;
     endif;
   $txtpar="aus Artikel-Parameter";
   $txtid=" (".$txtpar." '<tt>id</tt>')";
   #
   $mes="";
   $string=$proofstr;
   #
   # --- art_basename=index.html bei Startartikeln
   if($param=="name" and $start==1):
     $std=rex_config::get(REWRITER,DEFAULT_STARTNAME).$ext;
     if($string!=$std)
       $mes="Startartikel: ".$legname."=<code>".$std."</code> vorgeschrieben";
     return array($std,$mes);
     endif;
   #
   # --- art_basename: fehlende/falsche Namenserweiterung
   if($param=="name" and $start!=1):
     $arr=explode(".",$string);
     $nz=count($arr)-1;
     $korr=FALSE;
     if($arr[$nz]!=substr($ext,1)):
       $string=$arr[0];
       if($nz>1) for($i=1;$i<$nz;$i=$i+1) $string=$string.".".$arr[$i];
         $string=$string.$ext;
         if(!empty($ext)) $korr=TRUE;
       endif;
     if($korr):
       $kortxt="falsche";
       if($nz<=0) $kortxt="leere";
       if(!empty($mes)) $mes=$mes."\n<br/>";
       $mes=$mes."<code>".$kortxt." Namenserweiterung</code> korrigiert: ".
          $legname."=<code>".$string."</code>";
       endif;
     endif;
   #
   # --- leerer Namensstamm
   $arr=explode(".",$string);
   if(empty($arr[0])):
     $string=$artname.$ext;
     if(!empty($mes)) $mes=$mes."\n<br/>";
     $mes=$mes."<code>leerer ".$lername."</code> mit Default-Wert belegt: ".
        $legname."=<code>".$string."</code>";
     endif;
   #
   # --- nicht erlaubte Zeichen im Namen
   if(!path_rewrite_allowed_name($string)):
     if(!empty($mes)) $mes=$mes."\n<br/>";
     $text="<code>nicht erlaubte Zeichen</code> in ".$legname;
     $mes=$mes.$text." <code>".$string."</code>, korrigiert zu: ";
     $string=$artname.$ext;
     $mes=$mes."<code>".$string."</code>";
     if(!path_rewrite_allowed_name($string)):
       $mes=$mes."\n<br/>".$text." <code>".$string."</code>, korrigiert zu: ";
       $string=strval($art_id).$ext;
       $mes=$mes."<code>".$string."</code>".$txtid;
       endif;
     $mes=$mes."\n<br/> &nbsp; &nbsp; erlaubte Zeichen: ".
        "<span style=\"color:green;\">".path_rewrite_allowed_chars()."</span>";
     if(!unique_url($article,$metname,$string)):
       $text=$legname." <code>schon vergeben</code>: ";
       $mes=$mes."\n<br/>".$text."<code>".$string."</code>, ersetzt durch: ";
       $string=path_rewrite_rand_string(12).$ext;
       $mes=$mes."<code>".$string."</code> (Zufalls-String)";
       endif;
     endif;
   #
   # --- Name nicht eindeutig
   if(!unique_url($article,$metname,$string)):
     if(!empty($mes)) $mes=$mes."\n<br/>";
     $text=$legname." <code>schon vergeben</code>: ";
     $mes=$mes.$text."<code>".$string."</code>, ersetzt durch: ";
     $string=$artname.$ext;
     $mes=$mes."<code>".$string."</code>";
     if(!unique_url($article,$metname,$string)):
       $mes=$mes."\n<br/>".$text."<code>".$string."</code>, ersetzt durch: ";
       $string=strval($art_id).$ext;
       $mes=$mes."<code>".$string."</code>".$txtid;
       endif;
     if(!unique_url($article,$metname,$string)):
       $mes=$mes."\n<br/>".$text."<code>".$string."</code>, ersetzt durch: ";
       $string=path_rewrite_rand_string(12).$ext;
       $mes=$mes."<code>".$string."</code> (Zufalls-String)";
       endif;
     endif;
   return array($string,$mes);
   }
function get_url($article) {
   #   Rueckgabe des Wunsch-URLs eines Artikels ohne fuehrenden "/"
   #   im Frontend-Fall. Alle dafuer benoetigten Artikel-Parameter
   #   werden aus dem Artikel-Objekt genommen.
   #   $article           Artikel-Objekt
   #   Der URL wird hier als Artikel-Parameter aus der Tabelle
   #   rex_article ausgelesen.
   #   benutzte Functions:
   #      self::mode_url_clang($url,$clang_id) {
   #
   $art_id  =$article->getId();
   $clang_id=$article->getClang();
   $sql=rex_sql::factory();
   $where="id=".$art_id." AND clang_id=".$clang_id;
   $query="SELECT * FROM rex_article WHERE ".$where;
   $rows=$sql->getArray($query);
   $row=$rows[0];
   $url=$row[REWRITER_URL];
   return self::mode_url_clang($url,$clang_id);
   }
function mode_url_clang($url,$clang_id) {
   #   Rueckgabe eines URLs, der um die noetige Sprachkennzeichnung ergaenzt ist
   #   $url               Custom URL, wie in der Tabelle rex_article abgelegt
   #   $clang_id          Sprach-Id
   #
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $mode   =rex_config::get(REWRITER,CLANG_MODE);
   $urladd=$url;
   if($mode==1 and $clang_id>1)
     $urladd=rex_clang::get($clang_id)->getCode()."/".$url;
   if($mode==2 and $clang_id>1)
     $urladd=$url."?".$parlang."=".rex_clang::get($clang_id)->getCode();
   if($mode==3 and count(rex_clang::getAll())>1 and rex::isBackend()):
     session_start();
     $_SESSION["$parlang"]=rex_clang::get($clang_id)->getCode();
     endif;
   return $urladd;
   }
function mode_clang_info($url,$clang_id) {
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $mode   =rex_config::get(REWRITER,CLANG_MODE);
   $clang_code=rex_clang::get($clang_id)->getCode();
   $sty="style=\"color:blue\"";
   if($mode==1 and $clang_id>1)
     $info=" &nbsp; &nbsp; angezeigter URL: ".
        "<span ".$sty.">".$clang_code."/</span>Custom Url";
   if($mode==2 and $clang_id>1)
     $info=" &nbsp; &nbsp; URL-Parameter: ".
        "<span ".$sty.">".$parlang."=".$clang_code."</span>";
   if($mode==3 and count(rex_clang::getAll())>1)
     $info=" &nbsp; &nbsp; Session-Variable: ".
        "<span ".$sty.">\$_SESSION['".$parlang."']='".$clang_code."'</span>";
   return "Custom URL: <span ".$sty.">".$url."</span>".$info;
   }
}
