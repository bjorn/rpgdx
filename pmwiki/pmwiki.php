<?php
/*
    PmWiki
    Copyright 2001-2003 Patrick R. Michaud 
    pmichaud@pobox.com
    http://www.pmichaud.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if (ini_get('register_globals')) {
  foreach($HTTP_POST_VARS as $k=>$v) { unset(${$k}); }
  foreach($HTTP_GET_VARS as $k=>$v) { unset(${$k}); }
  foreach($HTTP_COOKIE_VARS as $k=>$v) { unset(${$k}); }
}
$UnsafeGlobals = array_keys($GLOBALS);
@include_once("scripts/version.php");
$WikiTitle = "PmWiki";
$DefaultGroup = "Main";
$DefaultTitle = "HomePage";
$ScriptUrl = 'http://'.$HTTP_SERVER_VARS['HTTP_HOST'];
$ScriptUrl .= $HTTP_SERVER_VARS['SCRIPT_NAME'];
$ScriptDir = preg_replace("#/[^/]*\$#","",$ScriptUrl,1);
$WikiImgUrl = "$ScriptDir/pmwiki-50.gif";
$DiffKeepDays = 3650;
$WikiDir = "wiki.d";
$WikiLibDirs = array(&$WikiDir,"wikilib.d");
$BodyWidth = 600;
$BodyLeft = 20;
$DeleteKeyWord = "delete";
$RedirectDelay = 0;
$AuthFunction = 'BasicAuth';
$AllowPassword = 'nopass';
$SysDiffCmd = '/usr/bin/diff';
$SysPatchCmd = '/usr/bin/patch --silent';
$DefaultPasswords = array('admin'=>'*','attr'=>'','edit'=>'','read'=>'');
$AuthRealmFmt = '$WikiTitle - $Group';
$AuthDeniedFmt = 'A valid password is required to access this feature.';
$DefaultPageTextFmt = '$[Describe $Tlink here.]';
$GroupHeaderFmt = '$Group.GroupHeader';
$GroupFooterFmt = '$Group.GroupFooter';
$GroupAttributesFmt = '$Group.GroupAttributes';
$TimeFmt = "%B %d, %Y, at %I:%M %p";
$HTTPHeaders=array(
  "Expires: Tue, 01 Jan 2002 00:00:00 GMT",
  "Last-Modified: ".gmstrftime('%a, %d %b %Y %H:%M:%S GMT'),
  "Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0",
  "Pragma: no-cache",
  "Content-Type: text/html; charset=iso-8859-1;");
$HTMLDoctypeFmt = 
  "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" 
    \"http://www.w3.org/TR/html40/loose.dtd\">
  <html><head>";
$HTMLTitleFmt = "<title>\$WikiTitle - \$HTMLTitle</title>";
$HTMLHeaderFmt = "<style type='text/css'>
  HR { text-align:left; }
  .wikiheader { font-size:32px; font-weight:bold; }
  .wikiops, .wikifooter { font-size:13px; }
  .wikibody { margin-left:\$BodyLeftpx; width:\$BodyWidthpx; }
  .revdate { font-family:verdana,sans-serif; font-size:11px; }
  .revtype { font-family:verdana,sans-serif; font-size:11px; font-weight:bold; }
  .revadd { background:#99ff99; color:black; }
  .revdel { background:#ffff99; color:black; }
</style>";
$HTMLBodyFmt = "</head><body bgcolor='#ffffff'><div class='wikibody'>";
$HTMLStartFmt = array('headers:',&$HTMLDoctypeFmt,&$HTMLTitleFmt,
  &$HTMLHeaderFmt,&$HTMLBodyFmt);
$HTMLEndFmt = "</div></body></html>";
$PageNameFmt = '$Group.$Title_';
$PageFileFmt = '$PageName';
$PageUrlFmt = '$ScriptUrl/$Group/$Title_';
$PageHeaderFmt = "
  <table width='\$BodyWidth' cellpadding='0' cellspacing='0' border='0'>
  <tr><td valign='bottom' align='left' width='10%'>\$WikiImg&nbsp;&nbsp;</td>
    <td valign='bottom' align='left'><a href='\$ScriptUrl/\$Group'>\$Group</a> /<br />
      <span class='wikiheader'><a href='\$PageUrl?action=search&amp;text=\$Title_'>\$Title</a></span></td>
    <td valign='bottom' align='right' class='wikiops'>
      <a href='\$ScriptUrl/$[Main/SearchWiki]'>$[SearchWiki]</a><br />
      <a href='\$ScriptUrl/$[\$Group/RecentChanges]'>\$Group.$[RecentChanges]</a><br />
      <a href='\$PageUrl?action=edit'>$[Edit Page]</a><br />
      <a href='\$PageUrl?action=diff'>$[Page Revisions]</a></td>
   </tr></table><hr /><p />";
$PageTitleFmt = '';
$PageRedirectFmt = "<i>($[redirected from] <a href='\$PageUrl?action=edit'>\$PageName</a>)</i><p />\n";
$PageFooterFmt = "<hr /><small>
  <a href='\$PageUrl?action=edit'>$[Edit Page]</a> - 
  <a href='\$PageUrl?action=diff'>$[Page Revisions]</a> -
  <a href='\$ScriptUrl/$[PmWiki/WikiHelp]'>$[WikiHelp]</a> -
  <a href='\$ScriptUrl/$[Main/SearchWiki]'>$[SearchWiki]</a> -
  <a href='\$ScriptUrl/$[\$Group/RecentChanges]'>$[RecentChanges]</a><br />
  $[Page last modified on \$LastModified]</small>";
$PageEditFmt = "<a id='top' name='top'><a /><h1>Editing \$PageName</h1>
  <form action='\$PageUrl' method='post'>
  <input type='hidden' name='pagename' value='\$PageName' />
  <input type='hidden' name='action' value='edit' />
  <textarea name='text' rows='25' cols='80'
    onkeydown='if (event.keyCode == 27) event.returnValue=false;'
    >\$Text</textarea><br />
  <input type='submit' name='post' value=' $[Save] ' />
  <input type='submit' name='preview' value=' $[Preview] ' />
  <input type='reset' value=' $[Reset] ' />
  </form>";
$PagePreviewFmt = array(
  "function:ProcessTextDirectives",
  "<h2>Preview \$PageName</h2><b>Page is unsaved</b><hr /><p />",
  "function:PrintText",
  "<hr /><b>End of preview -- remember to save</b>
   <br /><a href='#top'>$[Top]</a>");
$PageDiffFmt = "<h1><a href='\$PageUrl'>\$PageName</a> $[Revisions]</h1>";
$PageDiffFootFmt = "<p /><hr />$[Back to] <a href='\$PageUrl'>\$PageName</a>";
$PageAttrFmt = "<h1>$[\$PageName Attributes]</h1>
    <p>Enter new attributes for this page below.  Leaving a field blank
    will leave the attribute unchanged.  To clear an attribute, enter 
    'clear'.</p>";
$HandleBrowseFmt = array(&$HTMLStartFmt,&$PageHeaderFmt,&$PageTitleFmt,
  &$PageRedirectFmt,"function:PrintText",&$PageFooterFmt,&$HTMLEndFmt);
$HandleEditFmt = array(&$HTMLStartFmt,&$PageEditFmt,
  "wiki:$[PmWiki.EditQuickReference]",&$PagePreviewFmt,&$HTMLEndFmt);
$XLLangs = array('en');

$FmtWikiLink = 'FmtWikiLink';
$FmtUrlLink = 'FmtUrlLink';
$WikiPageExistsFmt = "<a href='\$PageUrl\$Fragment'>\$LinkText</a>";
$WikiPageCreateFmt = 
  "\$LinkText<a href='\$PageUrl?action=edit'>?</a>";
$WikiPageCreateSpaceFmt =
  "\$LinkText<a href='\$PageUrl?action=edit'>?</a>";
$UrlLinkFmt = "<a href='\$Url'>\$LinkText</a>";
$UrlImgFmt = "<img src='\$Url' border='0' alt='' img>";
$GroupNamePattern="[A-Z][A-Za-z0-9]+";
$WikiWordPattern="[A-Z][A-Za-z0-9]*(?:[A-Z][a-z0-9]|[a-z0-9][A-Z])[A-Za-z0-9]*";
$FreeLinkPattern="{{(?>([A-Za-z][A-Za-z0-9]*(?:(?:[\\s_]*|-)[A-Za-z0-9]+)*)(?:\\|((?:(?:[\\s_]*|-)[A-Za-z0-9])*))?)}}((?:-?[A-Za-z0-9]+)*)";
$FragmentPattern="#[A-Za-z][-.:\\w]*";
$PageTitlePattern="[A-Z][A-Za-z0-9]*(?:-[A-Za-z0-9]+)*";
$UrlPathPattern="[^\\s<>[\\]\"\'()]*[^\\s<>[\\]\"\'(),.?]";
$UrlMethodPattern="http|ftp|news|file|gopher|nap|https";
$ImgExtPattern="\\.(gif|jpg|jpeg|png)";
$InterMapFiles=array('scripts/intermap.txt','localmap.txt',
  'local/localmap.txt');
$LinkPatterns=array();

$RecentChanges = array(
  "Main.AllRecentChanges"=>'$Group.$Tlink',
  '$Group.RecentChanges'=>'$Group/$Tlink');
$BrowseDirectives = array(
  '[[spacewikiwords]]' => '$GLOBALS["SpaceWikiWords"]=1;',
  '[[noheader]]' => '$GLOBALS["PageHeaderFmt"]="";',
  '[[notitle]]' => '$GLOBALS["PageTitleFmt"]="";',
  '[[nofooter]]' => '$GLOBALS["PageFooterFmt"]="";',
  '[[nogroupheader]]' => '', '[[nogroupfooter]]' => '');
$DoubleBrackets = array('[[$Group]]'=>'$Group', '[[$Title]]'=>'$Title',
  '[[$Groupspaced]]'=>'$Groupspaced','[[$Titlespaced]]'=>'$Titlespaced',
  '[[$Tlink]]'=>'$Tlink',
  '[[$LastModified]]'=>'$LastModified',
  '[[$LastModifiedHost]]'=>'$LastModifiedHost',
  '[[$Version]]'=>'$Version',
  '[[$DefaultGroup]]'=>'$DefaultGroup',
  '[[$Edit' => '[[$PageUrl?action=edit ',
  '[[$Diff' => '[[$PageUrl?action=diff ');
$InlineReplacements = array(
  "/'''''(.*?)'''''/" => "<em><strong>\$1</strong></em>",
  "/'''(.*?)'''/" => "<strong>\$1</strong>",
  "/''(.*?)''/" => "<em>\$1</em>",
  "/@@(.*?)@@/" => "<code>\$1</code>",
  "/\\[\\[&lt;&lt;\\]\\]/" => "<br clear='all' />",
  "/\\[(([-+])+)(.*?)\\1\\]/e" => 
    "'<font size=\'\$2'.strlen('\$1').'\'>'.str_replace('\\\"','\"','$3').'</font>'",
  "/^----+/" => "<hr />",
  "/&amp;([A-Za-z0-9]+;|#\d+;)/" => "&\$1");
$PageAttributes = array(
  'passwdread' => 'Set new read password:',
  'passwdedit' => 'Set new edit password:',
  'passwdattr' => 'Set new attribute password:');
$HandleActions = array(
  'edit' => 'HandleEdit', 'post' => 'HandlePost',
  'attr' => 'HandleAttr', 'postattr' => 'HandlePostAttr',
  'source' => 'HandleSource', 'diff' => 'HandleDiff');
$WikiStylePattern = '%%|%[A-Za-z][-,=#\\w\\s]*%';
$WikiStyleTags = array(
  'color' => array( 'style' => 'color:$value; ') , 
  'bgcolor' => array( 'style' => 'background-color:$value; '),
  'font-size' => array( 'style' => 'font-size:$value; '),
  'target' => array( 'a' => 'target=\'$value\' '),
  'rel' => array('a' => 'rel=\'$value\' '),
  'hspace' => array( 'img' => 'hspace=\'$value\' '),
  'vspace' => array( 'img' => 'vspace=\'$value\' ')
);

$Now = time(); 
$MaxIncludes = 10;
$TableAttr = "";
$TableCellAttr = "valign='top'";
$Newline = "\262";
$KeepToken = "\263";
$LinkToken = "\264";
$EnableStdConfig = 1;

umask(002);

$gvars = array('pagename','action','text','restore','preview');
foreach($gvars as $v) {
  if (isset($HTTP_GET_VARS[$v])) $$v=$HTTP_GET_VARS[$v];
  elseif (isset($HTTP_POST_VARS[$v])) $$v=$HTTP_POST_VARS[$v];
  else $$v = '';
}

$EnablePathInfo = !preg_match("/^cgi/",php_sapi_name());
if ($pagename=='' && $EnablePathInfo)
  $pagename = @substr($HTTP_SERVER_VARS['PATH_INFO'],1);
if ($action=='') $action='browse';

if (file_exists('local.php')) { include('local.php'); $LocalConf=1; }
elseif (file_exists('local/local.php')) 
  { include('local/local.php'); $LocalConf=1; }
if ($EnableStdConfig && file_exists('scripts/stdconfig.php')) 
  include_once('scripts/stdconfig.php');

mkgiddir($WikiDir);
if (!file_exists("$WikiDir/.htaccess") && 
    $fp=@fopen("$WikiDir/.htaccess","w")) {
  fwrite($fp,"Order Deny,Allow\nDeny from all\n");
  fclose($fp);
}
SDV($DefaultPage,"$DefaultGroup/$DefaultTitle");
SDV($WikiImg,FmtPageName(
  "<a href='\$PageUrl'><img src='$WikiImgUrl' alt='$WikiTitle' 
  border='0' /></a>",$DefaultPage));
SDV($UrlLinkTextFmt,$UrlLinkFmt);
SDV($RedirectPattern,"\\[\\[redirect:(\\S+)\\]\\]");

$LinkPatterns[200]["\\bmailto:($UrlPathPattern)"] = "<a href='$0'>$1</a>";
$LinkPatterns[300]["\\b($UrlMethodPattern):($UrlPathPattern)"] = $FmtUrlLink;

foreach($InterMapFiles as $mapfile) {
  if (@!($mapfd=fopen($mapfile,"r"))) continue;
  while ($mapline=fgets($mapfd,1024)) {
    if (preg_match("/^\\s*\$/",$mapline)) continue;
    list($mapid,$mapurl) = preg_split("/\\s+/",$mapline);
    $LinkPatterns[400]["\\b$mapid:($UrlPathPattern)"] = $FmtUrlLink;
    $InterMapUrls[$mapid] = $mapurl;
  }
  fclose($mapfd);
}

$LinkPatterns[500]["\\b($GroupNamePattern([\\/.]))?($FreeLinkPattern)($FragmentPattern)?"] = $FmtWikiLink;
$LinkPatterns[600]["$FreeLinkPattern($FragmentPattern)?"]  = $FmtWikiLink;
$LinkPatterns[700]["\\b$GroupNamePattern([\\/.])$WikiWordPattern($FragmentPattern)?"] = $FmtWikiLink;
$LinkPatterns[780]["\\[\\[#([A-Za-z][-.:\\w]*?)\\]\\]"] = 
  "<a name='$1' id='$1'></a>";
$LinkPatterns[780]["\\[\\[#([A-Za-z][-.:\\w]*?)\\s(.+?)\\]\\]"] =
  "<a href='#$1'>$2</a>";
$LinkPatterns[800]["\\b$WikiWordPattern($FragmentPattern)?"] = $FmtWikiLink;

$LastModified = strftime($TimeFmt,$Now);

if ($action=="crypt") { HandleCrypt(); exit; }

if ($pagename=='') $pagename=$DefaultPage;
if (preg_match("/^($GroupNamePattern)[\\/.]?\$/",$pagename,$match)) {
  if (PageExists($match[1].'.'.$match[1])) Redirect($match[1].'.'.$match[1]);
  else Redirect($match[1].".$DefaultTitle");
}
if (!preg_match("/^($GroupNamePattern)[\\/.]($PageTitlePattern)\$/",$pagename,
    $match))
  Abort("'$pagename' is not a valid PmWiki page name");
$pagename=FmtPageName('$PageName',$pagename);

$handle = @$HandleActions[$action];
if (function_exists($handle)) $handle($pagename);
else { HandleBrowse($pagename); }
EndHTML();
Lock(-1);

function SDV(&$var,$val) { if (!isset($var)) $var=$val; }
function stripmagic($s) 
  { return get_magic_quotes_gpc() ? stripslashes($s) : $s; } 

function mkgiddir($dir) {
  global $ForceMkdir;
  if (is_dir($dir)) return;
  if (!$ForceMkdir) {
    $parent = dirname($dir);
    $rparent = realpath($parent);
    $perms = fileperms($parent);
    if (umask()!=0 && posix_getegid()!=filegroup($parent) && 
        ($perms & 02000)==0) 
      Abort("PmWiki wants setgid permissions enabled on <tt>$rparent</tt><br />
        before it creates the <tt>$dir</tt> directory.  <br />
        Try executing <pre>    chmod 2777 $rparent</pre> 
        on your server and reloading this page.  Afterwards, you 
        can restore the permissions<br />to their current setting by executing
        <pre>    chmod ".decoct($perms & 03777)." $rparent</pre>If this
        doesn't work for you, see the link below.","Setgid");
  }
  mkdir($dir,0777) or 
    Abort("Cannot create <tt>$dir</tt><br />
      Current directory is <tt>".getcwd()."</tt>","Mkdir");
}

function Abort($msg,$aref = NULL) {
  StartHTML("","Program error");
  echo("<h3>PmWiki can't process your request</h3>
    <p>$msg</p><p>We are sorry for any inconvenience.</p>");
  if ($aref) {
    $href = "http://www.pmichaud.com/ref/PmWiki/$aref";
    echo("<p><a href='$href' target='_blank'>$href</a></p>");
  }
  EndHTML();
  exit();
}

function StartHTML($pagename,$title = "") {
  global $calledStartHTML; if ($calledStartHTML++) return;
  global $HTMLStartFmt;
  $GLOBALS['HTMLTitle']=$title; $GCount=0;
  PrintFmt($pagename,$HTMLStartFmt);
}

function EndHTML($pagename=NULL,$x=NULL) {
  static $called; if ($called++) return;
  global $calledStartHTML,$HTMLEndFmt;
  if ($calledStartHTML) PrintFmt($pagename,$HTMLEndFmt);
}

function Redirect($pagename,$urlfmt='$PageUrl') {
  global $RedirectDelay,$ScriptUrl,$DefaultPage;
  clearstatcache();
  if (!PageExists($pagename)) $pagename=$DefaultPage;
  $pageurl=FmtPageName($urlfmt,$pagename);
  header("Location: $pageurl");
  header("Content-type: text/html");
  print("<html><head>
    <meta http-equiv='Refresh' Content='$RedirectDelay; URL=$pageurl'>
    <title>Redirect</title></head><body></body></html>");
  exit;
}

function AsSpaced($word) {
  $word = str_replace('_',' ',$word);
  $word = preg_replace("/([a-z0-9])([A-Z])/","\$1 \$2",$word);
  $word = preg_replace("/([0-9]+( |\$))/"," \$1",$word);
  return preg_replace("/([A-Z])([A-Z][a-z0-9])/","\$1 \$2",$word);
}

function PrintFmt($pagename,$fmt) {
  global $HTTPHeaders;
  if (is_array($fmt)) 
    { foreach($fmt as $f) PrintFmt($pagename,$f); return; }
  $x = FmtPageName($fmt,$pagename);
  if (preg_match("/^headers:/",$x)) {
    foreach($HTTPHeaders as $h) (@$sent++) ? @header($h) : header($h);
    return; 
  }
  if (preg_match("/^function:(\S+)\s*(.*)\$/s",$x,$match) && 
      function_exists($match[1])) 
    { $f = $match[1]; $f($pagename,$match[2]); return; }
  if (preg_match("/^wiki:(.+)\$/s",$x,$match))
    { PrintWikiPage($pagename,$match[1]); return; }
  if (preg_match("/^file:(.+)/s",$x,$match)) {
    $filelist = preg_split('/[\\s]+/',$match[1],-1,PREG_SPLIT_NO_EMPTY);
    foreach($filelist as $f) {
      if (file_exists($f)) { include($f); return; }
    }
    return;
  }
  print $x;
}

function FmtUrlLink($pat,$ref,$txt) {
  global $InterMapUrls,$ImgExtPattern,$UrlLinkFmt,$UrlImgFmt,$UrlLinkTextFmt;
  $link = $UrlLinkFmt; $rtxt=$ref;
  if (!is_null($txt)) { $rtxt=$txt; $link=$UrlLinkTextFmt; }
  elseif (preg_match("/$ImgExtPattern\$/",$ref)) { $link=$UrlImgFmt; }
  if (preg_match('/^([^:]*):(.*)$/',$ref,$match)) {
    if (@$InterMapUrls[$match[1]]) $ref=$InterMapUrls[$match[1]].$match[2];
  }
  $link = str_replace('$Url',$ref,$link);
  return str_replace('$LinkText',$rtxt,$link);
}

function FmtWikiLink($pat,$ref,$btext,$out=NULL,$pname=NULL) {
  global $GroupNamePattern,$FreeLinkPattern,$PageNameSpace,$SpaceWikiWords,
    $WikiPageExistsFmt,$WikiPageCreateSpaceFmt,$WikiPageCreateFmt,
    $PageTitlePattern,$FragmentPattern,$Fragment,$GCount;
  $txt = ''; $Fragment='';
  if (preg_match("/$FragmentPattern\$/",$ref,$match)) {
    $Fragment=$match[0]; $GCount=0;
    $ref=str_replace($Fragment,'',$ref);
  }
  if (preg_match("/^($GroupNamePattern)([\\/.])/",$ref,$match)
      && $match[2]=='.') $txt=$match[1].'.';
  if (@$match[1]>"") $group=$match[1];
  else $group = preg_replace('/[\\/.].*$/','',
      ($pname>"") ? $pname : $GLOBALS['pagename']);
  if (preg_match("/$FreeLinkPattern\$/",$ref,$fl)) {
    $title=preg_replace('/[_\\s]+/',' ',$fl[1].$fl[2]);
    $title=str_replace(' ',$PageNameSpace,ucwords($title));
    $txt .= $fl[1].$fl[3];
  } else { 
    $title = preg_replace('/^.*[\\/.]/','',$ref); 
    $txt .= ($SpaceWikiWords) ? AsSpaced($title) : $title;
  }
  if ($out=='PageName') return "$group.$title";
  if (!preg_match("/^$PageTitlePattern\$/",$title)) return $ref;
  $txt .= $Fragment;
  if (!is_null($btext)) $txt=$btext;
  if (PageExists("$group.$title")) $fmt=$WikiPageExistsFmt;
  elseif (preg_match('/\\s/',$txt)) $fmt=$WikiPageCreateSpaceFmt;
  else $fmt=$WikiPageCreateFmt;
  return str_replace('$LinkText',$txt,FmtPageName($fmt,"$group.$title"));
}

function XL($key) {
  global $XL,$XLLangs;
  foreach($XLLangs as $l) { if (isset($XL[$l][$key])) return $XL[$l][$key]; }
  return $key;
}
function XLSDV($lang,$a) {
  global $XL;
  foreach($a as $k=>$v) { if (!isset($XL[$lang][$k])) $XL[$lang][$k]=$v; }
}

function FmtPageName($fmt,$pagename) {
  global $UnsafeGlobals,$GroupNamePattern,$PageTitlePattern,
    $PageUrlFmt,$EnablePathInfo,$PageNameFmt,$WikiWordPattern,$GCount;
  static $g;
  static $qk = array('$ScriptUrl','$Groupspaced','$Group',
    '$Titlespaced','$Title_','$Title','$Tlink');
  if (!is_null($pagename) && !preg_match("/^($GroupNamePattern)[\\/.]($PageTitlePattern)\$/",$pagename,$match)) return "";
  $fmt = preg_replace("/\\$\\[(.+?)\\]/e","XL(stripslashes('$1'))",$fmt);
  $qv = @array($GLOBALS['ScriptUrl'],AsSpaced($match[1]),$match[1],
    AsSpaced($match[2]),$match[2],str_replace('_',' ',$match[2]),
    preg_match("/^$WikiWordPattern\$/",$match[2]) ? $match[2] :
      '{{'.str_replace('_',' ',$match[2]).'}}');
  $fmt=str_replace('$PageUrl',$PageUrlFmt,$fmt);
  $fmt=str_replace('$PageName',$PageNameFmt,$fmt);
  if (isset($EnablePathInfo) && !$EnablePathInfo) 
    $fmt = preg_replace('!\\$ScriptUrl/([^\'"\\s?./]+)(?:[./]([^\'"\\s?]*))?(?:\\?([^\'"\\s]*))?!e',"'\$ScriptUrl?pagename=$1'.(('$2')?'.$2':'').(('$3')?'&amp;$3':'')",$fmt);
  if (phpversion()>="4.0.5") $fmt=str_replace($qk,$qv,$fmt);
  else for($i=0;$i<count($qk);$i++) $fmt=str_replace($qk[$i],$qv[$i],$fmt);
  if (strpos($fmt,'$')===false) return $fmt;
  if (count($GLOBALS)!=$GCount) {
    foreach($GLOBALS as $n=>$v) {
      if ($n == 'Text') continue;
      if (in_array($n,$UnsafeGlobals)) continue;
      if (is_array($v)) { $UnsafeGlobals[]=$n; continue; }
      $g["\$$n"]=$v;
    }
    $GCount = count($GLOBALS);
    krsort($g); reset($g);
  }
  if (isset($g[$fmt])) return $g[$fmt];
  if (phpversion()>="4.0.5") 
    $fmt = str_replace(array_keys($g),array_values($g),$fmt);
  else foreach($g as $n=>$v) $fmt=str_replace($n,$v,$fmt);
  $fmt = str_replace('$Text',
    @htmlspecialchars($GLOBALS['Text'],ENT_NOQUOTES),$fmt);
  return $fmt;
}

function Diff($oldtext,$newtext) {
  global $WikiDir,$SysDiffCmd;
  if (!$SysDiffCmd) return '';
  $tempold = tempnam($WikiDir,"old");
  if ($oldfp = fopen($tempold,"w")) { 
    fputs($oldfp,$oldtext); 
    fclose($oldfp); 
  }
  $tempnew = tempnam($WikiDir,"new");
  if ($newfp = fopen($tempnew,"w")) { 
    fputs($newfp,$newtext); 
    fclose($newfp); 
  }
  $diff = '';
  $diff_handle = popen("$SysDiffCmd $tempold $tempnew","r");
  if ($diff_handle) {
    while (!feof($diff_handle)) { $diff .= fread($diff_handle,1024); }
    pclose($diff_handle);
  }            
  @unlink($tempold); @unlink($tempnew);
  return $diff;
}

function Patch($page,$restore) {
  global $WikiDir,$SysPatchCmd;
  Lock(2);
  $txtfile = tempnam($WikiDir,"txt");
  $patfile = tempnam($WikiDir,"pat");
  if ($txtfp = fopen($txtfile,"w")) {
    fputs($txtfp,$page['text']);
    fclose($txtfp);
  }
  krsort($page); reset($page);
  foreach($page as $k=>$v) {
    if ($k < $restore) break;
    if (!preg_match('/^diff:/',$k)) continue;
    if ($patfp = fopen($patfile,"w")) {
      fputs($patfp,$v);
      fclose($patfp);
    }
    system("$SysPatchCmd $txtfile $patfile");
  }
  $text = implode('',file($txtfile));
  @unlink($txtfile); @unlink($patfile);
  return $text;
}

function PageExists($pagename) {
  global $WikiLibDirs,$PageFileFmt;
  $pagefile = FmtPageName($PageFileFmt,$pagename);
  if (!$pagefile) return false;
  foreach ($WikiLibDirs as $dir) {
    if (file_exists("$dir/$pagefile")) return true;
  }
  return false;
}
    
function Lock($op) {
  global $WikiDir;
  static $lockfp,$curop;
  if (!$lockfp) {
    $lockfp=fopen("$WikiDir/.flock","w") or 
      Abort("Cannot acquire lockfile","Lockfile");
  }
  if ($op<0) { flock($lockfp,LOCK_UN); fclose($lockfp); $lockfp=0; $curop=0; }
  elseif ($op==0) { flock($lockfp,LOCK_UN); $curop=0; }
  elseif ($op==1 && $curop<1) { flock($lockfp,LOCK_SH); $curop=1; }
  elseif ($op==2 && $curop<2) { flock($lockfp,LOCK_EX); $curop=2; }
}

function ReadPage($pagename,$defaulttext=NULL) {
  global $WikiLibDirs,$PageFileFmt,$DefaultPageTextFmt,$Now,$TimeFmt;
  $pagefile = FmtPageName($PageFileFmt,$pagename);
  if (!$pagefile) return false;
  if (is_null($defaulttext)) $defaulttext=$DefaultPageTextFmt;
  $page["text"] = FmtPageName($defaulttext,$pagename);
  $page["time"] = $Now;
  $newline = "\262";
  Lock(1);
  foreach ($WikiLibDirs as $dir) {
    $fp = @fopen("$dir/$pagefile","r");
    if ($fp) break;
  }
  if ($fp) {
    while (!feof($fp)) {
      $line = fgets($fp,4096);
      while (!strstr($line,"\n") && !feof($fp)) { $line .= fgets($fp,4096); }
      @list($k,$v) = explode("=",rtrim($line),2);
      if ($k=='newline') { $newline=$v; continue; }
      $page[$k] = str_replace($newline,"\n",$v);
    }
    fclose($fp);
  }
  $page['timefmt'] = strftime($TimeFmt,$page['time']);
  return $page;
}

function WritePage($pagename,$page) {
  global $Now,$HTTP_SERVER_VARS,$WikiDir,$PageFileFmt,$Version,$Newline;
  Lock(2);
  foreach (array('timefmt','pagename','action','version','newline') as $k)
    unset($page[$k]);
  $page['time'] = $Now; 
  $page['host'] = $HTTP_SERVER_VARS['REMOTE_ADDR'];
  $page['agent'] = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
  $page['rev'] = @$page['rev']+1;
  $pagefile = FmtPageName($PageFileFmt,$pagename);
  if ($pagefile && ($fp=fopen("$WikiDir/$pagefile","w"))) {
    fputs($fp,"version=$Version\n");
    fputs($fp,"newline=$Newline\n");
    foreach($page as $k=>$v) 
      { if ($k>"") fputs($fp,str_replace("\n",$Newline,"$k=$v")."\n"); }
    fclose($fp);
  } else Abort("Cannot write text to $pagename ($pagefile)"); 
}  

function QuoteAttrs($attr) {
  return preg_replace('/([a-zA-Z]=)([^\'"]\\S*)/',"\$1'\$2'",$attr);
}
  
function EmitCode($code,$depth,$attr="") {
  static $cs;
  static $li = array('dl'=>'</dd>','ul'=>'</li>','ol'=>'</li>');
  $attr = QuoteAttrs($attr);
  if (!$cs) { $cs = array(); }
  if (@$cs[0]=='table' && $code!=@$cs[0]) 
    while (count($cs)>0) { echo "</",array_pop($cs),">"; }
  if ($code=='p') { echo (@$cs[0]=='pre') ? "\n" : "<p />"; return; }
  if (@$cs[0]=='pre' && $code!=@$cs[0]) 
    while (count($cs)>0) { echo "</",array_pop($cs),">"; }
  while (count($cs)>$depth) 
    { $c=array_pop($cs); echo @$li[$c],"</$c>"; }
  if ($depth==0) return;
  if ($depth==count($cs)) {
    if ($cs[$depth-1]==$code) { echo @$li[$code]; return; }
    $c=array_pop($cs); echo @$li[$c],"</$c>";
  }
  while (count($cs)<$depth-1) { array_push($cs,'dl'); echo '<dl><dd>'; }
  while (count($cs)<$depth) { array_push($cs,$code); echo "<$code $attr>"; }
}

function FormatTableRow($x,$cellattr) {
  $x = preg_replace("/\\|\\|\$/","",$x);
  $td = explode('||',$x); $y='';
  for($i=0;$i<count($td);$i++) {
    if ($td[$i]=="") continue;
    if (preg_match("/^\\s+\$/",$td[$i])) $td[$i]="&nbsp;";
    $attr=" $cellattr";
    if (preg_match("/^\\s.*\\s\$/",$td[$i])) { $attr .= " align='center'"; }
    elseif (preg_match("/^\\s/",$td[$i])) { $attr .= " align='right'"; }
    for($colspan=1;$i+$colspan<count($td);$colspan++) 
      if ($td[$colspan+$i] != "") break;
    if ($colspan>1) { $attr .= " colspan='$colspan'"; }
    $y .= "<td$attr>".$td[$i]."</td>";
  }
  return "<tr>$y</tr>";
}

function EmitCell($x,$y) {
  static $tableattr,$intable;
  $y = QuoteAttrs($y);
  if ($x == 'cell' || $x == 'cellnr') {
    if (!$intable) { echo "<table $tableattr><tr><td $y>"; $intable=1; }
    else if ($x == 'cellnr') { echo "</td></tr><tr><td $y>"; }
    else { echo "</td><td $y>"; }
    return;
  } 
  if ($intable) { echo "</td></tr></table>"; $intable=0; }
  $tableattr = $y;
}

function ApplyStyles($x) {
  global $WikiStylePattern,$WikiStyle,$WikiStyleTags;
  $lineparts = preg_split("/($WikiStylePattern)/",$x,-1,
    PREG_SPLIT_DELIM_CAPTURE);
  $out='';
  $style = array();
  while ($lineparts) {
    $WikiStyle['curr']=$style; $style = array();
    if (preg_match("/^$WikiStylePattern\$/",$lineparts[0])) {
      $slist=preg_split('/[^-#=,\\w]+/',array_shift($lineparts),-1,
        PREG_SPLIT_NO_EMPTY);
      foreach($slist as $s) {
        if (preg_match('/^([^=]+)=(.*)$/',$s,$match)) {
          $style[$match[1]] = $match[2];
        } else { $style = array_merge($style,@$WikiStyle[$s]); }
      }
      if (@$style['define']) { 
        $d = $style['define']; unset($style['define']);
        $WikiStyle[$d] = $style;
      }
    }
    $l = array_shift($lineparts);
    if ($l>"") {
      $styleattr = '';
      foreach($style as $k=>$v) {
        if (!is_array($WikiStyleTags[$k])) continue;
        foreach($WikiStyleTags[$k] as $tag=>$w) {
          $w = str_replace('$value',$v,$w);
          if ($tag=='style') $styleattr.=$w;
          else $l=preg_replace("/(<$tag )/","\$1$w ",$l);
        }
      }
      if ($styleattr) { 
        $out .= preg_replace("/(<\\w+ )/","\$1style='$styleattr' ",
          "<span >$l</span>");
      }
      else $out .= $l;
    }
  }
  return $out;
}
  
function PrintText($pagename,$text="") {
  global $KeepToken,$LinkToken,$LinkPatterns,$ImgExtPattern,
    $DoubleBrackets,$TableAttr,$TableCellAttr,
    $InlineReplacements,$WikiStylePattern,$Text;
  global $FreeLinkPattern;
  static $refcount;
  if ($text=="") $text=$Text;
  ksort($LinkPatterns);
  foreach($LinkPatterns as $n=>$a)
    foreach($a as $p=>$r) $linkpats[$p]=$r;
  $kp=$KeepToken; $lp=$LinkToken;
  $text = htmlspecialchars($text,ENT_NOQUOTES);
  $kpcount=0;
  while (preg_match("/\\[\\=(.*?)\\=\\]/s",$text,$match)) {
    $text = preg_replace("/\\[\\=(.*?)\\=\\]/s","$kp$kpcount$kp",$text,1);
    $kpv[$kpcount] = $match[1]; $kpcount++;
  }
  $text = str_replace("\r","",$text);
  $text = str_replace("\\\n"," ",$text);
  foreach($DoubleBrackets as $n=>$fmt) {
    if ($n[0]!='/') $text=str_replace($n,FmtPageName($fmt,$pagename),$text);
  }
  $lines = explode("\n",$text);
  foreach($lines as $x) {
    foreach($DoubleBrackets as $n=>$fmt) {
      if ($n[0]=='/') $x=preg_replace($n,$fmt,$x);
    }
    $lpcount = 0;
    $lpv = array();
    foreach($linkpats as $pat=>$rep) {
      $re = "/\\[\\[($pat)(\\s.*?)?(\\]\\])/";
      while(preg_match($re,$x,$match)) {
        $x=preg_replace($re,"$lp$lpcount$lp",$x,1);
        array_pop($match); $txt=array_pop($match);
        if ($txt=="") $txt="$lp#$lp";
        else {
          $txt = ltrim($txt);
          if (preg_match("/$ImgExtPattern\$/",$txt)) {
            foreach($linkpats as $p=>$r) {
              if (preg_match("/^$p\$/",$txt)) {
                if (function_exists($r)) $txt = $r($p,$txt,NULL); 
                else $txt = preg_replace($p,$r,$txt);
                break;
              }
            }
          } 
        }
        if (function_exists($rep)) 
          $txt = $rep($pat,$match[1],$txt);
        else {
          $r = preg_replace("/^$pat\$/",$rep,$match[1]);
          preg_match("/(name|href)='.*?'/",$r,$match);
          $txt = "<a ".@$match[0].">$txt</a>";
        }
        $lpv[$lpcount++] = $txt;
      }
    }
    foreach($linkpats as $pat=>$rep) {
      $re = "/($pat)/";
      while(preg_match($re,$x,$match)) {
        $x=preg_replace($re,"$lp$lpcount$lp",$x,1);
        if (function_exists($rep)) 
          $txt = $rep($pat,$match[1],NULL);
        else $txt = preg_replace("/^$pat\$/",$rep,$match[1]);
        $lpv[$lpcount++] = $txt;
      }
    }
    if (preg_match("/^\\[\\[(table|cell|cellnr|tableend)(\\s.*?)?\\]\\]/",$x,$match)) {
      EmitCode("",0);
      @EmitCell($match[1],$match[2]);
      $x=preg_replace("/^\\[\\[.*?\\]\\]/","",$x);
    }
    else if (preg_match("/^\\s*\$/",$x)) { EmitCode("p",0); continue; }
    if (preg_match("/^(:+)[^:]*:/",$x,$match)) {
      $x=preg_replace("/^:+([^:]*):/","<dt>\\1</dt><dd>",$x);
      EmitCode("dl",strlen($match[1]));
    } elseif (preg_match("/^(\\*+)/",$x,$match)) {
      $x=preg_replace("/^\\*+/","<li>",$x);
      EmitCode("ul",strlen($match[1]));
    } elseif (preg_match("/^(#+)/",$x,$match)) {
      $x=preg_replace("/^#+/","<li>",$x);
      EmitCode("ol",strlen($match[1]));
    } elseif (preg_match("/^\s/",$x)) {
      EmitCode("pre",1);
    } elseif (preg_match("/^\\|\\|.*\\|\\|/",$x,$match)) {
      EmitCode("table",1,$TableAttr);
      $x=FormatTableRow($x,$TableCellAttr);
    } elseif (preg_match("/^\\|\\|(.*)/",$x,$match)) {
      EmitCode("",0);
      $TableAttr = $match[1];
      continue;
    } elseif (preg_match("/^(!+)/",$x,$match)) {
      $h="h".strlen($match[1]);
      $x=preg_replace("/^(!+)/","<$h>",$x)."</$h>";
      EmitCode("",0);
    } else EmitCode("",0);
    foreach ($InlineReplacements as $pat => $rep) 
      $x = preg_replace($pat,$rep,$x);
    if (preg_match("/^\\s*($lp(\\d+)$lp)\\s*\$/",$x,$match)) 
      $x = str_replace(" img>"," /><br />",$lpv[$match[2]]); 
    if (preg_match("/^\\s*($lp(\\d+)$lp).*\\S/",$x,$match)) 
      $x = str_replace($match[1],
        str_replace(" img>"," align='left' />",$lpv[$match[2]]), $x); 
    if (preg_match("/\\S.*($lp(\\d+)$lp)\\s*\$/",$x,$match)) {
      $rt = str_replace(" img>"," align='right' />",$lpv[$match[2]]);
      if (strstr($rt,"<img")) $x=$rt.str_replace($match[1],"",$x);
      else $x=str_replace($match[1],$rt,$x);
    } 
    $x = preg_replace("/$lp(\\d+)$lp/e",
      'str_replace(" img>"," />",$lpv[$1])',$x);
    $x = preg_replace("/$lp#$lp/e",'"[".++$refcount."]"',$x);
    if ($WikiStylePattern) $x = ApplyStyles($x);
    $x = preg_replace("/$kp(\\d+)$kp/e",'$kpv[$1]',$x);
    echo $x,"\n";
  }
  EmitCode("",0);
  EmitCell("","");
}

function PrintWikiPage($pagename,$wikilist=NULL) {
  global $PrintWikiPageNotFoundFmt;
  if (is_null($wikilist)) $wikilist=$pagename;
  $pagelist = preg_split('/\s+/',$wikilist,-1,PREG_SPLIT_NO_EMPTY);
  foreach($pagelist as $p) {
    if (PageExists($p)) {
      $page = RetrieveAuthPage($p,"read",false);
      PrintText($pagename,$page['text']);
      return;
    }
  }
  if ($PrintWikiPageNotFoundFmt>'') 
    print FmtPageName(@$PrintWikiPageNotFoundFmt,array_pop($pagelist));
}

function ProcessTextDirectives($pagename,$text="") {
  global $Text,$GroupNamePattern,$PageTitlePattern,$MaxIncludes,$SpaceWikiWords,
    $GroupHeaderFmt,$GroupFooterFmt,$BrowseDirectives;
  if (!$text) $text=$Text;
  $inclcount=0;
  while ($inclcount<$MaxIncludes &&
      preg_match("/\\[\\[include:(.*?)\\]\\]/",$text,$match)) {
    $inclrepl=$match[0]; $inclname=$match[1]; $incltext='';
    if (!preg_match("/^$GroupNamePattern([\\/.])$PageTitlePattern\$/",
        $inclname))
      $inclname = FmtPageName('$Group',$pagename).".$inclname";
    $inclpage = RetrieveAuthPage($inclname,"read",false);
    if ($inclpage) $incltext=$inclpage['text'];
    $text = str_replace($inclrepl,$incltext,$text);
    $inclcount++;
  }
  if (!strstr($text,"[[nogroupheader]]")) {
    $hdname = FmtPageName($GroupHeaderFmt,$pagename);
    if ($hdname != $pagename) 
      { $hdpage=ReadPage($hdname,""); $text = $hdpage['text'].$text; }
  }
  if (!strstr($text,"[[nogroupfooter]]")) {
    $hdname = FmtPageName($GroupFooterFmt,$pagename);
    if ($hdname != $pagename) 
      { $hdpage=ReadPage($hdname,""); $text = $text.$hdpage['text']; }
  }
  Lock(0);
  foreach($BrowseDirectives as $p=>$s) {
    if (strstr($text,$p)) $text = str_replace($p,eval($s),$text);
  }
  $Text = $text;
}

function HandleBrowse($pagename) {
  global $Text,$LastModified,$LastModifiedHost,
    $HTTP_GET_VARS,$PageRedirectFmt,$RedirectPattern,
    $HandleBrowseFmt,$HTMLTitle,$GCount;
  $page = RetrieveAuthPage($pagename,"read"); 
  if (!$page) { Abort("Invalid page name"); }
  $Text = $page['text'];
  $LastModified = $page['timefmt'];
  $LastModifiedHost = $page['host'];
  if (@!$HTTP_GET_VARS['from']) {
    $PageRedirectFmt = '';
    if (preg_match("/$RedirectPattern/",$Text,$match)) {
      $rpage = FmtWikiLink('',$match[1],NULL,'PageName',$pagename);
      if (PageExists($rpage)) Redirect($rpage,"\$PageUrl?from=$pagename");
    }
  }
  else $PageRedirectFmt = FmtPageName($PageRedirectFmt,$HTTP_GET_VARS['from']);
  ProcessTextDirectives($pagename);
  $HTMLTitle = $pagename; $GCount=0;
  PrintFmt($pagename,$HandleBrowseFmt);
}

function HandleEdit($pagename) {
  global $HandleEditFmt,$restore,$preview,$HTTP_POST_VARS,$HandleActions;
  global $Text,$HTMLTitle,$GCount;
  if (@$HTTP_POST_VARS['post']) 
    { $handle = $HandleActions['post']; return $handle($pagename); }
  $page = RetrieveAuthPage($pagename,"edit");
  if (!$page) { Abort("?cannot edit $pagename"); }
  if ($restore) { $text = Patch($page,$restore); }
  else if ($preview) { $text = stripmagic($HTTP_POST_VARS['text']); }
  else { $text = $page['text']; }
  $Text = $text;
  $HTMLTitle = "Edit $pagename"; $GCount = 0;
  PrintFmt($pagename,$HandleEditFmt);
}

function HandlePost($pagename) {
  global $WikiDir,$DeleteKeyWord,$RecentChanges,$HTTP_POST_VARS,$HTTP_SERVER_VARS,$Now,
    $TimeFmt,$PageFileFmt,$DiffKeepDays;
  foreach($HTTP_POST_VARS as $k=>$v)
    { $new[$k]=str_replace("\r","",stripmagic($HTTP_POST_VARS[$k])); }
  Lock(2);
  $page = RetrieveAuthPage($pagename,"edit");
  if (!$page) { Abort("?cannot post $pagename"); }
  $pagename = FmtPageName('$PageName',$pagename);
  if ($new['text']==$page['text']) 
    { Redirect($pagename); return; }
  if ($page["time"]>0) 
    { $new["diff:$Now:".$page['time']] = Diff($new["text"],$page["text"]); }
  foreach($new as $k=>$v) {
    if ($k=='pagename' || $k=='action') continue;
    $page[$k] = $v;
  }
  $keepgmt = $Now-$DiffKeepDays*86400;
  $keys = array_keys($page);
  foreach ($keys as $k) 
    if (preg_match("/^diff:(\\d+):/",$k,$match))
      if ($match[1] < $keepgmt) unset($page[$k]);
  $pagefile = FmtPageName($PageFileFmt,$pagename);
  if ($page['text']==$DeleteKeyWord) 
    { @rename("$WikiDir/$pagefile","$WikiDir/$pagefile,$Now"); }
  else WritePage($pagename,$page);
  foreach($RecentChanges as $rcfmt => $pgfmt) {
    $rcname=FmtPageName($rcfmt,$pagename); if (!$rcname) continue;
    $pgname=FmtPageName($pgfmt,$pagename); if (!$pgname) continue;
    if (@$seen[$rcname]++) continue;
    $rcpage = ReadPage($rcname,"");
    $rcpage['text'] = "* $pgname . . . . . . ".strftime($TimeFmt,$Now)." ''(".$HTTP_SERVER_VARS['REMOTE_ADDR'].")''"."\n".
      preg_replace("%\\* ".preg_quote($pgname)." .*?\\n%","",
        $rcpage['text']);
    WritePage($rcname,$rcpage);
  }
  Redirect($pagename); 
}

function HandleSource($pagename) {
  $page = RetrieveAuthPage($pagename,"read");
  if (!$page) Abort("?cannot source $pagename");
  Lock(0);
  header("Content-Type: text/plain");
  echo $page['text'];
}

function HandleDiff($pagename) {
  global $TimeFmt,$PageDiffFmt,$PageDiffFootFmt,$SysPatchCmd;
  $page = RetrieveAuthPage($pagename,"read");
  $pageedit = FmtPageName('$PageUrl?action=edit',$pagename);
  if (!$page) { Abort("?cannot diff $pagename"); }
  Lock(0);
  krsort($page); reset($page);
  StartHTML($pagename,"$pagename Revisions");
  PrintFmt($pagename,$PageDiffFmt);
  foreach($page as $k=>$v) {
    if (!preg_match("/^diff:(\d+):(\d+)/",$k,$match)) continue;
    $gmt=$match[1];
    echo "<p /><table width='100%' cellspacing='0' cellpadding='0' border='1'>\n";
    echo "<tr><td class='revdate'>", strftime($TimeFmt,$gmt),"</td></tr>
      <tr><td><table width='100%'>\n";
    $difflines = explode("\n",$v."\n");
    $in=array(); $out=array(); $dtype="";
    foreach ($difflines as $d) {
      if ($d>'') {
        if ($d[0]=='-' || $d[0]=='\\') continue;
        if ($d[0]=='<') { $out[]=substr($d,2); continue; }
        if ($d[0]=='>') { $in[]=substr($d,2); continue; }
      }
      if (preg_match("/^(\\d+)(,(\\d+))?a\\d/",$dtype,$match)) {
        $l = (isset($match[3])) ? "s ".$match[1]."-".$match[3] : " ".$match[1];
        echo "<tr><td colspan='2' class='revtype'>Deleted line$l:</td></tr>\n";
        echo "<tr><td width='4' class='revdel'>&nbsp;</td><td>";
        PrintText($pagename,join("\n",$in));
        echo "</td></tr>\n";
      } elseif (preg_match("/d(\\d+)(,(\\d+))?$/",$dtype,$match)) {
        $l = (isset($match[3])) ? "s ".$match[1]."-".$match[3] : " ".$match[1];
        echo "<tr><td colspan='2' class='revtype'>Added line$l:</td></tr>\n";
        echo "<tr><td width='4' class='revadd'>&nbsp;</td><td>";
        PrintText($pagename,join("\n",$out));
        echo "</td></tr>\n";
      } elseif (preg_match("/c(\\d+)(,(\\d+))?$/",$dtype,$match)) {
        $l = (isset($match[3])) ? "s ".$match[1]."-".$match[3] : " ".$match[1];
        echo "<tr><td colspan='2' class='revtype'>Changed line$l 
	  from:</td></tr>\n";
        echo "<tr><td width='4' class='revdel'>&nbsp;</td><td>";
        PrintText($pagename,join("\n",$in));
        echo "</td></tr>\n";
        echo "<tr><td colspan='2' class='revtype'>to:</td></tr>";
        echo "<tr><td width='4' class='revadd'>&nbsp;</td><td>";
        PrintText($pagename,join("\n",$out));
        echo "</td></tr>\n";
      }
      $in=array(); $out=array(); $dtype=$d;
    }
    echo "</table></td></tr></table>\n";
    if ($SysPatchCmd) 
      echo "<p><a href='$pageedit&amp;restore=$k'><span class='revdate'>Restore</span></a></p>\n";
  }
  PrintFmt($pagename,$PageDiffFootFmt);
  EndHTML();
}

function HandleCrypt() {
  global $HTTP_POST_VARS,$ScriptUrl;
  StartHTML("","Encrypt Password");
  $passwd = @$HTTP_POST_VARS["passwd"];
  if ($passwd) { echo "<p>Encrypted password = ",crypt($passwd),"</p>"; }
  echo "<form action='$ScriptUrl' method='POST'><p>
    Enter password to encrypt: <input type='text' name='passwd' value='' />
    <input type='submit' />
    <input type='hidden' name='action' value='crypt' /></p></form>";
  EndHTML();
}

function BasicAuth($pagename,$level,$authprompt=true) {
  global $HTTP_SERVER_VARS,$AuthRealmFmt,$AuthDeniedFmt,$DefaultPasswords,
    $AllowPassword,$GroupAttributesFmt;
  $page = ReadPage($pagename);
  if (!$page) { return false; }
  @$passwd = $page["passwd$level"];
  if ($passwd=="") { 
    $grouppg = ReadPage(FmtPageName($GroupAttributesFmt,$pagename));
    @$passwd = $grouppg["passwd$level"];
  }
  if ($passwd=="") { $passwd=@$DefaultPasswords[$level]; }
  if ($passwd=="") return $page;
  if (crypt($AllowPassword,$passwd)==$passwd) return $page;
  foreach (array_merge($DefaultPasswords['admin'],$passwd) as $pw) 
    if (@crypt($HTTP_SERVER_VARS['PHP_AUTH_PW'],$pw)==$pw) return $page;
  if (!$authprompt) return false;
  $realm=FmtPageName($AuthRealmFmt,$pagename);
  header("WWW-Authenticate: Basic realm=\"$realm\"");
  header("Status: 401 Unauthorized");
  header("HTTP-Status: 401 Unauthorized");
  PrintFmt($pagename,$AuthDeniedFmt);
  exit;
}

function RetrieveAuthPage($pagename,$level,$authprompt=true) {
  global $AuthFunction;
  if (!function_exists($AuthFunction)) 
    Abort("?Invalid AuthFunction specified: $AuthFunction","AuthFunction");
  return $AuthFunction($pagename,$level,$authprompt);
}

function HandleAttr($pagename) {
  global $ScriptUrl,$PageAttributes,$PageAttrFmt;
  $page = RetrieveAuthPage($pagename,"attr");
  if (!$page) { Abort("?unable to read $pagename"); }
  StartHTML($pagename,"Edit $pagename Attributes");
  echo FmtPageName($PageAttrFmt,$pagename);
  echo "<form action='$ScriptUrl' method='post'>
    <input type='hidden' name='action' value='postattr' />
    <input type='hidden' name='pagename' value='$pagename' />
    <table>";
  foreach($PageAttributes as $attr=>$prompt) {
    $value = (substr($attr,0,6)=='passwd') ? '' : $page[$k];
    echo "<tr><td>$prompt</td>
      <td><input type='text' name='$attr' value='$value' /></td></tr>";
  }
  echo "</table><input type='submit' /></form>";
  EndHTML();
}

function HandlePostAttr($pagename) {
  global $HTTP_POST_VARS,$PageAttributes;
  $page = RetrieveAuthPage($pagename,"attr");
  if (!$page) { Abort("?cannot get $pagename"); }
  foreach($PageAttributes as $k=>$v) {
    $newpw = $HTTP_POST_VARS[$k];
    if ($newpw=="clear") unset($page[$k]);
    else if ($newpw>"") $page[$k]=crypt($newpw);
  }
  WritePage($pagename,$page);
  Redirect($pagename);
  exit;
}

?>
