<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juni 2019
 */
#
# --- hierarchische Liste der functions
#     select_lang()
#        url_parameterliste($par1,$par2,$par3)
#        is_normurl()                          (class url_rewrite)
#     select_url()
#        url_parameterliste($par1,$par2,$par3)
#        is_normurl()                          (class url_rewrite)
#        mode_url_clang($url,$clang_id)        (class url_rewrite)
#
function select_lang() {
   #   Rueckgabe eines HTML-Codes fuer ein Select-Menue
   #   zur Sprachauswahl im aktuellen Artikel.
   #   benutzte functions:
   #      url_parameterliste($par1,$par2,$par3)
   #      url_rewrite::is_normurl()
   #
   # --- Artikel-Id, Sprach-Id, URL
   $art_id  =rex_article::getCurrentId();
   $clang_id=rex_clang::getCurrentId();
   $url     =rex_getUrl($art_id,$clang_id);
   #
   # --- URL-Stamm (ohne Parameter bei konfiguriertem URL)
   $mode=rex_config::get(REWRITER,CLANG_MODE);
   if(url_rewrite::is_normurl()) $mode=0;  // Redaxo-Standard-URL
   if($mode==1 and $clang_id>1):
     $code=rex_clang::get($clang_id)->getCode();
     $url=substr($url,strlen($code)+1);
     endif;
   if($mode==2):
     $arr=explode('?',$url);
     $url=$arr[0];
     endif;
   #
   # --- option-Liste
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $opt='';
   foreach(rex_clang::getAll() as $key=>$lang):
          $id  =$lang->getId();
          $code=$lang->getCode();
          $sel='';
          if($id==$clang_id) $sel=' selected="selected"';
          if($mode<=0):
            # --- Redaxo-Standard-URL
            $urlid='/index.php?article_id='.$art_id.'&clang='.$id;
            #     URL-Parameter neu zusammenstellen
            $par=url_parameterliste($parlang,'article_id','clang');
            if(!empty($par)) $urlid=$urlid.'&'.$par;
            ;else:
            # --- Custom URL
            $urlid=$url;
            if($mode==1 and $id>1) $urlid='/'.$code.$urlid;
            #     URL-Parameter bis auf 'language' auslesen und neu zusammenstellen
            $par=url_parameterliste($parlang);
            #     URL-Parameter neu setzen
            if($mode>=2):
              if(empty($par)):
                $par=$parlang.'='.$code;
                else:
                $par=$parlang.'='.$code.'&'.$par;
                endif;
              endif;
            if(!empty($par)) $urlid=$urlid.'?'.$par;
            endif;
          $opt=$opt.'
    <option'.$sel.' value="'.$urlid.'">'.
             rex_clang::get($id)->getName().'</option>';
          endforeach;
   #
   # --- select-Menue
   $form='<select onchange="window.open(this.options[this.selectedIndex].value,\'_self\');">'.
$opt.'
</select>';
   return $form;
   }
function select_url() {
   #   Rueckgabe eines HTML-Codes fuer ein Select-Menue zum Wechsel
   #   zwischen Custom URL und Redaxo-Standard-URL im aktuellen Artikel.
   #   benutzte functions:
   #      url_parameterliste($par1,$par2,$par3)
   #      url_rewrite::is_normurl()
   #      url_rewrite::mode_url_clang($url,$clang_id)
   #
   #
   # --- Artikel-Id und Sprach-Id
   $art_id  =rex_article::getCurrentId();
   $clang_id=rex_clang::getCurrentId();
   #
   # --- Custom URL
   $article=rex_article::get($art_id,$clang_id);
   $murl=$article->getValue(REWRITER_URL);
   #     Sprachkennzeichnung ergaenzen
   $murl='/'.url_rewrite::mode_url_clang($murl,$clang_id);
   #
   # --- Standard-URL
   $nurl='/index.php?article_id='.$art_id.'&clang='.$clang_id;
   #
   # --- aktuelle URL-Form bestimmen
   if(url_rewrite::is_normurl()):
     #     Standard-URL
     $seln='selected="selected"';
     $selm='';
     else:
     #     Custom URL
     $seln='';
     $selm='selected="selected"';
     endif;
   #
   # --- Parameterliste bestimmen und anhaengen
   #     Standard-URL
   $parlang=rex_config::get(REWRITER,CLANG_PARAMETER);
   $npar=url_parameterliste($parlang,'article_id','clang');
   if(!empty($npar)) $nurl=$nurl.'&'.$npar;
   #     Custom URL
   $mpar=url_parameterliste('article_id','clang');
   if(!empty($mpar)) $murl=$murl.'?'.$mpar;
   #
   # --- select-Menue
   $opt='    <option '.$selm.' value="'.$murl.'">konfigurierter URL</option>
    <option '.$seln.' value="'.$nurl.'">Standard-URL</option>';
   $form='<select onchange="window.open(this.options[this.selectedIndex].value,\'_self\');">
'.$opt.'
</select>';
   return $form;
   }
function url_parameterliste($par1=FALSE,$par2=FALSE,$par3=FALSE) {
   #   Rueckgabe der Parameterliste des URLs der aktuellen Seite,
   #   wobei ausgewaehlte Parameter optional ausgelassen werden.
   #   $par1              1. nicht zurueck zu gebender Parameter
   #   $par2              2. nicht zurueck zu gebender Parameter
   #   $par3              3. nicht zurueck zu gebender Parameter
   #
   $par='';
   $arr=explode('?',$_SERVER['REQUEST_URI']);
   if(!empty($arr[1])):
     parse_str($arr[1],$arr);
     $keys=array_keys($arr);
     for($i=0;$i<count($keys);$i=$i+1):
        $key=$keys[$i];
        if($key!=$par1 and $key!=$par2 and $key!=$par3)
          $par=$par.'&'.$key.'='.$arr[$key];
        endfor;
     $par=substr($par,1);
     endif;
   return $par;
   }
?>
