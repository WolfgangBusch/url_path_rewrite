<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Dezember 2017
 */
#
class fe_output {
   #
function output() {
   #   ###################################################
   #   IDENTISCHE FUNCTION WIE IN:   url_simple_rewrite
   #   UNTERSCHIEDLICH NUR:          get_article($req_url)
   #   ###################################################
   #   Frontend-Ausgabe des Artikels.
   #   Die Extension gibt den tatsaechlich gewuenschten Artikel aus.
   #   Wird dieser nicht gefunden oder ist offline, wird stattdessen
   #   der NotFound-Artikel ausgegeben.
   #   aufgerufene functions:
   #      param_normurl()
   #      article_from_normurl($param)
   #      self::get_article($req_url)
   #
   # --- anzuzeigender Artikel
   #   Aus $_SERVER["REQUEST_URI"] wird der auszugebende Artikel
   #   ermittelt. Ggf. alternativ der Notfound-Artikel.
   $param=param_normurl();
   if(count($param)>=2):
     # --- aus der Normalform-URL
     $article=article_from_normurl($param);
     else:
     # --- aus dem Wunsch-URL
     $article=self::get_article($param[url]);
     endif;
   $art_id  =$article->getId();
   $clang_id=$article->getClang();
   $temp_id=$article->getTemplateId();
   #
   # --- neuer Artikel-Inhalt mit den Parametern des gefundenen Artikels
   $content=new rex_article_content;
   $content->setArticleId($art_id);
   $content->setTemplateId($temp_id);
   $content->setClang($clang_id);
   #
   # --- aktuelle Artikel-Id uns Sprach-Id setzen
   #     (Werte von rex_article::getCurrentId(), rex_clang::getCurrentId())
   rex_addon::get('structure')->setProperty("article_id",$art_id);
   rex_clang::setCurrentId($clang_id);
   #
   # --- neuen Artikel-Inhalt ausgeben
   rex_response::sendPage($content->getArticleTemplate());
   }
function get_article($req_url) {
   #   Rueckgabe des Artikel-Objekts zum aufgerufenen URL.
   #   Falls der Artikel offline ist oder nicht gefunden wird,
   #   wird stattdessen der Notfound-Artikel zurueck gegeben.
   #   Links auf Kategorie-Startartikel in der Form ".../" werden auch gefunden.
   #   $req_url           aufgerufener relativer URL ohne Parameter
   #   aufgerufene functions:
   #      path_rewrite_default_config()
   #      self::mode_clang_url($req_url)
   #
   $arr=self::mode_clang_url($req_url);
   $url=$arr[0];
   #
   # --- Links auf Kategorie-Startartikel in der Form ".../"
   if(substr($url,strlen($url)-1)=="/" or empty($url)):
     $key=array_keys(path_rewrite_default_config());
     $ext=rex_config::get(REWRITER,$key[0]);
     $brr=explode(" ",$ext);
     $ext=$brr[0];
     if(!empty($ext)) $ext=".".$ext;
     $url=$url.rex_config::get(REWRITER,$key[1]).$ext;
     endif;
   $clang_id=$arr[1];
   #
   # --- Artikel-Id gemaess Artikel-MetaInfo fuer den URL
   $sql=rex_sql::factory();
   $where=REWRITER_URL."='".$url."' AND clang_id=$clang_id";
   $query="SELECT * FROM rex_article WHERE $where";
   $rows=$sql->getArray($query);
   $row=$rows[0];
   $art_id=$row[id];
   $status=$row[status];
   #
   # --- Artikel nicht gefunden oder offline
   if($art_id<=0 or $status<=0) $art_id=rex_article::getNotfoundArticleId();
   #
   # --- Artikel-Objekt
   return rex_article::get($art_id,$clang_id);
   }
function mode_clang_url($req_url) {
   #   Rueckgabe des Custom URL und der Sprach-Id, basierend auf dem
   #   aufgerufenen URL.
   #   $req_url           aufgerufener relativer URL ohne Parameter
   #   aufgerufene functions:
   #      path_rewrite_clangid_from_clangcode($clang_code)
   #
   $mode=rex_config::get(REWRITER,CLANG_MODE);
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $url=$req_url;
   #
   if($mode==1):
     #     Sprach-Code bestimmen
     $arr=explode("/",$url);
     $clang_code=$arr[0];
     #     Sprach-Id aus dem Sprach-Code bestimmen
     $clang_id=path_rewrite_clangid_from_clangcode($clang_code);
     #     Custom URL neu bestimmen
     if($clang_id>1) $url=substr($url,strlen($clang_code)+1);
     endif;
   #
   if($mode==2):
     #     Sprach-Code bestimmen
     $clang_code=$_GET["$parlang"];
     #     Sprach-Id aus dem Sprach-Code bestimmen
     if(!empty($clang_code)):
       $clang_id=path_rewrite_clangid_from_clangcode($clang_code);
       else:
     #     Sprach-Code nicht definiert: Standard-Sprache annehmen
       $clang_id=1;
       endif;
     endif;
   #
   if($mode==3):
     session_start();
     #     Sprach-Code aus URL-Parameter bestimmen
     $clang_code=$_GET["$parlang"];
     if(!empty($clang_code)):
       #     Session-Variable entsprechend neu setzen
       $_SESSION["$parlang"]=$clang_code;
       else:
     #     Sprach-Code aus SESSION-Variabler bestimmen
       $clang_code=$_SESSION["$parlang"];
       endif;
     #     Sprach-Code nicht definiert: Standard-Sprache annehmen
     if(empty($clang_code)):
       $clang_code=rex_clang::get(1)->getCode();
       #     Session-Variable entsprechend neu setzen
       $_SESSION["$parlang"]=$clang_code;
       endif;
     #     Sprach-Id aus dem Sprach-Code bestimmen
     $clang_id=path_rewrite_clangid_from_clangcode($clang_code);
     endif;
   return array($url,$clang_id);
   }
}
