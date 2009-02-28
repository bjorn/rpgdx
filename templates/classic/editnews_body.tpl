<!-- BEGIN error -->
<p align="center">
One or more errors have occured:<br />
<span style="color: rgb(255,128,128);">{error.TEXT}</span>
</p>
<!-- END error -->

<!-- BEGIN message -->
<p align="center" style="color: rgb(255,255,255);"><b>{message.TEXT}</b></p>
<!-- END message -->

<!-- BEGIN form -->
<h4>Guidelines</h4>
<p>Your message may contain anything about one of your games or articles, or anything else that you think is important and relates to RPGs (either directly or indirectly). It will be visible in the news instantly, please check if everything went ok after posting.</p>
<form action="{form.ACTION}" method="post">
{form.HIDDEN}
<h4>News message</h4>
<table cellpadding="0" cellspacing="1" border="0" width="100%">
  <tr>
    <td class="th" width="0">Title:</td>
    <td class="td" width="100%"><input type="text" class="formInput" name="{form.NEWS_TITLE_FIELD}" value="{form.NEWS_TITLE}"></td>
  </tr>
  <tr>
    <td class="th" valign="top">Message:</td>
    <td class="td"><textarea rows="7" name="{form.NEWS_FIELD}" cols="30" class="formInput">{form.NEWS_MESSAGE}</textarea></td>
  </tr>
</table>
<div style="font-size: 10px; margin-left: 1px;">
bbcode is enabled<br />
html is disabled
</div>
<br />
<input type="submit" value="{form.SUBMIT_TEXT}" name="submit">
</form>
<!-- END form -->

<!-- BEGIN remove -->
<p align="center">
<b>Are you sure you want to remove this news message from RPGDX?</b><br />
[<a href="{remove.YES_LINK}">yes</a>] [<a href="{remove.NO_LINK}">no</a>]
</p>
<!-- END remove -->
