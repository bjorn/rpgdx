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
<form action="{form.ACTION}" method="post">
{form.HIDDEN}
<h4>Article:</h4>
<table cellpadding="0" cellspacing="1" border="0" width="100%">
  <tr>
    <td class="th" width="0">Title:</td>
    <td class="td" width="100%"><input type="text" name="{form.NAME_FIELD}" size="35" class="formInput" value="{ART_NAME}"></td>
  </tr>
  <tr>
    <td class="th">URL:</td>
    <td class="td"><input type="text" name="{form.URL_FIELD}" size="35" class="formInput" value="{ART_URL}"></td>
  </tr>
  <tr>
    <td class="th" valign="top">Type:</td>
    <td class="td">{form.TYPE_SELECTION}</td>
  </tr>
  <tr>
    <td class="th" valign="top">Description:</td>
    <td class="td"><textarea rows="7" name="{form.DESC_FIELD}" cols="30" class="formInput">{ART_DESC}</textarea></td>
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
<b>Are you sure you want to remove the article "{ART_NAME}" from RPGDX?</b><br />
[<a href="{remove.YES_LINK}">yes</a>] [<a href="{remove.NO_LINK}">no</a>]
</p>
<!-- END remove -->
