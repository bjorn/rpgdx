<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="ISO-8859-1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{TITLE}
<!-- BEGIN subtitle -->
&gt; {subtitle.TITLE}
<!-- END subtitle -->
</title>
<meta name="Keywords" content="rpg, rpgs, rpgdx, indie, indierpg, mandrake, community, game, games, free, independent, programmers, community, graphics, sound, music, development, projects, resources, qbasic, qb, quickbasic, c, c++">
<meta name="Description" content="A community site for independent RPG programmers, allowing project pages, discussion on forums and resource sharing.">
<meta name="theme-color" content="rgb(33,33,57)">
<script defer data-domain="rpgdx.net" src="https://plausible.io/js/script.js"></script>
<link rel="stylesheet" href="{TEMPL_DIR}stylesheet.css?v={STYLE_VERSION}" type="text/css">
<link rel="icon" href="data:,">
</head>

<body>

<a class="skip-link" href="#main">Skip to content</a>

<header class="topbar">
  <div class="topbar-inner">
    <a class="topbar-brand" href="{U_NEWS}">
      <img src="{TEMPL_DIR}images/rpgdx_logo.png" alt="" />
      <span>RPGDX</span>
    </a>
    <button type="button" class="nav-toggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="primary-nav">
      <span></span><span></span><span></span>
    </button>
    <nav id="primary-nav" class="topbar-nav" aria-label="Primary">
      <a href="{U_NEWS}">News</a>
      <a href="{U_FORUMS}">Forums</a>
      <a href="{U_WIKI}">Wiki</a>
      <a href="{U_CONTESTS}">Contests</a>
      <a href="{U_SEARCH}">Search</a>
      <a href="{U_ABOUT}">About</a>
    </nav>
    <div class="topbar-user">
      <span class="topbar-user-text">{LOGINBAR_TEXT}</span>
      <!-- BEGIN userlinks -->
      <span class="topbar-user-links">
        <!-- BEGIN userlink -->
        <a href="{userlinks.userlink.URL}">{userlinks.userlink.TEXT}</a>
        <!-- END userlink -->
      </span>
      <!-- END userlinks -->
    </div>
  </div>
</header>

<div class="layout">
  <aside class="sidebar" aria-label="Categories">
    <section class="card sidebar-card">
      <h2 class="card-title">Projects</h2>
      <ul class="nav-list">
        <!-- BEGIN rpgtype -->
        <li>
          <a href="{rpgtype.URL}">
            <span>{rpgtype.NAME}</span>
            <span class="badge">{rpgtype.COUNT}</span>
          </a>
        </li>
        <!-- END rpgtype -->
      </ul>
    </section>

    <section class="card sidebar-card">
      <h2 class="card-title">Articles</h2>
      <ul class="nav-list">
        <!-- BEGIN artcat -->
        <li>
          <a href="{artcat.URL}">
            <span>{artcat.NAME}</span>
            <span class="badge">{artcat.COUNT}</span>
          </a>
        </li>
        <!-- END artcat -->
      </ul>
    </section>

    <section class="sidebar-promos">
      <p class="sidebar-promos-title">Vote for us</p>
      <a href="http://www.rpg-dev.net/top50/vote.aspx?user=Bjorn">
        <img src="images/rpgdev_top50.gif" alt="" width="88" height="31">
      </a>
      <p class="sidebar-promos-title">Affiliates</p>
      <a href="http://www.rpg-dev.net/">
        <img src="images/rpgdev_banner.gif" alt="" width="88" height="31">
      </a>
      <a href="http://www.abstract-productions.net/">
        <img src="images/ap_banner.png" alt="" width="88" height="31">
      </a>
    </section>
  </aside>

  <main id="main" class="content">
    <header class="page-head">
      <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="{U_NEWS}">RPGDX</a>
        <!-- BEGIN subtitle -->
        <span class="breadcrumb-sep">/</span>
        <!-- BEGIN url -->
        <a href="{subtitle.url.URL}">
        <!-- END url -->
        {subtitle.TITLE}
        <!-- BEGIN url -->
        </a>
        <!-- END url -->
        <!-- END subtitle -->
      </nav>
      <h1 class="page-title">{PAGE_TITLE}</h1>
    </header>
    <div class="page-body">
