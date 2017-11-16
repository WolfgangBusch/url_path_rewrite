<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
function path_rewrite_metainfos() {
   #   Einfuegen der MetaInfos in die Redaxo-Tabellen
   #        rex_metainfo_field und rex_article
   #   bei der Installation des Rewriters
   #   benutzte functions:
   #      path_rewrite_sql_action($sql,$query)
   #
   $version=trim(rex::getVersion());
   $version=substr($version,0,1);
   #
   # --- Redaxo 5-Spezifisches
   if(rex_backend_login::createUser()) $user=rex::getUser()->getValue("login");
   $table1="rex_metainfo_field";
   $idkey="id";
   $priokey="priority";
   $table2="rex_article";
   #
   # --- Vorbereitungen
   $heute=date("Y-m-d H:i:s");
   $metainfos=array(
      array("title"     =>"\"Verzeichnisname\"er Kategorie ".
                          "(wird Teil des URL-Strings, Default: obiger Name)",
            "name"      =>REWRITER_DIR,
            "attributes"=>"",
            "type_id"   =>1),
      array("title"     =>"\"Dateiname\" des Artikels ".
                          "(ein Startartikel bekommt automatisch den Namen \"index.html\")",
            "name"      =>REWRITER_BASE,
            "attributes"=>"",
            "type_id"   =>1),
      array("title"     =>"Custom URL (readonly)",
            "name"      =>REWRITER_URL,
            "attributes"=>"readonly=readonly style=background-color:#dddddd",
            "type_id"   =>1));
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfos[$i][params]    ="";
      $metainfos[$i][validate]  ="";
      $metainfos[$i][createuser]=$user;
      $metainfos[$i][createdate]=$heute;
      $metainfos[$i][updateuser]=$user;
      $metainfos[$i][updatedate]=$heute;
      endfor;
   #
   $sql=rex_sql::factory();
   #
   # --- vorhandene MetaInfos abfragen
   for($i=0;$i<count($metainfos);$i=$i+1) $meta_exist[$i]=FALSE;
   $rows=$sql->getArray("SELECT * FROM $table1");
   $anzmeta=count($rows);
   $prio=0;
   for($k=0;$k<$anzmeta;$k=$k+1):
      $row=$rows[$k];
      $key=$row[name];
      $priority=$row[$priokey];
      $meta_typ=substr($key,0,3);
      if($meta_typ=="art" and $priority>$prio) $prio=$priority;
       for($i=0;$i<count($metainfos);$i=$i+1):
         if($metainfos[$i][name]==$key):
           $meta_exist[$i]=TRUE;
           endif;
         endfor;
      endfor;
   #
   # --- neue MetaInfos einfuegen / aktualisieren
   $err_meta="";
   $id=$anzmeta;
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfo=$metainfos[$i];
      $name=$metainfo[name];
      $set="";
      foreach($metainfo as $key => $value)
             if(!empty($value) and $key!="name") $set=$set.", $key='$value'";
      $where="name='$name'";
      $prio=$prio+1;
      $set=substr($set,2).", $priokey=$prio";
      if($meta_exist[$i]):
        $err_meta=$err_meta.",  &nbsp; ".$name;
        else:
        $id=$id+1;
        $set="$idkey=$id, name='$name', ".$set;
        $query="INSERT $table1 SET $set";
        path_rewrite_sql_action($sql,$query);
        endif;
      endfor;
   #
   # --- Meldung ueber schon vorhandene MetaInfos
   $stx="style=\"padding-right:10px;\"";
   if(!empty($err_meta))
      $err_meta="<tr><td $stx>Schon vorhandene <b>MetaInfos</b> ( nicht ".
         "aktualisiert (!) ):</td><td $stx>".substr($err_meta,2)."</td></tr>\n";
   #
   # --- rex_article-Spalten einfuegen
   $err_column="";
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfo=$metainfos[$i];
      $name=$metainfo[name];
      $query="ALTER TABLE $table2 ADD $name VARCHAR(255)";
      path_rewrite_sql_action($sql,$query);
      endfor;
   #
   # --- Meldung ueber schon vorhandene rex_article-Spalten
   if(!empty($err_column))
     $err_column="<tr><td $stx>Schon vorhandene <b>rex_article-Parameter</b> (nicht ".
        "aktualisiert (!) ):</td><td $stx>".substr($err_column,2)."</td></tr>\n";
   #
   # --- Ausgabe der ggf. schon vorhandenen MetaInfos/rex_article-Spalten
   $errstr="";
   if(!empty($err_meta) or !empty($err_column))
     $errstr="<table cellpadding=\"0\" cellspacing=\"0\">\n".
        $err_meta.$err_column."</table>\n";
   echo rex_view::warning($errstr);
   }
?>
