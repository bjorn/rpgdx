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
    <!-- BEGIN submenu -->
    <section class="card">
      <h2 class="card-title">{submenu.TITLE}</h2>
      <ul class="update-list">
        <!-- BEGIN update -->
        <li>
          <div class="update-date">{submenu.update.DATE}</div>
          <div class="update-text">{submenu.update.LINK}</div>
        </li>
        <!-- END update -->
      </ul>
    </section>
    <!-- END submenu -->
  </aside>
</div>
