<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
  <td width="50%" valign="top">

    <table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
      <td class="boxHead">Personal Information</td>
    </tr>
    <tr>
      <td class="boxBody">
        <table>
        <tr>
          <td align="right" valign="top">
            <!-- BEGIN avatar -->
            <img src="{avatar.IMG_URL}" style="background-color: rgb(56,56,97); border: 2px solid rgb(56,56,97);" alt="Avatar" />
            <!-- END avatar -->
          </td>
          <td valign="bottom">
            <b>{USERNAME}</b>
            <div style="font-size: 10px;">{RANK}</div>
          </td>
        </tr>
        <tr>
          <td align="right" valign="top">Joined:</td>
          <td>{JOINED}</td>
        </tr>
        <!-- BEGIN website -->
        <tr>
          <td align="right" valign="top">Website:</td>
          <td>
            [<a href="{website.URL}" target="_blank">visit</a>]
          </td>
        </tr>
        <!-- END website -->
        <tr>
          <td align="right" valign="top">Location:</td>
          <td>{LOCATION}</td>
        </tr>
        <tr>
          <td align="right" valign="top">Occupation:</td>
          <td>{OCCUPATION}</td>
        </tr>
        <tr>
          <td align="right" valign="top">Interests:</td>
          <td>{INTERESTS}</td>
        </tr>
        </table>
      </td>
    </tr>
    </table>

    <div style="height: 5px; font-size: 1px;"></div>

    <table cellspacing="0" cellpadding="0"  width="100%">
    <tr>
      <td class="boxHead">Statistics</td>
    </tr>
    <tr>
      <td class="boxBody">
        <table>
        <tr>
          <td align="right" valign="top">Reviews:</td>
          <td>
            <b>{REVIEWS}</b><br />
            {AVG_REVIEW_SCORE} average score
          </td>
        </tr>
        <tr>
          <td valign="top">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td align="right" valign="top">Posts:</td>
          <td>
            <b>{POSTS}</b> [<a href="{SEARCH_URL}">find all</a>]<br />
            {POSTS_PERCENTAGE}% of total<br />
            {POSTS_PER_DAY} posts per day
          </td>
        </tr>
        </table>
      </td>
    </tr>
    </table>
  </td>

  <td><div style="width: 5px; font-size: 1px;"></div></td>

  <td width="50%" valign="top">

    <table cellspacing="0" cellpadding="0"  width="100%">
    <tr>
      <td class="boxHead">Contact Information</td>
    </tr>
    <tr>
      <td class="boxBody">
        <table width="100%">
        <tr>
          <td width="0" align="right" valign="top">Private&nbsp;Message:</td>
          <td width="100%">[<a href="{PM_URL}">send&nbsp;message</a>]</td>
        </tr>
        <tr>
          <td align="right" nowrap colspan="2" style="font-size: 1px; height: 0px; border-bottom: 1px solid rgb(80,80,130);">&nbsp;</td>
        </tr>
        <!-- BEGIN email -->
        <tr>
          <td align="right" valign="top" nowrap>E-mail&nbsp;Address:</td>
          <td>
            [<a href="{email.URL}">send&nbsp;email</a>]
          </td>
        </tr>
        <!-- END email -->
        <!-- BEGIN msn -->
        <tr>
          <td align="right" valign="top" nowrap>MSN&nbsp;Messenger:</td>
          <td>{msn.EMAIL}</td>
        </tr>
        <!-- END msn -->
        <!-- BEGIN yim -->
        <tr>
          <td align="right" valign="top" nowrap>Yahoo&nbsp;Messenger:</td>
          <td><a href="{yim.URL}">{yim.NAME}</a></td>
        </tr>
        <!-- END yim -->
        <!-- BEGIN aim -->
        <tr>
          <td align="right" valign="top" nowrap>AIM&nbsp;Address:</td>
          <td><a href="{aim.URL}">{aim.NAME}</a></td>
        </tr>
        <!-- END aim -->
        <!-- BEGIN icq -->
        <tr>
          <td align="right" valign="top" nowrap>ICQ&nbsp;Number:</td>
          <td>{icq.NUMBER} {icq.IMG}</td>
        </tr>
        <!-- END icq -->
        </table>
      </td>
    </tr>
    </table>

    <div style="height: 5px; font-size: 1px;"></div>

    <table cellspacing="0" cellpadding="0"  width="100%">
    <tr>
      <td class="boxHead">Projects on RPGDX</td>
    </tr>
    <tr>
      <td class="boxBody">
        <!-- BEGIN projects -->
        <ul>
        <!-- BEGIN project -->
        <li><a href="{projects.project.URL}">{projects.project.NAME}</a></li>
        <!-- END project -->
        </ul>
        <!-- END projects -->
        <!-- BEGIN no_projects -->
        <div style="text-align: center; font-style: italic;">{USERNAME} has no projects on RPGDX</div>
        <!-- END no_projects -->
      </td>
    </tr>
    </table>

  </td>
</tr>
</table>
