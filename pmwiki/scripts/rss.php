<?php
/*  Copyright 2002-2003 Patrick R. Michaud (pmichaud@pobox.com)
    This file is part of PmWiki; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.  See pmwiki.php for full details.

    This script defines "?action=rss".  It will read the current page
    for a WikiTrail, and then output an RSS 2.0 document with the current
    page as the channel and the pages in the WikiTrail as the items.

    To add RSS capabilities to your site, simply add to local.php:
        include_once("scripts/rss.php");
*/

SDV($HandleActions['rss'],'HandleRss');
SDV($HandleActions['rdf'],'HandleRss');

SDV($RssMaxItems,20);				# maximum items to display
SDV($RssSourceSize,256);			# max size to build desc from
SDV($RssDescSize,80);				# max desc size
SDV($RssItems,array());				# RSS item elements
SDV($RssItemsRDFList,array());			# RDF <items> elements

if ($action=='rdf') {
  ### RSS 1.0 (RDF) definitions
  SDV($RssTimeFmt,'%Y-%m-%dT%H:%M+00:00');	# time format
  SDV($RssItemsRDFListFmt,"<rdf:li rdf:resource=\"\$PageUrl\" />\n");
  SDV($RssChannelFmt,array('<?xml version="1.0"?'.'>
    <rdf:RDF  xmlns="http://purl.org/rss/1.0/"
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns:dc="http://purl.org/dc/elements/1.1/">
      <channel rdf:about="$PageUrl">
        <title>$WikiTitle - $Group.$Title</title>
        <link>$PageUrl</link>
        <description>$RssChannelDesc</description>
        <dc:date>$RssChannelBuildDate</dc:date>
        <items>
          <rdf:Seq>',&$RssItemsRDFList,'
          </rdf:Seq>
        </items>
      </channel>'));
  SDV($RssItemFmt,'
      <item rdf:about="$PageUrl">
        <title>$WikiTitle - $Group.$Title</title>
        <link>$PageUrl</link>
        <description>$RssItemDesc</description>
        <dc:date>$RssItemPubDate</dc:date>
      </item>');
  SDV($HandleRssFmt,array(&$RssChannelFmt,&$RssItems,'</rdf:RDF>'));
}

### RSS 2.0 definitions
SDV($RssTimeFmt,'%a, %d %b %Y %H:%M:%S GMT');
SDV($RssChannelFmt,'<?xml version="1.0"?'.'>
  <rss version="2.0">
    <channel>
      <title>$WikiTitle - $Group.$Title</title>
      <link>$PageUrl</link>
      <description>$RssChannelDesc</description>
      <lastBuildDate>$RssChannelBuildDate</lastBuildDate>
      <generator>$Version</generator>');
SDV($RssItemFmt,'
        <item>
          <title>$PageName</title>
          <link>$PageUrl</link>
          <description>$RssItemDesc</description>
          <pubDate>$RssItemPubDate</pubDate>
        </item>');
SDV($HandleRssFmt,array(&$RssChannelFmt,&$RssItems,'</channel></rss>'));


function HandleRss($pagename) {
  global $RssMaxItems,$RssSourceSize,$RssDescSize,
    $RssChannelFmt,$RssChannelDesc,$RssTimeFmt,$RssChannelBuildDate,
    $RssItemsRDFList,$RssItemsRDFListFmt,$RssItems,$RssItemFmt,$RssItemDesc,
    $RssItemPubDate,$GCount,$HandleRssFmt;
  $t = ReadTrail($pagename);
  $page = RetrieveAuthPage($pagename,false);
  $cbgmt = $page['time'];
  $r = array();
  for($i=0;$i<count($t) && count($r)<$RssMaxItems;$i++) {
    if (!PageExists($t[$i]['name'])) continue;
    $page = RetrieveAuthPage($t[$i]['name'],false);
    ob_start(); 
    PrintText($t[$i]['name'],substr($page['text'],0,$RssSourceSize));
    $text = ob_get_contents(); ob_end_clean();
    $text = preg_replace("/<.*?>/s","",$text); 
    preg_match("/^(.{0,$RssDescSize}\\s)/s",$text,$match);
    $r[] = array('name' => $t[$i]['name'],'time' => $page['time'],
       'desc' => $match[1]." ...");
    if ($page['time']>$cbgmt) $cbgmt=$page['time'];
  }
  SDV($RssChannelBuildDate,gmstrftime($RssTimeFmt,$cbgmt));
  SDV($RssChannelDesc,FmtPageName('$Group.$Title',$pagename));
  foreach($r as $page) {
    $RssItemPubDate = gmstrftime($RssTimeFmt,$page['time']);
    $RssItemDesc = $page['desc'];
    $GCount = 0;
    $RssItemsRDFList[] = FmtPageName($RssItemsRDFListFmt,$page['name']);
    $RssItems[] = FmtPageName($RssItemFmt,$page['name']);
  }
  header("Content-type: text/xml");
  PrintFmt($pagename,$HandleRssFmt);
  exit();
}
