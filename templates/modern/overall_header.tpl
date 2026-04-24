<!DOCTYPE html>
<html>

<head>
<title>{TITLE}
<!-- BEGIN subtitle -->
&gt; {subtitle.TITLE}
<!-- END subtitle -->
</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="Keywords" content="rpg, rpgs, rpgdx, indie, indierpg, mandrake, community, game, games, free, independant, programmers, community, graphics, sound, music, development, projects, resources, graphics, sound, music, qbasic, qb, quickbasic, c, c++">
<meta name="Description" content="A community site for independent RPG programmers, allowing project pages, discussion on forums and resource sharing.">
<script defer data-domain="rpgdx.net" src="https://plausible.io/js/script.js"></script>
<link rel="stylesheet" href="{TEMPL_DIR}stylesheet.css?v={STYLE_VERSION}" type="text/css">
<link rel="icon" href="data:,">
</head>

<body>

<header class="site-header-row">
  <div class="site-header-logo">
    <a href="http://rpgdx.net/"><img src="{TEMPL_DIR}images/rpgdx_logo.png" alt="RPGDX" title="" /></a>
  </div>
  <div class="site-header-banner">
    <img src="{TEMPL_DIR}images/rpgdx_logotext.png" alt="The center of Indie-RPG gaming" title="" />
  </div>
</header>

<div class="user_bar">
  <span>{LOGINBAR_TEXT}</span>
  <!-- BEGIN userlinks -->
  <div class="user_links">
    <!-- BEGIN userlink -->
    <div class="user_link">
      <a href="{userlinks.userlink.URL}">{userlinks.userlink.TEXT}</a>
    </div>
    <!-- END userlink -->
  </div>
  <!-- END userlinks -->
</div>

<div class="site-gradient-row">
  <div class="site-gradient-sidebar"></div>
  <div class="site-gradient-main"></div>
</div>

<div class="site-body-row">
  <aside class="site-sidebar">
    <div class="menu_background">
      <div class="menu">
        <div class="menu_title" style="border-top: 1px solid rgb(64,64,100);">Main</div>
        <div class="menu_item"><a href="{U_NEWS}">news page</a></div>
        <div class="menu_item"><a href="{U_FORUMS}">forums</a></div>
        <div class="menu_item"><a href="{U_WIKI}">wiki</a></div>
        <div class="menu_item"><a href="{U_CONTESTS}">contests</a></div>
        <div class="menu_item"><a href="{U_SEARCH}">search projects</a></div>
        <div class="menu_item"><a href="{U_ABOUT}">about rpgdx</a></div>

        <div class="menu_title">Projects</div>
        <!-- BEGIN rpgtype -->
        <div class="menu_item menu-item-row">
          <a href="{rpgtype.URL}">{rpgtype.NAME}</a>
          <span>{rpgtype.COUNT}</span>
        </div>
        <!-- END rpgtype -->

        <div class="menu_title">Articles</div>
        <!-- BEGIN artcat -->
        <div class="menu_item menu-item-row">
          <a href="{artcat.URL}">{artcat.NAME}</a>
          <span>{artcat.COUNT}</span>
        </div>
        <!-- END arttype -->
      </div>
    </div>
    <div class="site-sidebar-promos">
      <br />
      <br />
      <b>Vote for us</b><br />
      <a href="http://www.rpg-dev.net/top50/vote.aspx?user=Bjorn"><img src="images/rpgdev_top50.gif" alt="" width="88" height="31" style="margin-top: 10px;"></a><br />
      <br />
      <b>Affiliates</b><br />
      <a href="http://www.rpg-dev.net/"><img src="images/rpgdev_banner.gif" alt="" width="88" height="31" style="margin-top: 10px;"></a><br />
      <a href="http://www.abstract-productions.net/"><img src="images/ap_banner.png" alt="" width="88" height="31" style="margin-top: 10px;"></a>
    </div>
  </aside>
  <main class="site-main">
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
    <div class="seperation">&nbsp;</div>
    <div class="body_main">
