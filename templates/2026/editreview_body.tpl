<!-- BEGIN error -->
<p align="center">
One or more errors have occured:<br />
<span style="color: rgb(255,128,128);">{error.TEXT}</span>
</p>
<!-- END error -->

<!-- BEGIN message -->
<p align="center" style="color: rgb(255,255,255);"><b>{message.TEXT}</b></p>
<!-- END message -->

<!-- BEGIN writing -->
<!-- END writing -->
<!-- BEGIN editing -->
<h2>Review about {GAME_NAME}</h2>
<!-- END editing -->

<!-- BEGIN form -->
<h4>Guidelines</h4>
<p>Here are some guidelines for the reviewer, without trying to patronize him. Make sure you have played the game long enough to have a decent opinion about the game. Then, in your review, don't just tell what you like and don't like but try to give some arguments. Be honest while at the same time not insulting anyone.</p>

<p>If you have problems running the game or questions about how to get further in the game, the review is not the place to put them. Post those on the forum instead.</p>
<form action="{form.ACTION}" method="post">
{form.HIDDEN}
<h4>Review</h4>
<table cellpadding="0" cellspacing="1" border="0" width="100%">
  <tr>
    <td class="th" width="0">Rating:</td>
    <td class="td" width="100%">
      <select align="bottom" name="{form.RATING_FIELD}" class="formInput" style="width: 45px;">
      {form.RATING_OPTIONS}
      </select>
    </td>
  </tr>
  <tr>
    <td class="th" valign="top">Review:</td>
    <td class="td"><textarea rows="7" name="{form.TEXT_FIELD}" cols="30" class="formInput">{REV_TEXT}</textarea></td>
  </tr>
</table>
<div style="font-size: 10px; margin-left: 1px;">
bbcode is disabled<br />
html is disabled
</div>
<br />
<input type="submit" value="{form.SUBMIT_TEXT}" name="submit">
</form>
<!-- END form -->

<!-- BEGIN remove -->
<p align="center">
<b>Are you sure you want to remove your review about the game "{GAME_NAME}" from RPGDX?</b><br />
[<a href="{remove.YES_LINK}">yes</a>] [<a href="{remove.NO_LINK}">no</a>]
</p>
<!-- END remove -->
