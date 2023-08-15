<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version August 2023
 */
#
class url_path_config {
#
#   define_metainfos()
#   insert_metainfos($metainfos)
#   insert_metainfos_articles($metainfos)
#   install_metainfos()
#   default_config()
#   first_config()
#   configuration()
#
const this_addon=url_path_rewrite::REWRITER;
#
public static function define_metainfos() {
   #   Definition der MetaInfos fuer die Redaxo-Tabellen
   #   rex_metainfo_field und rex_article.
   #
   $addon=self::this_addon;
   $user='';
   if(rex_backend_login::createUser()) $user=rex::getUser()->getValue('login');
   $priokey='priority';
   #
   # --- Definition der MetaInfos
   $heute=date('Y-m-d H:i:s');
   $metainfos=array(
      array('name'      =>$addon::REWRITER_DIR,
            'title'     =>'"Verzeichnisname" der Kategorie '.
                          '(wird Teil des URL-Strings, Default: obiger Name)',
            'attributes'=>'',
            'type_id'   =>1),
      array('name'      =>$addon::REWRITER_BASE,
            'title'     =>'"Dateiname" des Artikels '.
                          '(ein Startartikel bekommt automatisch den Namen "index.html")',
            'attributes'=>'',
            'type_id'   =>1),
      array('name'      =>$addon::REWRITER_URL,
            'title'     =>'Custom URL (readonly)',
            'attributes'=>'readonly=readonly '.$addon::BG_GREY,
            'type_id'   =>1));
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfos[$i]['createuser']=$user;
      $metainfos[$i]['createdate']=$heute;
      $metainfos[$i]['updateuser']=$user;
      $metainfos[$i]['updatedate']=$heute;
      endfor;
   return $metainfos;
   }
public static function insert_metainfos($metainfos) {
   #   Einfuegen der MetaInfos als Zeilen in die Tabelle rex_metainfo_field.
   #   Rueckgabe der Namen der eingetragenen MetaInfos als nummeriertes
   #   Array (Nummerierung ab 1) mit dem Zusatz '(inserted)' bzw. '(updated)'.
   #   $metainfos         Array der 3 MetaInfos mit den noetigen Parametern
   #
   $table=rex::getTablePrefix().'metainfo_field';
   $sql=rex_sql::factory();
   #
   # --- alle MetaInfos auslesen, einzutragende schon vorhanden?
   $rows=$sql->getArray('SELECT * FROM '.$table);
   $meta_id=array();
   for($i=0;$i<count($metainfos);$i=$i+1) $meta_id[$i]=0;
   for($k=0;$k<count($rows);$k=$k+1):
      $key=$rows[$k]['name'];
      for($i=0;$i<count($metainfos);$i=$i+1)
         if($metainfos[$i]['name']==$key) $meta_id[$i]=$rows[$k]['id'];
      endfor;
   #
   # --- Einfuegen/Aktualisieren
   $ret=array();
   $m=0;
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfo=$metainfos[$i];
      $name=$metainfo['name'];
      $set='';
      $keys=array_keys($metainfo);
      for($k=0;$k<count($keys);$k=$k+1):
         $key=$keys[$k];
         if($key=='name') continue;
         $val=$metainfo[$key];
         if(is_numeric($val)):
           $set=$set.', '.$key.'='.$val;
           else:
           $set=$set.', '.$key.'=\''.$val.'\'';
           endif;
         endfor;
      $set='name=\''.$name.'\''.$set;
      $m=$m+1;
      if($meta_id[$i]<=0):
        $query='INSERT '.$table.' SET '.$set;
        $ret[$m]=$name.' (inserted)';
        else:
        $query='UPDATE '.$table.' SET '.$set.' WHERE id='.$meta_id[$i];
        $ret[$m]=$name.' (updated)';
        endif;
      $sql->setQuery($query);
      endfor;
   return $ret;
   }
public static function insert_metainfos_articles($metainfos) {
   #   Ergaenzen der Tabelle rex_article um die Spalten der neuen MetaInfos.
   #   Rueckgabe der entsprechenden Spalten in Form eines nummerierten Arrays
   #   der Namen der ergaenzten MetaInfos (Nummerierung ab 1) mit dem Zusatz
   #   '(new rex_article column)' bzw. '(already rex_article column)').
   #   $metainfos         Array der 3 MetaInfos mit den noetigen Parametern
   #
   $addon=self::this_addon;
   #
   # --- schon als Spalten in rex_article definierte MetaInfos
   $table=rex::getTablePrefix().'article';
   $sql=rex_sql::factory();
   $cols=$sql->getArray('SHOW COLUMNS FROM '.$table);
   $col=array();
   $m=0;
   for($i=0;$i<count($cols);$i=$i+1):
      for($k=0;$k<count($metainfos);$k=$k+1):
         $name=$metainfos[$k]['name'];
         if($cols[$i]['Field']==$name):
            $m=$m+1;
            $col[$m]=$name;
            break;
            endif;
         endfor;
      endfor;
   #
   # --- fehlende Spalten ergaenzen
   $ret=array();
   $m=0;
   for($i=0;$i<count($metainfos);$i=$i+1):
      $name=$metainfos[$i]['name'];
      $ret[$i+1]=$name.' (already rex_article column)';
      $query='';
      for($k=1;$k<=count($col);$k=$k+1)
         if($col[$k]==$name):
           $query=$name;
           break;
           endif;
      if(empty($query)):
        $query='ALTER TABLE '.rex::getTablePrefix().'article ADD '.$name.' VARCHAR(255)';
        $sql->setQuery($query);
        $ret[$i+1]=$name.' (new rex_article column)';
        endif;
      endfor;
   return $ret;
   }
public static function install_metainfos() {
   #   Einfuegen der MetaInfos in die Redaxo-Tabellen
   #   rex_metainfo_field und rex_article.
   #   benutzte functions:
   #      self::define_metainfos()
   #      self::insert_metainfos($metainfos)
   #      self::insert_metainfos_articles($metainfos)
   #
   $addon=self::this_addon;
   #
   # --- MetaInfos definieren
   $metainfos=self::define_metainfos();
   #
   # --- MetaInfos einfuegen, falls nicht schon vorhanden
   $ret1=self::insert_metainfos($metainfos);
   $msg1='';
   for($i=1;$i<=count($ret1);$i=$i+1)
      if(strpos($ret1[$i],'inserted')>0 or !rex::isBackend())
        $msg1=$msg1.', '.trim(explode('(',$ret1[$i])[0]);
   if(!empty($msg1))
     $msg1='<div '.$addon::INDENT.'>'.substr($msg1,2).' &nbsp; (additional MetaInfos)</div>';
   #
   # --- MetaInfos als Spalten in rex_article einfuegen
   $ret2=self::insert_metainfos_articles($metainfos);
   $msg2='';
   for($i=1;$i<=count($ret2);$i=$i+1)
      if(strpos($ret2[$i],'new')>0 or !rex::isBackend())
        $msg2=$msg2.', '.trim(explode('(',$ret2[$i])[0]);
   if(!empty($msg2))
     $msg2='<div '.$addon::INDENT.'>'.substr($msg2,2).' &nbsp; (additional '.rex::getTablePrefix().'article columns)</div>';
   $msg=$msg1.$msg2;
   if(!empty($msg)) rex_addon::get($addon)->setProperty('successmsg',$msg);
   return $msg;
   }
public static function default_config() {
   #   Rueckgabe der Default-Konfigurations-Parameter.
   #
   $addon=self::this_addon;
   $defconf=array(
      $addon::DEFAULT_EXTENSION=>'html php css',
      $addon::DEFAULT_STARTNAME=>'index',
      $addon::CLANG_PARAMETER  =>$addon::CLANG_VALUE,
      $addon::CLANG_MODE       =>3);            // ***
   # ***
   #   Art der Sprachkennzeichnung eines Artikels im Frontend
   #   moegliche Darstellungsformen des URLs:
   #      Standardsprache:   URL-String=Stamm-URL
   #                         (kein Sprachhinweis im URL, keine Session-Variable)
   #      Sonstige Sprachen: der URL-String enthaelt den Sprach-Code oder
   #                         eine Session-Variable enthaelt den Sprachcode:
   #          CLANG_MODE=1:  URL=/code/Stamm-URL
   #                    =2:  URL=Stamm-URL?CLANG_VALUE=code
   #                    =3:  URL=Stamm-URL, Session-Variable: CLANG_VALUE=code
   #                         Aenderung der Session-Variable durch URL-Parameter
   #                         ...?CLANG_VALUE=code
   return $defconf;
   }
public static function first_config() {
   #   Setzen der Default-Konfiguration bei der Erstinstallation,
   #   d.h. falls noch keine Konfigurationsparameter gesetzt sind.
   #   benutzte functions:
   #      self::default_config()
   #
   $addon=self::this_addon;
   $defconf=self::default_config();
   $key=array_keys($defconf);
   $first=TRUE;
   for($i=0;$i<count($key);$i=$i+1)
      if(!empty(rex_config::get($addon::REWRITER,$key[$i]))):
        $first=FALSE;
        break;
        endif;
   if($first)
     for($i=0;$i<count($key);$i=$i+1)
        rex_config::set($addon::REWRITER,$key[$i],$defconf[$key[$i]]);
   rex_config::refresh();
   }
public static function configuration() {
   #   Einlesen und Speichern der Konfigurationsparameter.
   #   benutzte functions:
   #      self::default_config()
   #      $addon::allowed_name()
   #      $addon::allowed_chars()
   #
   $addon=self::this_addon;
   #
   # --- Default-Konfiguration
   $defconf=self::default_config();
   $defval=array();
   $key=array_keys($defconf);
   for($i=0;$i<count($key);$i=$i+1) $defval[$i]=$defconf[$key[$i]];
   #
   # --- Auslesen der gesetzen Konfiguration (falls alles leer: Default-Werte)
   $confval=array();
   $leer=TRUE;
   for($i=0;$i<count($key);$i=$i+1):
      $confval[$i]=rex_config::get($addon::REWRITER,$key[$i]);
      if(!empty($confval[$i])) $leer=FALSE;
      endfor;
   if($leer)
     for($i=0;$i<count($key);$i=$i+1) $confval[$i]=$defval[$i];
   #
   # --- Einlesen der gesetzten Formularwerte
   $save='';
   $reset='';
   $val=array();
   if(count($_POST)>0):
     for($i=0;$i<count($key);$i=$i+1) $val[$i]=$_POST[$key[$i]];
     if(!empty($_POST['save']))  $save=$_POST['save'];
     if(!empty($_POST['reset'])) $reset=$_POST['reset'];
     ;else:
     for($i=0;$i<count($key);$i=$i+1) $val[$i]=$confval[$i];
     endif;
   #
   # --- nicht erlaubte Werte ermitteln, Warnungen setzen
   $arrext=explode(' ',$val[0]);
   $warn=array('','','');
   if(!empty($val[0])):
     for($i=0;$i<count($arrext);$i=$i+1):
        if(!empty($save) and !$addon::allowed_name($arrext[$i])):
          $warn[0]='nicht erlaubte Zeichen in <code>'.$val[0].
             '</code>, Parameter zurückgesetzt';
          break;
          endif;
        endfor;
     endif;
   if(!empty($warn[0])) $val[0]=$confval[0];
   for($i=1;$i<=2;$i=$i+1):
      if(!empty($save) and !$addon::allowed_name($val[$i]) and !empty($val[$i])):
        $warn[$i]='nicht erlaubte Zeichen in <code>'.$val[$i].
           '</code>, Parameter zurückgesetzt';
        $val[$i]=$confval[$i];
        endif;
      endfor;
   if(empty($val[1]) and !empty($val[0])):
     $warn[0]='bei leerem Namensstamm eines Startartikels muss auch die Namenserweiterung leer sein';
     $val[0]='';
     endif;
   if(empty($val[0]) and !empty($val[1])):
     $warn[1]='bei leerer Namenserweiterung muss auch der Namensstamm eines Startartikels leer sein';
     $val[1]='';
     endif;
   if(empty($val[2])):
     $warn[2]='Parameter darf nicht leer sein, zurückgesetzt';
     $val[2]=$confval[2];
     endif;
   #
   # --- Im Reset-Fall die Eingabefelder mit den Default-Werten fuellen
   if(!empty($reset)) $val=$defval;
   #
   # --- Abfragetexte
   for($i=1;$i<=3;$i=$i+1):
      $c[$i]='<tt>';
      $d[$i]='</tt>';
      if($val[3]==$i):
        $c[$i]='<code '.$addon::BG_GREY.'>';
        $d[$i]='</code>';
        $stc[$i]=' '.$addon::BG_GREY;
        endif;
      endfor;
   $code=rex_clang::get(count(rex_clang::getAll()))->getCode();
   $stdcode=rex_clang::get(1)->getCode();
   $ext='.'.$arrext[0];
   if(empty($val[0])) $ext='';
   $text[0]='erlaubte Erweiterungen für den &quot;Dateinamen&quot; eines Artikels<br>
            (durch Leerzeichen separiert)
            <div '.$addon::INDENT.'>Beispiel: <tt>'.$addon::REWRITER_BASE.'=xxxxxx</tt><code>
            '.$ext.'</code><br>
            (können entfallen, in dem Fall entfällt auch der Punkt)</div>';
   $text[1]='Stamm für den &quot;Dateinamen&quot; des Startartikels einer Kategorie
            <div '.$addon::INDENT.'>also &quot;Dateiname&quot; eines Startartikels:
            <code>'.$val[1].'</code><tt>'.$ext.'</tt><br>
            (entfällt bei leerer [erster] Namenserweiterung)</div>';
   $text[2]='Name für den URL-Parameter bzw. die Session-Variable
            <div '.$addon::INDENT.'>zur Kennzeichnung der Sprache</div>';
   $text[3]='Art der Kennzeichnung (=1/2/3):
            <div '.$addon::INDENT.'><div '.$addon::INDENT.'>
            (für den Sprachcode steht im Folgenden: &nbsp; <code>'.$code.'</code> )</div></div>';
   $textpl='<ol '.$addon::MARGIN0.'>
                <li'.$stc[1].'>vordere Erweiterung des URL-Pfades um den Sprachcode<br>
                    Beispiel-URL: '.$c[1].$code.'/cat_dirname1/.../xxxxxx'.$ext.$d[1].'</li>
                <li'.$stc[2].'>Eweiterung des URL um einen Sprachcode-Parameter<br>
                    Beispiel-URL: '.$c[2].'cat_dirname1/.../xxxxxx'.$ext.'?'.$val[2].'='.$code.$d[2].'</li>
                <li'.$stc[3].'>Ergänzung des URL um eine Sprachcode-Session-Variable<br>
                    Beispiel-URL: '.$c[3].'cat_dirname1/.../xxxxxx'.$ext.$d[3].'<br>
                    Beispiel Session-Variable: '.$c[3].'$_SESSION[\''.$val[2].'\']=\''.$code.'\''.$d[3].'</li>
            </ol>
            <div '.$addon::INDENT.'>Für die Standardsprache <tt>'.$stdcode.'</tt> entfallen die
            Kennzeichnungen 1, 2 im URL.<br>
            Falls nur eine Sprache definiert ist, entfallen alle Kennzeichnungen.</div>';
   #
   # --- Formularanfang
   $string='
<form method="post">';
   echo $string;
   #
   # --- erlaubte Zeichen
   $allow=$addon::allowed_chars();
   $pos=strpos($allow,'Buchstaben')-1;
   $allow=substr($allow,$pos);
   $string='
<table '.$addon::BG_INHERIT.'>
    <tr valign="top">
        <td colspan="2">
            <b>Erlaubte Zeichen für die folgenden Parameter:</b>
            <div '.$addon::INDENT.'>'.$allow.'</div></td></tr>';
   echo $string;
   #
   # --- Konfigurationsparameter 0,1,2
   $string='
    <tr valign="top">
        <td colspan="2">
            <b>Parameter zur Konstruktion der URLs:</b></td></tr>';
   for($i=0;$i<count($key)-1;$i=$i+1):
      $string=$string.'
    <tr valign="top">
        <td '.$addon::INDENT.'>
            '.$text[$i].'</td>
        <td '.$addon::INDENT.'>
            <input '.$addon::INPUT_WIDTH.' id="'.$key[$i].'" name="'.$key[$i].'" value="'.$val[$i].'" /></td></tr>';
      if(!empty($warn[$i])) $string=$string.'
     <tr valign="top">
         <td colspan="2" '.$addon::INDENT.'>
             '.rex_view::warning($warn[$i]).'</td></tr>';
      if($i==1) $string=$string.'
    <tr valign="top">
        <td colspan="2">
            <b>Parameter zur Kennzeichnung der Sprache:</b></td></tr>';
      endfor;
   echo $string;
   #
   # --- Konfigurationsparameter 3
   $stropt='';
   for($i=1;$i<=3;$i=$i+1):
      $selst[$i]='';
      if($i==$val[3]) $selst[$i]='selected="selected"';
      $stropt=$stropt.'
                <option '.$selst[$i].' value="'.$i.'">'.$i.'</option>';
      endfor;
   #
   # --- Art der Bezeichnung
   $string='
    <tr valign="top">
        <td '.$addon::INDENT.'>
            '.$text[3].'
            '.$textpl.'</td>
        <td '.$addon::INDENT.'>
            <select id="'.$key[3].'" name="'.$key[3].'" '.$addon::BG_GREY.'>'.$stropt.'
            </select></td></tr>';
   echo $string;
   #
   # --- Submit-Button, Reset-Button und Formular-Abschluss
   $tit='Parameter speichern';
   $txt='auf Defaultwerte zurücksetzen';
   $sit='Parameter '.$txt.' und speichern';
   $string='
    <tr valign="top">
        <td '.$addon::INDENT.'><br>
            <button class="btn btn-save"   type="submit" name="save"  value="save"
                    title="'.$tit.'"> '.$tit.' </button></td>
        <td '.$addon::INDENT.'><br>
            <button class="btn btn-update" type="submit" name="reset" value="reset"
                    title="'.$sit.'"> '.$txt.' </button></td></tr>
</table>
</form>';
   echo $string;
   #
   # --- Uebersschreiben der Konfiguration
   if(!empty($save) or !empty($reset)):
     for($i=0;$i<count($key);$i=$i+1)
        if(is_numeric($val[$i])):
          rex_config::set($addon::REWRITER,$key[$i],intval($val[$i]));
          else:
          rex_config::set($addon::REWRITER,$key[$i],$val[$i]);
          endif;
     endif;
   }
}
?>
