<?php
// URL
$ScriptUrl = "wiki";

// Layout
$WikiTitle = "RPGDX Wiki";
$BodyWidth = "100%";
$HTMLDoctypeFmt = array(
  "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html40/loose.dtd\">\n",
  "<html>\n",
	"<head>\n");
$PageHeaderFmt = '
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="0" valign="top" colspan="1">
      <a href="http://www.rpgdx.net/"><img src="http://localhost/rpgdx/forums/templates/modern/images/rpgdx_logo.png" style="margin: 0px 0px 0px 4px;" alt="RPGDX" title="" border="0" /></a><img src="http://localhost/rpgdx/templates/modern/images/rpgdx_logotext.png" alt="The center of Indie-RPG gaming" title="" style="margin: 0px 0px 3px 8px;" />
    </td>
  </tr>
  <tr>
    <td>
      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td class="user_bar" align="right">
            <table class="user_links" cellspacing="0" cellpadding="0">
              <tr>
                <td class="user_link"><a href="$ScriptUrl/$[Main/SearchWiki]">$[SearchWiki]</a></td>
                <td class="user_link"><a href="$ScriptUrl/$[$Group/RecentChanges]">$Group.$[RecentChanges]</a></td>
                <td class="user_link"><a href="$PageUrl?action=edit">$[Edit Page]</a></td>
                <td class="user_link"><a href="$PageUrl?action=diff">$[Page Revisions]</a></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td style="font-size: 0px; background-image: url(http://localhost/rpgdx/templates/modern/images/page_top_gradient.png); background-repeat: repeat-x; height: 6px;">&nbsp;</td></tr>

  <tr>
    <td width="100%" valign="top" align="left" style="padding: 10px; padding-top: 0px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td colspan="3" height="9" style="font-size: 0px;">&nbsp;</td>
        </tr>
        <tr>
          <td>
            <div class="thin_shadow">
            <div class="lift_by_one">
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td width="100%" class="body_header">
                  <a href="$PageUrl">$Title</a>
                </td>
              </tr>
              <tr>
                <td class="navigation" colspan="3">
                  <a href="http://www.rpgdx.net/">RPGDX</a> &gt;&gt; <a href="http://wiki.rpgdx.net/">Wiki</a> &gt;&gt; <a href="$ScriptUrl/$Group">$Group</a> &gt;&gt; <a href="$PageUrl">$Title</a>
                </td>
              </tr>
            </table>
            </div>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="body_main">';

	
	/*
	<table width='\$BodyWidth' cellpadding='0' cellspacing='0' border='0'>
  <tr><td valign='bottom' align='left' width='10%'>\$WikiImg&nbsp;&nbsp;</td>
    <td valign='bottom' align='left'><a href='\$ScriptUrl/\$Group'>\$Group</a> /<br />
      <span class='wikiheader'><a href='\$PageUrl?action=search&amp;text=\$Title_'>\$Title</a></span></td>
    <td valign='bottom' align='right' class='wikiops'>
      <a href='\$ScriptUrl/$[Main/SearchWiki]'>$[SearchWiki]</a><br />
      <a href='\$ScriptUrl/$[\$Group/RecentChanges]'>\$Group.$[RecentChanges]</a><br />
      <a href='\$PageUrl?action=edit'>$[Edit Page]</a><br />
      <a href='\$PageUrl?action=diff'>$[Page Revisions]</a></td>
   </tr></table><hr /><p />";*/
$HTMLTitleFmt = "<title>\$WikiTitle - \$HTMLTitle</title>\n";
$HTMLHeaderFmt = array("<style type='text/css'>\n", 'file:local/stylesheet.css', "</style>");

// Uploading files
$EnableUpload = 1;
$EnableUploadOverwrite = 0;
$DefaultPasswords['upload'] = '';
$DefaultPasswords['admin'] = '$1$Dv5.Q/2.$fAyqOViijGrIWqiIIC9nN1';
$UploadUrlFmt = 'http://www.rpgdx.net/pmwiki/uploads';
?>
