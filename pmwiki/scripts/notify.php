<?php

##
## This script adds watched pages markup.
## Any markup '[[notify:WikiWord]]' gets converted into
## a list of pages that are appended to the $RecentChanges array.
## This allows authors to keep a personal RecentChanges page.
##
## The markup variant '[[notify:{{free link}}]]' is also supported.
##
## Lines of the form '=notify WikiWord {{free link}} ...' work too.
##
## Copyright 2003 John Rankin (john.rankin@affinity.co.nz)
##

$NotifyGroup = "Topics";
$WatchingFor = '$Group.$Tlink';
$WatcherPattern = "($WikiWordPattern)|($FreeLinkPattern)";

## single notifier
if ($action=='post' && 
  preg_match_all("/\\[\\[notify:($WatcherPattern)\\]\\]/",$text,$notifies)) {
    foreach($notifies[1] as $n) {
        $i = "$NotifyGroup." . Wikify($n);
        $RecentChanges[$i] = "$WatchingFor";
    }
}
$DoubleBrackets["/\\[\\[notify:($WatcherPattern)\\]\\]/e"] = 'Notify("$1");';

## notifier list
if ($action=='post' && 
  preg_match_all("/\n=notify((\\s+($WatcherPattern))+)/",$text,$match)) {
    $notifies = preg_split('/\\s+/',WikifyList(ltrim(join(' ',$match[1]))));
    foreach($notifies as $n) {
        $i = "$NotifyGroup.$n";
        $RecentChanges[$i] = "$WatchingFor";
    }
}
$DoubleBrackets["/^=notify((\s+($WatcherPattern))+)/e"] 
  = 'NotifyList("$1");';

function NotifyList($words) {
  global $NotifyGroup,$WatcherPattern;
  $watchers = "[-<b>$NotifyGroup/$NotifyGroup:</b> ";
  $watchlist = 
        preg_replace("/($WatcherPattern)/e",'Notify("$1")',ltrim($words));
  $watchers .= preg_replace("/&gt;\s+/", "&gt;, ", $watchlist) . ".-]";
  return $watchers;
}

function Notify($word) {
  global $NotifyGroup;
  return "&lt;|$NotifyGroup/$word|&gt;";
}

function WikifyList($words) {
  global $FreeLinkPattern;
  return preg_replace("/($FreeLinkPattern)/e",'Wikify("$1")',$words);
}

function Wikify($word) {
  global $PageNameSpace;
  $linktext = preg_replace("/{{(.*)}}.*/","$1",str_replace("|","",$word));
  return preg_replace("/\s+/",$PageNameSpace,ucwords($linktext));
}

?>