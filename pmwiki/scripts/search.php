<?php
/*  Copyright 2002-2003 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script provides search capabilities for PmWiki.  It is included
    by default from the stdconfig.php script unless disabled by
    $EnableSearch=0; in local.php.
*/

XLSDV('en',array(
  'SearchFor' => 'Searching for <em>$Needle</em>:',
  'SearchFound' => 
    '$MatchCount pages found out of $MatchSearched pages searched.'
));

SDV($HandleActions['search'],'HandleSearch');
if (isset($EnablePathInfo) && !$EnablePathInfo) 
  SDV($SearchTagFmt,"<form action='\$ScriptUrl' method='get'><input 
    type='hidden' name='pagename' value='\$PageName'><input type='hidden' 
    name='action' value='search' /><input type='text' name='text' value='' 
    size='40' /><input type='submit' value='$[Search]' /></form>");
SDV($SearchTagFmt,"<form action='\$PageUrl' method='get'><input 
  type='hidden' name='action' value='search' /><input type='text' 
  name='text' value='' size='40' /><input type='submit' 
  value='$[Search]' /></form>");
SDV($PageSearchFmt,"<h1>\$WikiImg $[Search Results]</h1>
  $[SearchFor]
  <p /><dl>\$MatchList</dl>
  <p />$[SearchFound]
  <hr /><small>
  <a href='\$ScriptUrl/$[PmWiki/WikiHelp]'>$[WikiHelp]</a> -
  <a href='\$ScriptUrl/$[Main/SearchWiki]'>$[SearchWiki]</a><br />
  $[Search performed on \$LastModified]</small>");
SDV($HandleSearchFmt,array(&$HTMLStartFmt,&$PageSearchFmt,&$HTMLEndFmt));
SDV($SearchListItemFmt,'<dd><a href="$PageUrl">$Title</a></dd>');
SDV($SearchListGroupFmt,'<dt><a href="$ScriptUrl/$Group">$Group /</a></dt>');
SDV($InlineReplacements['/\\[\\[\\$Search\\]\\]/e'],
  "FmtPageName(\$GLOBALS['SearchTagFmt'],\$pagename)");

function HandleSearch($pagename) {
  global $WikiLibDirs,$SearchListItemFmt,$SearchListGroupFmt,$HandleSearchFmt,
    $FreeLinkPattern, $PageNameSpace,$HTTP_POST_VARS,$HTTP_GET_VARS,
    $GroupNamePattern;
  $matches = array(); $matchsearched = 0;
  foreach(array('text','group') as $v) {
    $$v = '';
    if (isset($HTTP_POST_VARS[$v])) $$v=stripmagic($HTTP_POST_VARS[$v]);
    if (isset($HTTP_GET_VARS[$v])) $$v=stripmagic($HTTP_GET_VARS[$v]);
  }
  $needle = $text;
  $terms = $text;
  if (preg_match("!^($GroupNamePattern)/!i",$terms,$match)) {
    $group = $match[1]; 
    $terms = preg_replace("!^($GroupNamePattern)/!i","",$terms);
  }
  $terms = preg_split('/((?<!\\S)[-+]?[\'"].*?[\'"](?!\\S)|\\S+)/',$terms,-1,
    PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
  $excl = array(); $incl = array();
  foreach($terms as $t) {
    if (trim($t)=="") continue;
    preg_match('/^([-+]?)([\'"]?)(.+?)\\2$/',$t,$match);
    if ($match[1]=='-') $excl[] = $match[3];
    else $incl[] = $match[3];
  }
  foreach($WikiLibDirs as $dir) {
    $dfp = opendir($dir); if (!$dfp) continue;
    while (($pagefile=readdir($dfp))!=false) {
      if (@$seen[$pagefile]++) continue;
      if ($group && strcasecmp(FmtPageName('$Group',$pagefile),$group)!=0) 
        continue;
      $page = ReadPage($pagefile);  if (!$page) continue;
      $matchsearched++;
      $text = $pagefile."\n".preg_replace("/$FreeLinkPattern/e","'$0'.preg_replace('/\\s+/','$PageNameSpace',ucwords('$1'.'$2')).' '.'$1'.'$3'",$page['text']);
      foreach($excl as $t) if (stristr($text,$t)) continue 2;
      foreach($incl as $t) if (!stristr($text,$t)) continue 2;
      $matches[] = $pagefile; 
    }
    closedir($dfp);
  }
  sort($matches); reset($matches);
  $MatchList = array();
  foreach($matches as $pagefile) { 
    $group = FmtPageName($SearchListGroupFmt,$pagefile);
    if ($group!=@$lgroup) {
      $MatchList[] = $group;
      $lgroup = $group;
    }
    $MatchList[] = FmtPageName($SearchListItemFmt,$pagefile); 
  }
  $GLOBALS['HTMLTitle'] = 'Search Results';
  $GLOBALS['MatchList'] = join('',$MatchList);
  $GLOBALS['MatchCount'] = count($matches);
  $GLOBALS['MatchSearched'] = $matchsearched;
  $GLOBALS['Needle'] = htmlspecialchars($needle);
  PrintFmt($pagename,$HandleSearchFmt);
}

?>
