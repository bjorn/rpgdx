<?php
// URL
//$ScriptUrl = "wiki";

// Enable sending of mail notification
$EnableMailPosts=1;
$MailPostsTo="bjorn@rpgdx.net";
$MailPostsDelay=1800;                  # wait for initial post to age 30 minutes
$MailPostsSquelch=7200;               # require at least two hours between mails


// Layout
$WikiTitle = "RPGDX Wiki";
$WikiImgUrl = "http://www.rpgdx.net/images/wiki_logo.png";
$HTMLDoctypeFmt = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html40/loose.dtd">
<html>
<head>
';
$HTMLHeaderFmt = '
<style type="text/css">
BODY { background-color: rgb(255,255,255); }
BODY, TD, TH {
  font-family: Verdana, Sans-Serif;
  font-size: 13px;
}
HR { text-align: left; background-color: rgb(240,240,240); height: 5px; border: 0px; border: 1px dotted rgb(0,0,0); }
.wikititle { font-size: 32px; font-weight: bold; }
.wikiops {  }
.wikiheader { background-color: rgb(240,240,240); border: 1px dotted rgb(0,0,0); margin-bottom: 10px; }
.wikifooter { background-color: rgb(240,240,240); border: 1px dotted rgb(0,0,0); margin-top: 10px; padding: 5px; font-size: 10px; }
.wikibody { }
.revdate { font-size: 11px; }
.revtype { font-size: 11px; font-weight: bold; }
.revadd { background: #99ff99; color: black; }
.revdel { background: #ffff99; color: black; }
</style>
';
$HTMLBodyFmt = '</head>
<body bgcolor="#ffffff">
<div class="wikibody">
';
$PageHeaderFmt = '<div class="wikiheader">
<table width="100%" cellpadding="5" cellspacing="0" border="0">
  <tr>
    <td valign="middle" align="left" width="0">$WikiImg</td>
    <td valign="middle" align="center" width="100%">
      <a href="http://www.rpgdx.net/">RPGDX</a> &gt; <a href="http://wiki.rpgdx.net/Main/HomePage">Wiki</a> &gt; <a href="$ScriptUrl/$Group">$Group</a><br />
      <span class="wikititle"><a href="$PageUrl?action=search&amp;text=$Title_">$Title</a></span>
    </td>
    <td valign="middle" align="right" class="wikiops" style="white-space: nowrap;" width="0">
      <a href="$ScriptUrl/$[Main/SearchWiki]">$[SearchWiki]</a><br />
      <a href="$ScriptUrl/$[$Group/RecentChanges]">$Group.$[RecentChanges]</a><br />
      <a href="$PageUrl?action=edit">$[Edit Page]</a><br />
      <a href="$PageUrl?action=diff">$[Page Revisions]</a>
    </td>
  </tr>
</table>
</div>
';
$PageFooterFmt = '
<div class="wikifooter">
<a href="$PageUrl?action=edit">$[Edit Page]</a> - 
<a href="$PageUrl?action=diff">$[Page Revisions]</a> -
<a href="$ScriptUrl/$[PmWiki/WikiHelp]">$[WikiHelp]</a> -
<a href="$ScriptUrl/$[Main/SearchWiki]">$[SearchWiki]</a> -
<a href="$ScriptUrl/$[Main/AllRecentChanges]">$[AllRecentChanges]</a><br />
$[Page last modified on $LastModified (by $LastModifiedHost])
</div>
';
$HTMLEndFmt = '</div>
</body>
</html>
';

// Uploading files
$EnableUpload = 1;
$EnableUploadOverwrite = 0;
$DefaultPasswords['upload'] = '';
$DefaultPasswords['admin'] = '$1$Dv5.Q/2.$fAyqOViijGrIWqiIIC9nN1';
$UploadUrlFmt = 'http://www.rpgdx.net/pmwiki/uploads';


// Enable reference count action (refcount)
include_once("scripts/refcount.php");

// Check the permissions on Wiki files (and enable fixallperms action)
include_once("scripts/fixperms.php");

// Enable the printable page action (print)
include_once("scripts/printable-page.php");

?>
