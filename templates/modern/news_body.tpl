<div class="page-with-sidebar">
  <div class="page-main">
    <!-- BEGIN newspost -->
    <article class="news-post">
      <!-- post_id = {newspost.POST_ID} -->
      <header class="news_header">
        <h3 class="news-post-title">{newspost.TITLE}</h3>
        <span class="news-post-edit">{newspost.EDIT_LINK}</span>
      </header>
      <div class="news-post-meta"><a href="{newspost.POSTER_PROFILE_URL}">{newspost.POSTER_NAME}</a> - {newspost.POST_DATE}</div>
      <div class="news_main">{newspost.MESSAGE}</div>
    </article>
    <!-- END newspost -->
  </div>
  <aside class="page-sidebar">
    <div class="thin_shadow">
      <div class="lift_by_one">
        <div class="sidebar">
          <!--
          <div class="sidebar_title">Latest staff review</div>
          <div class="sidebar_item">
            <div style="padding: 2px 2px 0px 2px;"><img class="screenshot" src="/rpgdx/uploads/screenshot10.gif.thumb.jpg" alt=""></div>
            <div style="font-size: 11px; color: rgb(25,25,50); padding: 0px 3px 3px 3px;">
              <span style=" font-weight: bold;"><a class="highlight_url" href="showreview.php">Quest for a King <span style="  font-weight: normal;">reviewed by Rayner Deyke</span></span></a>
            </div>
          </div>
          -->
          <!-- BEGIN submenu -->
          <div class="sidebar_title">{submenu.TITLE}</div>
          <!-- BEGIN update -->
          <div class="sidebar_item">
            <div class="small_date">{submenu.update.DATE}</div>
            <div style="font-size: 11px; padding: 5px;">{submenu.update.LINK}</div>
          </div>
          <!-- END update -->
          <!-- END submenu -->
        </div>
      </div>
    </div>
  </aside>
</div>
