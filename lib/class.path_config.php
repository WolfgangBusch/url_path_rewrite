<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version April 2020
 */
#
define('REWRITER',         $this->getPackageId()); // Package-Id
define('REWRITER_URL',     'art_custom_url');      // Meta Info
define('REWRITER_BASE',    'art_basename');        // Meta Info
define('REWRITER_DIR',     'cat_dirname');         // Meta Info
define('DEFAULT_EXTENSION','default_extension');   // Konfiguration
define('DEFAULT_STARTNAME','default_startname');   // Konfiguration
define('CLANG_PARAMETER',  'clang_parameter');     // Konfiguration
define('CLANG_VALUE',      'language');            // Konfiguration
define('CLANG_MODE',       'clang_mode');          // Konfiguration
#
class url_path_config {
#
# --- hierarchische Liste der functions
#     install_metainfos()
#        sql_action($sql,$query)
#     configuration()
#        default_config()
#        allowed_name()
#        allowed_chars()
#
public static function install_metainfos() {
   #   Einfuegen der MetaInfos in die Redaxo-Tabellen
   #        rex_metainfo_field und rex_article
   #   bei der Installation des Rewriters
   #   benutzte functions:
   #      self::sql_action($sql,$query)
   #
   $user='';
   if(rex_backend_login::createUser()) $user=rex::getUser()->getValue('login');
   $table1='rex_metainfo_field';
   $idkey='id';
   $priokey='priority';
   $table2='rex_article';
   #
   # --- Definition der MetaInfos
   $heute=date('Y-m-d H:i:s');
   $metainfos=array(
      array('title'     =>'"Verzeichnisname" der Kategorie '.
                          '(wird Teil des URL-Strings, Default: obiger Name)',
            'name'      =>REWRITER_DIR,
            'attributes'=>'',
            'type_id'   =>1),
      array('title'     =>'"Dateiname" des Artikels '.
                          '(ein Startartikel bekommt automatisch den Namen "index.html")',
            'name'      =>REWRITER_BASE,
            'attributes'=>'',
            'type_id'   =>1),
      array('title'     =>'Custom URL (readonly)',
            'name'      =>REWRITER_URL,
            'attributes'=>'readonly=readonly style=background-color:#dddddd',
            'type_id'   =>1));
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfos[$i]['params']    ='';
      $metainfos[$i]['validate']  ='';
      $metainfos[$i]['createuser']=$user;
      $metainfos[$i]['createdate']=$heute;
      $metainfos[$i]['updateuser']=$user;
      $metainfos[$i]['updatedate']=$heute;
      endfor;
   #
   $sql=rex_sql::factory();
   #
   # --- vorhandene MetaInfos abfragen
   for($i=0;$i<count($metainfos);$i=$i+1) $meta_exist[$i]=FALSE;
   $rows=$sql->getArray('SELECT * FROM '.$table1);
   $anzmeta=count($rows);
   $prio=0;
   for($k=0;$k<$anzmeta;$k=$k+1):
      $key=$rows[$k]['name'];
      $priority=$rows[$k][$priokey];
      $meta_typ=substr($key,0,3);
      if($meta_typ=='art' and $priority>$prio) $prio=$priority;
       for($i=0;$i<count($metainfos);$i=$i+1):
         if($metainfos[$i]['name']==$key):
           $meta_exist[$i]=TRUE;
           endif;
         endfor;
      endfor;
   #
   # --- neue MetaInfos einfuegen, falls nicht schon vorhanden
   $err_meta='';
   $id=$anzmeta;
   for($i=0;$i<count($metainfos);$i=$i+1):
      $metainfo=$metainfos[$i];
      $name=$metainfo['name'];
      $set='';
      foreach($metainfo as $key => $value)
             if(!empty($value) and $key!='name') $set=$set.', '.$key.'=\''.$value.'\'';
      $where='name=\''.$name.'\'';
      $prio=$prio+1;
      $set=substr($set,2).', '.$priokey.'='.$prio;
      if($meta_exist[$i]):
        $err_meta=$err_meta.', '.$name;
        else:
        $id=$id+1;
        $set=$idkey.'='.$id.', name=\''.$name.'\', '.$set;
        $query='INSERT '.$table1.' SET '.$set;
        self::sql_action($sql,$query);
        endif;
      endfor;
   #
   # --- Meldung ueber schon vorhandene MetaInfos
   $stx='style="padding-right:10px;"';
   if(!empty($err_meta))
      $err_meta='<tr><td '.$stx.'>Schon vorhandene <b>MetaInfos</b> ( nicht '.
         'aktualisiert (!) ):</td><td '.$stx.'>'.substr($err_meta,2).'</td></tr>';
   #
   # --- rex_article-Spalten einfuegen, falls nicht schon vorhanden
   $cols=$sql->getArray('SHOW COLUMNS FROM '.$table2);
   $col[0]='';
   $col[1]='';
   $col[2]='';
   for($i=0;$i<count($cols);$i=$i+1):
      if($cols[$i]['Field']==REWRITER_DIR)  $col[0]=$cols[$i]['Field'];
      if($cols[$i]['Field']==REWRITER_BASE) $col[1]=$cols[$i]['Field'];
      if($cols[$i]['Field']==REWRITER_URL)  $col[2]=$cols[$i]['Field'];
      endfor;
   $err_column='';
   for($i=0;$i<count($metainfos);$i=$i+1):
      $name=$metainfos[$i]['name'];
      if($name!=$col[$i]):
        $query='ALTER TABLE '.$table2.' ADD '.$name.' VARCHAR(255)';
        self::sql_action($sql,$query);
        else:
        $err_column=$err_column.', '.$col[$i];
        endif;
      endfor;
   #
   # --- Meldung ueber schon vorhandene rex_article-Spalten
   if(!empty($err_column))
     $err_column='<tr><td '.$stx.'>Schon vorhandene <b>rex_article-Parameter</b> '.
        '( nicht aktualisiert (!) ):</td><td '.$stx.'>'.substr($err_column,2).'</td></tr>';
   #
   # --- Ausgabe der ggf. schon vorhandenen MetaInfos/rex_article-Spalten
   if(!rex::isBackend() and (!empty($err_meta) or !empty($err_column)))
     echo rex_view::warning('<table cellpadding="0" cellspacing="0">'.
        $err_meta.$err_column.'</table>');
   }
public static function sql_action($sql,$query) {
   #   Ausfuehrung einer SQL-Aktion mittels setQuery()
   #   ggf. Ausgabe einer Fehlermeldung
   #   $sql               SQL-Handle
   #   $query             SQL-Aktion
   #
   try {
        $sql->setQuery($query);
        $error='';
         } catch(rex_sql_exception $e) {
        $error=$e->getMessage();
        }
   if(!empty($error)) echo rex_view::error($error);
   }
public static function default_config() {
   #   Rueckgabe der Default-Konfigurations-Parameter
   #
   $defconf=array(
      DEFAULT_EXTENSION=>'html php css',
      DEFAULT_STARTNAME=>'index',
      CLANG_PARAMETER  =>CLANG_VALUE,
      CLANG_MODE       =>3);            // ***
   # ***
   #   Art der Sprachkennzeichnung eines Artikels im Frontend
   #   moegliche Darstellungsformen des URLs:
   #      Standardsprache:   URL-String=Stamm-URL
   #                         (kein Sprachhinweis im URL, keine Session-Variable)
   #      Sonstige Sprachen: der URL-String enthaelt den Sprach-Code oder
   #                         eine Session-Variable enthaelt den Sprachcode:
   #         CLANG_MODE=1:  URL=/code/Stamm-URL
   #                   =2:  URL=Stamm-URL?CLANG_VALUE=code
   #                   =3:  URL=Stamm-URL, Session-Variable: CLANG_VALUE=code
   #                        Aenderung der Session-Variable durch URL-Parameter
   #                        ...?CLANG_VALUE=code
   return $defconf;
   }
public static function configuration() {
   #   einlesen und speichern der Konfigurationsparameter
   #   benutzte functions:
   #      self::default_config()
   #      self::allowed_name()
   #      self::allowed_chars()
   #
   # --- Default-Konfiguration
   $defconf=self::default_config();
   $key=array_keys($defconf);
   for($i=0;$i<count($key);$i=$i+1) $defval[$i]=$defconf[$key[$i]];
   #
   # --- Auslesen der gesetzen Konfiguration
   for($i=0;$i<count($key);$i=$i+1) $confval[$i]=rex_config::get(REWRITER,$key[$i]);
   #
   # --- Einlesen der gesetzten Formularwerte
   $save='';
   $reset='';
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
   $warn=array('','','','');
   if(!empty($val[0])):
     for($i=0;$i<count($arrext);$i=$i+1):
        if(!empty($save) and !self::allowed_name($arrext[$i])):
          $warn[0]='nicht erlaubte Zeichen in <code>'.$val[0].
             '</code>, Parameter zurückgesetzt';
          break;
          endif;
        endfor;
     endif;
   if(!empty($warn[0])) $val[0]=$confval[0];
   for($i=1;$i<=2;$i=$i+1):
      if(!empty($save) and !self::allowed_name($val[$i]) and !empty($val[$i])):
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
   if(!empty($reset))
     $warn[3]='zurückgesetzte Parameter sind <code>noch nicht gespeichert</code>'; 
   #
   # --- Abfragetexte
   for($i=1;$i<=3;$i=$i+1):
      $c[$i]='<tt>';
      $d[$i]='</tt>';
      $stc[$i]=' style="color:grey;"';
      if($val[3]==$i):
        $c[$i]='<code>';
        $d[$i]='</code>';
        $stc[$i]='';
        endif;
      endfor;
   $code=rex_clang::get(count(rex_clang::getAll()))->getCode();
   $stdcode=rex_clang::get(1)->getCode();
   $stx='style="padding-left:30px; margin-bottom:0px; white-space:nowrap;"';
   $sty='style="padding-left:20px; vertical-align:top;"';
   $bl='            ';
   $ext='.'.$arrext[0];
   if(empty($val[0])) $ext='';
   $text[0]='
erlaubte Erweiterungen für den &quot;Dateinamen&quot; eines Artikels<br/>
(durch Leerzeichen separiert)
<div '.$stx.'>
Beispiel: <tt>'.REWRITER_BASE.'=xxxxxx</tt><code>'.$ext.'</code><br/>
(können entfallen, in dem Fall entfällt auch der Punkt)</div>';
   $text[1]='
Stamm für den &quot;Dateinamen&quot; des Startartikels einer Kategorie
<div '.$stx.'>
also &quot;Dateiname&quot; eines Startartikels: <code>'.$val[1].'</code><tt>'.$ext.'</tt></br/>
(entfällt bei leerer [erster] Namenserweiterung)</div>';
   $text[2]='
Name für den URL-Parameter bzw. die Session-Variable
<div '.$stx.'>zur Kennzeichnung der Sprache</div>';
   $text[3]='
Art der Kennzeichnung (=1/2/3)<br/>
<div '.$stx.'>
für den Sprachcode steht im Folgenden: <tt>'.$code.'</tt></div>';
   $textpl='<div '.$stx.'>
<ol '.$stx.'>
    <li'.$stc[1].'>vordere Erweiterung des URL-Pfades um den Sprachcode<br/>
        Beispiel-URL: '.$c[1].$code.'/cat_dirname1/.../xxxxxx'.$ext.$d[1].'</li>
    <li'.$stc[2].'>Eweiterung des URL um einen Sprachcode-Parameter<br/>
        Beispiel-URL: '.$c[2].'cat_dirname1/.../xxxxxx'.$ext.'?'.$val[2].'='.$code.$d[2].'</li>
    <li'.$stc[3].'>Ergänzung des URL um eine Sprachcode-Session-Variable<br/>
        Beispiel-URL: '.$c[3].'cat_dirname1/.../xxxxxx'.$ext.$d[3].'<br/>
        Beispiel Session-Variable: '.$c[3].'$_SESSION[\''.$val[2].'\']=\''.$code.'\''.$d[3].'</li>
</ol>
Für die Standardsprache <tt>'.$stdcode.'</tt> entfallen die Kennzeichnungen 1, 2 im URL.<br/>
Falls nur eine Sprache definiert ist, entfallen alle Kennzeichnungen.</div>';
   #
   # --- Formularanfang
   $string='
<script>
    function fill(text,toid) {
        document.getElementById(toid).value = text;
    }
</script>
<form method="post">';
   echo $string;
   #
   # --- erlaubte Zeichen
   $string='
<table style="background-color:inherit;">
    <tr><td colspan="2">
            <b>Erlaubte Zeichen für die folgenden Parameter:</b>
            <div '.$stx.'>
            '.self::allowed_chars().'</div></td></tr>';
   #
   # --- Hinweis nach Reset
   $string=$string.'';
   if(!empty($warn[3])) $string=$string.'
    <tr><td colspan="2"><br/>'.rex_view::warning($warn[3]).'</td></tr>';
   echo $string;
   #
   # --- Konfigurationsparameter 0,1,2
   $string='
    <tr><td colspan="2">
            <b>Parameter zur Konstruktion der URLs:</b></td></tr>';
   for($i=0;$i<count($key)-1;$i=$i+1):
      $width=100;
      if($i==0) $width=150;
      $string=$string.'
       <tr><td '.$stx.'>'.$text[$i].'</td>
           <td '.$sty.' align="right">
               <input style="width:'.$width.'px;" id="'.$key[$i].'" name="'.$key[$i].'" value="'.$val[$i].'" /></td></tr>';
      if(!empty($warn[$i])) $string=$string.'
        <tr><td colspan="2" '.$stx.'>'.rex_view::warning($warn[$i]).'</td></tr>';
      if($i==1) $string=$string.'
    <tr><td colspan="2">
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
   $string='    <tr><td '.$stx.'>'.$text[3].'</td>
        <td '.$sty.' align="right">
            <select id="'.$key[3].'" name="'.$key[3].'">'.$stropt.'
            </select></td></tr>
    <tr><td colspan="2" '.$stx.'>
            '.$textpl.'<br/></td></tr>';
   echo $string;
   #
   # --- Submit-Button, Reset-Button und Formular-Abschluss
   $setstring='';
   for($i=0;$i<count($key);$i=$i+1)
      $setstring=$setstring.'
                        var v=\''.$defval[$i].'\'; k=\''.$key[$i].'\'; fill(v,k);';
   $string='
    <tr><td '.$stx.'>
            <button class="btn btn-save" type="submit" name="save" value="save"
                    title="Parameter speichern"> speichern </button></td>
        <td '.$sty.' align="right">
            <button class="btn btn-update" name="reset" value="reset"
                    title="Eingabefelder auf Defaultwerte zurücksetzen"
                    onclick="'.$setstring.'"> zurücksetzen </button></td></tr>
</table>
</form>';
   echo $string;
   #
   # --- Uebersschreiben der Konfiguration
   if(!empty($save)):
     for($i=0;$i<count($key)-1;$i=$i+1) rex_config::set(REWRITER,$key[$i],$val[$i]);
     rex_config::set(REWRITER,$key[3],intval($val[3]));
     endif;
   }
public static function allowed_name($string) {
   #   Ueberpruefen, ob ein gegebener String einem gueltigen Namen fuer einen
   #   art_basename entspricht. Ein gueltiger Name ist nicht leer und besteht
   #   ausschliesslich aus diesen Zeichen:
   #   - alle grossen und kleinen Buchstaben ausser irgendwelchen Umlauten
   #     ASCII-Nummern: chr(65)=A, chr(90)=Z, chr(97)=a, chr(122)=z
   #   - alle Ziffern
   #   - Punkt (.), Minuszeichen (-), Unterstrich (_)
   #
   if(strlen($string)<=0) return FALSE;
   for($i=1;$i<=strlen($string);$i=$i+1):
      $k=$i-1;
      $zeichen=substr($string,$k,1);
      $g=FALSE;
      if($zeichen=='.') $g=TRUE;
      if($zeichen=='-') $g=TRUE;
      if($zeichen=='_') $g=TRUE;
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
public static function allowed_chars() {
   #   Rueckgabe eines Info-Strings ueber erlaubte Zeichen in den Meta Infos:
   #   - alle grossen und kleinen Buchstaben ausser irgendwelchen Umlauten
   #   - alle Ziffern
   #   - Punkt (.), Minuszeichen (-), Unterstrich (_)
   #
   return 'Buchstaben, Ziffern, Punkt(.), Minuszeichen(-), '.
      'Unterstrich(_), <u>nicht erlaubt sind Umlaute oder Leerzeichen</u>';
   }
}
?>
