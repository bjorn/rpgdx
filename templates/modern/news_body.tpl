<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td valign="top">
      <!-- BEGIN newspost -->
      <table border="0" cellspacing="1" cellpadding="0" width="100%">
        <tr>
        <td width="100%">
          <table cellspacing="0" cellpadding="0" width="100%">
          <!-- post_id = {newspost.POST_ID} -->
          <tr>
            <td class="news_header"><b>{newspost.TITLE}&nbsp;</b></td>
            <td class="news_header" align="right">{newspost.EDIT_LINK}</td>
          </tr>
          <tr>
            <td colspan="2" class="td"><a href="{newspost.POSTER_PROFILE_URL}">{newspost.POSTER_NAME}</a> - {newspost.POST_DATE}</td>
          </tr>
          </table>
        </td>
        </tr>
        <tr>
        <td class="news_main" colspan="2" style="padding-bottom: 20px;">{newspost.MESSAGE}</td>
        </tr>
      </table>
      <!-- END newspost -->
    </td>
    <td><img src="{TEMPL_DIR}images/pixel.gif" width="5" height="1" alt="" /></td>
    <td valign="top">
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
    </td>
  </tr>
</table>
