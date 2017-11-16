<?php
/**
 * URL-Rewrite AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version November 2017
 */
$string='
<div><b>Die Rewrite-Regeln</b></div>
<div style="padding-left:30px;">werden in der Datei <code>.htaccess</code>
abgelegt, die mindestens diese Zeilen enthalten muss:</div>
<div style="padding-left:60px; font-family:monospace;">
&lt;IfModule mod_rewrite.c&gt;                  <br/>
RewriteEngine On                                <br/>
RewriteBase /                                   <br/>
RewriteCond %{REQUEST_FILENAME} !-f             <br/>
RewriteCond %{REQUEST_FILENAME} !-d             <br/>
RewriteCond %{REQUEST_FILENAME} !-l             <br/>
RewriteCond %{REQUEST_URI} !^redaxo/.*          <br/>
RewriteCond %{REQUEST_URI} !^media/.*           <br/>
RewriteRule ^(.*)$ index.php?%{QUERY_STRING} [L]<br/>
&lt;/IfModule&gt;
</div>
<br/>
';
echo utf8_encode($string);
?>
