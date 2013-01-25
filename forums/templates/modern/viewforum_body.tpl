
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
<tr> 
  <td align="left" valign="bottom" colspan="2"><span class="gensmall"><b>{L_MODERATOR}: {MODERATORS}<br />{LOGGED_IN_USER_LIST}</b></span></td>
  <td align="right" valign="bottom" nowrap="nowrap"><span class="gensmall"><b>{PAGINATION}</b></span></td>
</tr>
<tr> 
  <td align="left" valign="middle" width="50"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" /></a></td>
  <td align="left" valign="middle" class="nav" width="100%"></td>
  <td align="right" valign="bottom" class="nav" nowrap="nowrap"><span class="gensmall"><a href="{U_MARK_READ}">{L_MARK_TOPICS_READ}</a></span></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="1" width="100%" class="forumline">
<tr> 
  <th colspan="2" align="center" class="thCornerL" nowrap="nowrap">&nbsp;{L_TOPICS}&nbsp;</th>
  <th width="50" align="center" class="thTop" nowrap="nowrap">&nbsp;{L_REPLIES}&nbsp;</th>
  <th width="100" align="center" class="thTop" nowrap="nowrap">&nbsp;{L_AUTHOR}&nbsp;</th>
  <th width="50" align="center" class="thTop" nowrap="nowrap">&nbsp;{L_VIEWS}&nbsp;</th>
  <th align="center" class="thCornerR" nowrap="nowrap">&nbsp;{L_LASTPOST}&nbsp;</th>
</tr>
<!-- BEGIN topicrow -->
<tr> 
  <td class="row1" align="center" valign="middle" width="20"><img src="{topicrow.TOPIC_FOLDER_IMG}" width=19 height=18 alt="{topicrow.L_TOPIC_FOLDER_ALT}" title="{topicrow.L_TOPIC_FOLDER_ALT}" /></td>
  <td class="row1" width="100%"><span class="topictitle">{topicrow.NEWEST_POST_IMG}{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}" class="topictitle">{topicrow.TOPIC_TITLE}</a></span><span class="gensmall"> {topicrow.GOTO_PAGE}</span></td>
  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.REPLIES}</span></td>
  <td class="row3" align="center" valign="middle" style="white-space: nowrap;"><span class="name">{topicrow.TOPIC_AUTHOR}</span></td>
  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.VIEWS}</span></td>
  <td class="row3Right" align="right" valign="middle" nowrap="nowrap"><span class="postdetails" title="Last post by {topicrow.LAST_POST_AUTHOR_NAME}">{topicrow.LAST_POST_TIME}  <!--{topicrow.LAST_POST_AUTHOR}--> {topicrow.LAST_POST_IMG}</span></td>
</tr>
<!-- END topicrow -->
<!-- BEGIN switch_no_topics -->
<tr> 
  <td class="row1" colspan="6" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
</tr>
<!-- END switch_no_topics -->
</table>

<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
<tr> 
  <td align="left" valign="middle" width="50"><a href="{U_POST_NEW_TOPIC}"><img src="{POST_IMG}" border="0" alt="{L_POST_NEW_TOPIC}" /></a> </td>
  <td align="left" valign="middle" width="100%"><span class="gensmall">{PAGE_NUMBER}</span></td>
  <td align="right" valign="middle" nowrap="nowrap"><span class="gensmall">{S_TIMEZONE}</span><br /><span class="gensmall"><b>{PAGINATION}&nbsp;</b></span>
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

<form method="post" action="{S_POST_DAYS_ACTION}">
<table width="100%" border=0 cellspacing=3 cellpadding=0>
	<tr> 
	  <td align=right><span class=genmed><br />{L_DISPLAY_TOPICS}:&nbsp;{S_SELECT_TOPIC_DAYS} 
		<input type="submit" class="liteoption" value="{L_GO}" name="submit" />
		</span></td>
	</tr>
  <tr><td align=right>{JUMPBOX}</td></tr>
</table>
</form>

<table width="100%" cellspacing="0" border="0" align="center" cellpadding="0">
	<tr>
		<td align="left" valign="top"><table cellspacing="3" cellpadding="0" border="0">
			<tr>
				<td width="20" align="left"><img src="{FOLDER_NEW_IMG}" alt="{L_NEW_POSTS}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_IMG}" alt="{L_NO_NEW_POSTS}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_ANNOUNCE_IMG}" alt="{L_ANNOUNCEMENT}" width="19" height="18" /></td>
				<td class="gensmall">{L_ANNOUNCEMENT}</td>
			</tr>
			<tr> 
				<td width="20" align="center"><img src="{FOLDER_HOT_NEW_IMG}" alt="{L_NEW_POSTS_HOT}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS_HOT}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_HOT_IMG}" alt="{L_NO_NEW_POSTS_HOT}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS_HOT}</td>
				<td>&nbsp;&nbsp;</td>
				<td width="20" align="center"><img src="{FOLDER_STICKY_IMG}" alt="{L_STICKY}" width="19" height="18" /></td>
				<td class="gensmall">{L_STICKY}</td>
			</tr>
			<tr>
				<td class="gensmall"><img src="{FOLDER_LOCKED_NEW_IMG}" alt="{L_NEW_POSTS_LOCKED}" width="19" height="18" /></td>
				<td class="gensmall">{L_NEW_POSTS_LOCKED}</td>
				<td>&nbsp;&nbsp;</td>
				<td class="gensmall"><img src="{FOLDER_LOCKED_IMG}" alt="{L_NO_NEW_POSTS_LOCKED}" width="19" height="18" /></td>
				<td class="gensmall">{L_NO_NEW_POSTS_LOCKED}</td>
			</tr>
		</table></td>
		<td align="right"><span class="gensmall">{S_AUTH_LIST}</span></td>
	</tr>
</table>
