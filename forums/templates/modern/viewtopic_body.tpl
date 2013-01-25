

<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
  <td align="left" valign="bottom" nowrap="nowrap"><span class="nav"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" align="middle" /></a>&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{REPLY_IMG}" border="0" alt="{L_POST_REPLY_TOPIC}" align="middle" /></a></span></td>
  <td align="right" valign="middle" width="100%"><span class="gensmall"><b>{PAGINATION}</b>&nbsp; </span></td>
  </tr>
</table>

<table class="forumline" width="100%" cellspacing="1" cellpadding="3" border="0">
  <tr align="right">
    <td class="catHead" colspan="2"><span class="nav"><a href="{U_VIEW_OLDER_TOPIC}" class="gensmall">{L_VIEW_PREVIOUS_TOPIC}</a> - <a href="{U_VIEW_NEWER_TOPIC}" class="gensmall">{L_VIEW_NEXT_TOPIC}</a> &nbsp;</span></td>
  </tr>
  {POLL_DISPLAY} 
  <tr>
    <th class="thLeft" width="150" nowrap="nowrap">{L_AUTHOR}</th>
    <th class="thRight" nowrap="nowrap">{L_MESSAGE}</th>
  </tr>
  <!-- BEGIN postrow -->
  <tr> 
    <td width="150" align="left" valign="top" class="posterDetails">
      <span class="name"><a name="{postrow.U_POST_ID}"></a><b>{postrow.POSTER_NAME}</b></span><br /><span class="postdetails">{postrow.POSTER_RANK}<br />{postrow.RANK_IMAGE}{postrow.POSTER_AVATAR}<br /><br />{postrow.POSTER_JOINED}<br />{postrow.POSTER_POSTS}<br />{postrow.POSTER_FROM}</span><br />
    </td>
    <td class="{postrow.ROW_CLASS}" width="100%" height="28" valign="top">
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="100%"><a href="{postrow.U_MINI_POST}"><img src="{postrow.MINI_POST_IMG}" width="12" height="9" alt="{postrow.L_MINI_POST_ALT}" title="{postrow.L_MINI_POST_ALT}" border="0" /></a><span class="postdetails">{L_POSTED}: {postrow.POST_DATE}<span class="gen">&nbsp;</span>&nbsp; &nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></td>
          <td valign="top" align="right" nowrap="nowrap">{postrow.QUOTE_IMG} {postrow.EDIT_IMG} {postrow.DELETE_IMG} {postrow.IP_IMG}</td>
        </tr>
        <tr> 
          <td colspan="2"><hr /></td>
        </tr>
        <tr>
          <td colspan="2">
            <span class="postbody">{postrow.MESSAGE}{postrow.SIGNATURE}</span>
            <span class="gensmall">{postrow.EDITED_MESSAGE}</span>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td  class="posterDetails" width="150" align="left" valign="middle"><a href="#top" class="gensmall">{L_BACK_TO_TOP}</a></td>
    <td class="{postrow.ROW_CLASS}" width="100%" height="28" valign="bottom" nowrap="nowrap">&nbsp;</td>
  </tr>
  <tr> 
    <td class="spaceRow" colspan="2" height="1"><img src="templates/RPGDX/images/spacer.gif" alt="" width="1" height="1" /></td>
  </tr>
  <!-- END postrow -->
  <tr align="center"> 
    <!--<td class="catBottom" colspan="2" height="28"></td>-->
  </tr>
</table>

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
  <td align="left" valign="middle" nowrap="nowrap"><span class="nav"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" align="middle" /></a>&nbsp;<a href="{U_POST_REPLY_TOPIC}"><img src="{REPLY_IMG}" border="0" alt="{L_POST_REPLY_TOPIC}" align="middle" /></a></span></td>
  <td align="left" valign="middle" width="100%"><span class="gensmall">{PAGE_NUMBER}</span></td>
  <td align="right" valign="top" nowrap="nowrap"><span class="gensmall">{S_TIMEZONE}</span><br /><span class="gensmall"><b>{PAGINATION}&nbsp;</b></span> 
    </td>
  </tr>
</table>
<br />
<div class="thin_shadow">
<div class="lift_by_one">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
  <tr>
    <td class="navigation_only" colspan="3">
      <a href="http://www.rpgdx.net/">RPGDX</a> &gt;&gt; <a href="{U_INDEX}">Forums</a>
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
    </td>
  </tr>
</table>
</div>
</div>

<br />

<table width="100%" cellspacing="2" border="0" align="center">
  <tr>
    <td width="40%" valign=top nowrap="nowrap" align=left><span class="gensmall">{S_WATCH_TOPIC}</span><br /></td>
    <form method=post action="{S_POST_DAYS_ACTION}">
    <td align=right><span class="gensmall">{L_DISPLAY_POSTS}: {S_SELECT_POST_DAYS}&nbsp;{S_SELECT_POST_ORDER}&nbsp;<input type="submit" value="{L_GO}" class="liteoption" name="submit" /></span></td>
    </form>
  </tr>
  <tr>
    <td valign=bottom>{S_TOPIC_ADMIN}</td>
    <td align=right valign=top nowrap="nowrap">{JUMPBOX}<span class="gensmall">{S_AUTH_LIST}</span></td>
  </tr>
</table>
