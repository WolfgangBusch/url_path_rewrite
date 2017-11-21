<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
#
# --- Default-Konfiguration
$defconf=path_rewrite_default_config();
$key=array_keys($defconf);
for($i=0;$i<count($key);$i=$i+1) $defval[$i]=$defconf[$key[$i]];
#
# --- Auslesen der gesetzen Konfiguration
for($i=0;$i<count($key);$i=$i+1) $confval[$i]=rex_config::get(REWRITER,$key[$i]);
#
# --- Einlesen der gesetzten Formularwerte
for($i=0;$i<count($key);$i=$i+1) $val[$i]=$_POST["$key[$i]"];
if(empty($val[0]) and empty($val[1]) and empty($val[2]) and empty($val[3]))
  for($i=0;$i<=count($key);$i=$i+1) $val[$i]=$confval[$i];
$sendit=$_POST["sendit"];
#
# --- nicht erlaubte Werte ermitteln, Warnungen setzen
$arrext=explode(" ",$val[0]);
$warn[0]="";
if(!empty($val[0])):
  for($i=0;$i<count($arrext);$i=$i+1):
     if(!empty($sendit) and !path_rewrite_allowed_name($arrext[$i])):
       $warn[0]="nicht erlaubte Zeichen in <code>".utf8_decode($val[0]).
          "</code>, Parameter zurückgesetzt";
       break;
       endif;
     endfor;
  endif;
if(!empty($warn[0])) $val[0]=$confval[0];
for($i=1;$i<=2;$i=$i+1):
   $warn[$i]="";
   if(!empty($sendit) and !path_rewrite_allowed_name($val[$i]) and !empty($val[$i])):
     $warn[$i]="nicht erlaubte Zeichen in <code>".utf8_decode($val[$i]).
        "</code>, Parameter zurückgesetzt";
     $val[$i]=$confval[$i];
     endif;
   endfor;
if(empty($val[1]) and !empty($val[0])):
  $warn[0]="bei leerem Namensstamm eines Startartikels muss auch die Namenserweiterung leer sein";
  $val[0]="";
  endif;
if(empty($val[0]) and !empty($val[1])):
  $warn[1]="bei leerer Namenserweiterung muss auch der Namensstamm eines Startartikels leer sein";
  $val[1]="";
  endif;
if(empty($val[2])):
  $warn[2]="Parameter darf nicht leer sein, zurückgesetzt";
  $val[2]=$confval[2];
  endif;
#
# --- Abfragetexte
for($i=1;$i<=3;$i=$i+1):
   $c[$i]="<tt>";
   $d[$i]="</tt>";
   $stc[$i]=" style=\"color:grey;\"";
   if($val[3]==$i):
     $c[$i]="<code>";
     $d[$i]="</code>";
     $stc[$i]="";
     endif;
   endfor;
$code=rex_clang::get(count(rex_clang::getAll()))->getCode();
$stdcode=rex_clang::get(1)->getCode();
$stx="style=\"padding-left:30px; margin-bottom:0px; white-space:nowrap;\"";
$sty="style=\"padding-left:20px; vertical-align:top;\"";
$bl="            ";
$ext=".".$arrext[0];
if(empty($val[0])) $ext="";
$text[0]="\n".
   $bl."erlaubte Erweiterungen für den &quot;Dateinamen&quot; eines Artikels\n".
   $bl."<div ".$stx.">\n".
   $bl."Beispiel: <tt>".REWRITER_BASE."=xxxxxx</tt><code>".$ext."</code><br/>\n".
   $bl."(können entfallen, in dem Fall entfällt auch der Punkt)</div>";
$text[1]="\n".
   $bl."Stamm für den &quot;Dateinamen&quot; des Startartikels einer Kategorie\n".
   $bl."<div ".$stx.">\n".
   $bl."also &quot;Dateiname&quot; eines Startartikels: <code>".$val[1]."</code><tt>".$ext."</tt></br/>\n".
   $bl."(entfällt bei leerer [erster] Namenserweiterung)</div>";
$text[2]="\n".
   $bl."Name für den URL-Parameter bzw. die Session-Variable\n".
   $bl."<div ".$stx.">zur Kennzeichnung der Sprache</div>";
