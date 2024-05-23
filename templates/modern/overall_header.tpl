<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>

<head>
<title>{TITLE}
<!-- BEGIN subtitle -->
&gt; {subtitle.TITLE}
<!-- END subtitle -->
</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<meta name="Keywords" content="rpg, rpgs, rpgdx, indie, indierpg, mandrake, community, game, games, free, independant, programmers, community, graphics, sound, music, development, projects, resources, graphics, sound, music, qbasic, qb, quickbasic, c, c++">
<meta name="Description" content="A community site for independent RPG programmers, allowing project pages, discussion on forums and resource sharing.">
<script defer data-domain="rpgdx.net" src="https://plausible.io/js/script.js"></script>
<link rel="stylesheet" href="{TEMPL_DIR}stylesheet.css" type="text/css">
</head>

<body>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td width="0" valign="top" colspan="1" class="menu_background" style="text-align: center;">
      <a href="http://rpgdx.net/"><img src="{TEMPL_DIR}images/rpgdx_logo.png" style="margin: 0px;" alt="RPGDX" title="" border="0" /></a>
    </td>
    <td width="100%" valign="bottom" colspan="3">
      <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td valign="bottom" style="padding: 3px;"><img src="{TEMPL_DIR}images/rpgdx_logotext.png" alt="The center of Indie-RPG gaming" title="" /></td>
          <td style="text-align: right;">
          <!-- Optional image here -->
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="4">
      <table cellpadding="0" cellspacing="0" width="100%" border="0">
        <tr>
          <td width="0%" class="user_bar">{LOGINBAR_TEXT}</td>
          <!-- BEGIN userlinks -->
          <td width="100%" class="user_bar" align="right">
            <table class="user_links" cellspacing="0" cellpadding="0">
              <tr>
                <!-- BEGIN userlink -->
                <td class="user_link">
                  <a href="{userlinks.userlink.URL}">{userlinks.userlink.TEXT}</a>
                </td>
                <!-- END userlink -->
              </tr>
            </table>
          </td>
          <!-- END userlinks -->
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td style="font-size: 0px; background-color: rgb(128,128,150); background-image: url({TEMPL_DIR}images/menu_top_gradient.png); background-repeat: repeat-x; height: 6px;">&nbsp;</td>
    <td colspan="3" style="font-size: 0px; background-image: url({TEMPL_DIR}images/page_top_gradient.png); background-repeat: repeat-x; height: 6px;">&nbsp;</td>
    <td style="font-size: 0px;">&nbsp;</td>
  </tr>
  <tr>
    <td width="0" valign="top">
      <div class="menu_background">
        <div class="menu">
          <div class="menu_title" style="border-top: 1px solid rgb(64,64,100);">Main</div>
          <div class="menu_item"><a href="{U_NEWS}">news page</a></div>
          <div class="menu_item"><a href="{U_FORUMS}">forums</a></div>
          <div class="menu_item"><a href="{U_WIKI}">wiki</a></div>
          <div class="menu_item"><a href="{U_CONTESTS}">contests</a></div>
          <div class="menu_item"><a href="{U_SEARCH}">search projects</a></div>
          <div class="menu_item"><a href="{U_ABOUT}">about rpgdx</a></div>

          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr><td class="menu_title">Projects</td></tr>
            <!-- BEGIN rpgtype -->
            <tr><td class="menu_item">
              <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%"><tr>
                <td><a href="{rpgtype.URL}">{rpgtype.NAME}</a></td>
                <td align="right">{rpgtype.COUNT}</td>
              </tr></table>
            </td></tr>
            <!-- END rpgtype -->

            <tr><td class="menu_title">Articles</td></tr>
            <!-- BEGIN artcat -->
            <tr><td class="menu_item">
              <table cellpadding="0" cellspacing="0" border="0" align="center" width="100%"><tr>
                <td><a href="{artcat.URL}">{artcat.NAME}</a></td>
                <td align="right">{artcat.COUNT}</td>
              </tr></table>
            </td></tr>
            <!-- END arttype -->
          </table>
        </div>
        <div style="text-align: right;"><img src="{TEMPL_DIR}images/menu_bottom_corner.png" alt="" /></div>
      </div>
      <div align="center">
      <br />
      <br />
      <b>Vote for us</b><br />
      <a href="http://www.rpg-dev.net/top50/vote.aspx?user=Bjorn"><img src="images/rpgdev_top50.gif" alt="" width="88" height="31" border=0 style="margin-top: 10px;"></a><br />
      <br />
      <b>Affiliates</b><br />
      <a href="http://www.rpg-dev.net/"><img src="images/rpgdev_banner.gif" alt="" width="88" height="31" border=0 style="margin-top: 10px;"></a><br />
      <a href="http://www.abstract-productions.net/"><img src="images/ap_banner.png" alt="" width="88" height="31" border=0 style="margin-top: 10px;"></a><!--<br />
      <b>Hosted:</b><br />-->

      </div>
    </td>
    <td><img src="{TEMPL_DIR}images/pixel.gif" width="10" height="1" alt="" /></td>
    <td width="100%" valign="top" align="left">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td colspan="3" height="9"><img src="{TEMPL_DIR}images/pixel.gif" height="9" width="1" alt="" /></td>
        </tr>
        <tr>
          <td>
            <div class="thin_shadow">
              <div class="lift_by_one">
                <div class="body_header">{PAGE_TITLE}</div>
                <div class="navigation">
                  <a href="{U_NEWS}">RPGDX</a>
                  <!-- BEGIN subtitle -->
                  &gt;&gt;
                  <!-- BEGIN url -->
                  <a href="{subtitle.url.URL}">
                  <!-- END url -->
                  {subtitle.TITLE}
                  <!-- BEGIN url -->
                  </a>
                  <!-- END url -->
                  <!-- END subtitle -->
                </div>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="seperation" colspan="3">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3" class="body_main">
