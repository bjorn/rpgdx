<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td valign="top" width="100%">
      <table width="100%" cellspacing="2" cellpadding="0">
        <tr>
          <td class="th" width="0">Posted by</td>
          <td class="td" width="100%"><a href="{POSTER_PROFILE_URL}">{GAME_POSTER}</a>&nbsp;</td>
        </tr>
        <!-- BEGIN status -->
        <tr>
          <td class="th" width="0">Status</td>
          <td class="td" width="100%">{status.TEXT}&nbsp;</td>
        </tr>
        <!-- END status -->
        <!-- BEGIN language -->
        <tr>
          <td class="th" width="0">Prog. language</td>
          <td class="td" width="100%">{language.NAME}&nbsp;</td>
        </tr>
        <!-- END language -->
        <tr>
          <td class="th">Last update</td>
          <td class="td">{GAME_LASTUPDATE}&nbsp;</td>
        </tr>
        <!-- BEGIN links -->
        <tr>
          <td class="th">Links</td>
          <td class="td">
            <!-- BEGIN link -->
            <a href="{links.link.URL}">{links.link.NAME}</a>;  
            <!-- END link -->
          </td>
        </tr>
        <!-- END links -->
      </table>

      <div class="description">
      <!-- BEGIN descpart -->
      <b>{descpart.HEADING}:</b>
      <p>{descpart.CONTENT}</p>
      <!-- END descpart -->
      <!-- BEGIN nodesc -->
      <p><i>No description given.</i></p>
      <!-- END nodesc -->
      </div>

      <!-- BEGIN contest_award -->
      <div class="award_block">
      <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
          <td><img class="award_image" src="{contest_award.IMAGE_URL}"></td>
          <td align="center" valign="middle">
            {GAME_NAME} won {contest_award.CATEGORY_NAME} in the <a href="{contest_award.CONTEST_URL}">{contest_award.CONTEST_NAME}</a>.
          </td>
          <td align="right"><img class="award_image" src="{contest_award.IMAGE_URL}"></td>
        </tr>
      </table>
      </div>
      <!-- END contest_award -->

      <table cellpadding="0" cellspacing="0" width="100%">
        <tr>
          <td class="th">Reviews</td>
        </tr>
      </table>
      <div class="description">
      <!-- BEGIN noreviews -->
      <i>No reviews have been written yet.</i>
      <!-- END noreviews -->
      <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <!-- BEGIN review -->
        <tr>
          <td valign="top" width="100%">
            <table cellspacing="2" cellpadding="2" border="0" width="100%">
              <tr>
                <!-- review_id = {review.REVIEW_ID} -->
                <td style="border-bottom: 1px solid rgb(80,80,130); border-right: 1px solid rgb(80,80,130);">
                  Review by <a href="{review.REVIEWER_PROFILE_URL}">{review.REVIEWER}</a> on {review.ADDED}
                </td>
              </tr>
              <tr>
                <td><i>{review.TEXT}</i></td>
              </tr>
            </table>
          </td>
          <td valign="top" width="0">
            <table cellspacing="2" cellpadding="2" border="0" width="100%">
              <tr>
                <td nowrap align="right" style="padding: 5px; border: 1px solid rgb(80,80,130);">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr><td align="right" valign="top" nowrap class="th">
                      <b><span style="font-size: 15px;">{review.SCORE}</span></b>&nbsp;
                    </td></tr>
                  </table>
                </td>
              </tr>
              <tr><td></td></tr>
            </table>
          </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <!-- END review -->
      </table>
      </div>

    </td>
    <td><img src="{TEMPL_DIR}images/pixel.gif" width="5" height="1" /></td>
    <td valign="top">
      <div class="thin_shadow">
        <div class="lift_by_one">
          <div class="sidebar">
            <div class="sidebar_title">Project rating</div>
            <div class="sidebar_item">
              <div style="padding: 5px;">
              <!-- BEGIN reviews -->
              <div style="padding: 5px; border: 1px solid rgb(50,50,70); background-color: rgb(120,120,170); color: rgb(0,0,0);"><span style="font-size: 20px;">{reviews.AVERAGE}</span><div class="small_date">{reviews.CAPTION}</div></div>
              <!-- END reviews -->
              <!-- BEGIN review_allowed -->
              <div align="center" style="padding: 5px;">[<a href="{GAME_REVIEW_URL}">review this game</a>]</div>
              <!-- END review_allowed -->
              <!-- BEGIN review_not_allowed -->
              <div align="center" style="padding: 5px; font-size: 10px;">Reviewing disabled<br />for this project.</div>
              <!-- END review_not_allowed -->
              </div>
            </div>
            <!-- BEGIN screens -->
            <div class="sidebar_title">Screenshots</div>
            <div class="sidebar_item">
            <div class="screenshots">
            <!-- BEGIN shot -->
            <a href="{screens.shot.LINK}"><img src="{screens.shot.IMG}" alt="Screenshot" title="{screens.shot.TITLE}" class="screenshot" /></a><br />
            <!-- END shot -->
            </div>
            </div>
            <!-- END screens -->
          </div>
        </div>
      </div>
    </td>
  </tr>
</table>
