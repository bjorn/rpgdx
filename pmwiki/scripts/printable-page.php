<?php

/*
	Copyright 2003
	John Rankin
	john.rankin@affinity.co.nz

	produces a printable version of a PmWiki page
*/

$PageFooterFmt = str_replace("<br />",
    " - <a href='\$PageUrl?action=print' target='_blank'>Printable Version</a><br />",
    $PageFooterFmt);
if ($action == "print") {
    $HTMLHeaderFmt .= "<style type = 'text/css'> 
        body { background-color: #ffffff; font-family: Georgia, Times Roman, serif; color: #000000; }
        a:link  { color: #444444; font-weight: bold; text-decoration: none; }
        a:visited  { color: #444444; font-weight: bold; text-decoration: none; }
    </style>";
    $PageHeaderFmt = "<big>From $WikiTitle</big>
        <h1>\$Groupspaced: \$Titlespaced</h1><hr size='3' noshade='on' /><p />";
    $PageFooterFmt = "<p /><hr size='3' noshade='on' /><small>Retrieved from &ldquo;$PageUrlFmt&rdquo;<br />
        Page last modified on \$LastModified</small>";
    $GroupHeaderFmt = '$Group.GroupPrintHeader';
    $GroupFooterFmt = '$Group.GroupPrintFooter';
    $DoubleBrackets["/\\[\\[mailto:($UrlPathPattern)(.*?)\\]\\]/"] =
        "''$2'' [[[mailto:$1 $1]]]";
    $WikiPageExistsFmt = 
       "<a class='wikiword' href='\$PageUrl?action=print'>\$LinkText</a>";
    $WikiPageCreateFmt = 
       "\$LinkText<a class='nonexistent' href='\$PageUrl?action=print'>?</a>";
    $WikiPageCreateSpaceFmt =
       "(\$LinkText)<a class='nonexistent' href='\$PageUrl?action=print'>?</a>";
    $UrlLinkTextFmt = 
       "<cite>\$LinkText</cite> [<a class='url' href='\$Url'>\$Url</a>]";
    $hide = 1;
}

?>
