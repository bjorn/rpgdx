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
<h2>Reviewing {GAME_NAME}</h2>
<!-- END writing -->
<!-- BEGIN editing -->
<h2>Review about {GAME_NAME}</h2>
<!-- END editing -->

<!-- BEGIN form -->
<form action="{form.ACTION}" method="post">
{form.HIDDEN}
<h4>Rating:</h4>
<p>
<select align="bottom" name="{form.RATING_FIELD}" class="formInput" style="width: 45px;">
{form.RATING_OPTIONS}
</select>

<h4>Review</h4>
<textarea rows="7" name="{form.TEXT_FIELD}" cols="30" class="formInput">{REV_TEXT}</textarea>

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