$text[3]="\n".
   $bl."Art der Kennzeichnung (=1/2/3)<br/>\n".
   $bl."<div ".$stx.">\n".
   $bl."für den Sprachcode steht im Folgenden: <tt>".$code."</tt></div>";
$textpl=
   "<div ".$stx.">\n".
   $bl."<ol ".$stx.">\n".
   $bl."    <li".$stc[1].">vordere Erweiterung des URL-Pfades um den Sprachcode<br/>\n".
   $bl."        Beispiel-URL: ".$c[1].$code."/cat_dirname1/.../xxxxxx".$ext.$d[1]."</li>\n".
   $bl."    <li".$stc[2].">Ergänzung des URL um einen Sprachcode-Parameter<br/>\n".
   $bl."        Beispiel-URL: ".$c[2]."cat_dirname1/.../xxxxxx".$ext."?".$val[2]."=".$code.$d[2]."</li>\n".
   $bl."    <li".$stc[3].">Ergänzung des URL um eine Sprachcode-Session-Variable<br/>\n".
   $bl."        Beispiel-URL: ".$c[3]."cat_dirname1/.../xxxxxx".$ext.$d[3]."<br/>\n".
   $bl."        Beispiel Session-Variable: ".$c[3]."\$_SESSION['".$val[2]."']='".$code."'".$d[3]."</li>\n".
   $bl."</ol>\n".
   $bl."Für die Standardsprache <tt>".$stdcode."</tt> entfallen die Kennzeichnungen 1, 2 im URL.<br/>\n".
   $bl."Falls nur eine Sprache definiert ist, entfallen alle Kennzeichnungen.</div>";
#
# --- Formularanfang
$string='
<script>
    function fill(text,toid) {
        document.getElementById(toid).value = text;
    }
</script>
<form method="post">';
echo utf8_encode($string);
#
# --- Konfigurationsparameter 0,1,2
$string='
<table>
    <tr><td colspan="2">
            <b>Erlaubte Zeichen für die folgenden Parameter:</b>
            <div '.$stx.'>
            '.path_rewrite_allowed_chars().'</div></td></tr>
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
     <tr><td colspan="2">'.rex_view::warning($warn[$i]).'</td></tr>';
   if($i==1) $string=$string.'
    <tr><td colspan="2">
            <b>Parameter zur Kennzeichnung der Sprache:</b></td></tr>';
   endfor;
echo utf8_encode($string);
#
# --- Konfigurationsparameter 3
$stropt="";
for($i=1;$i<=3;$i=$i+1):
   $selst[$i]="";
   if($i==$val[3]) $selst[$i]="selected=\"selected\"";
   $stropt=$stropt.'
                <option '.$selst[$i].' value="'.$i.'">'.$i.'</option>';
   endfor;
$string='    <tr><td '.$stx.'>'.$text[3].'</td>
        <td '.$sty.' align="right">
            <select id="'.$key[3].'" name="'.$key[3].'">'.$stropt.'
            </select></td></tr>
    <tr><td colspan="2" '.$stx.'>
            '.$textpl.'<br/></td></tr>';
echo utf8_encode($string);
#
# --- Submit-Button, Reset-Button und Formular-Abschluss
$string='';
for($i=0;$i<count($key);$i=$i+1)
   $string=$string.'
                        var v=\''.$defval[$i].'\'; k=\''.$key[$i].'\'; fill(v,k);';
$string='
    <tr><td '.$stx.'>
            <button class="btn btn-save" type="submit" name="sendit" value="sendit"
                    title="Parameter speichern"> speichern </button></td>
        <td '.$sty.' align="right">
            <button title="Eingabefelder auf Defaultwerte zurücksetzen"
                    class="btn btn-update" onclick="'.$string.'">
            zurücksetzen</button></td></tr>
</table>
</form>';
echo utf8_encode($string);
#
# --- Uebersschreiben der Konfiguration
if(!empty($sendit))
  for($i=0;$i<count($key);$i=$i+1) rex_config::set(REWRITER,$key[$i],$val[$i]);
?>
