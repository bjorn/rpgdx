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
{S_FORM_TOKEN}
{form.HIDDEN}
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td class="formNameCell">Article name:&nbsp;</td>
    <td width="100%"><input type="text" name="{form.NAME_FIELD}" size="35" class="formInput" value="{ART_NAME}"></td>
  </tr>
  <tr>
    <td class="formNameCell">URL to article:&nbsp;</td>
    <td width="100%"><input type="text" name="{form.URL_FIELD}" size="35" class="formInput" value="{ART_URL}"></td>
  </tr>
</table>

<br />
<h4>Type of Article:</h4>
{form.TYPE_SELECTION}

<br />
<h4>Description:</h4>
<textarea rows="7" name="{form.DESC_FIELD}" cols="30" class="formInput">{ART_DESC}</textarea>

<br />
<input type="submit" value="{form.SUBMIT_TEXT}" name="submit">
</form>
<!-- END form -->

<!-- BEGIN remove -->
<div align="center">
<b>Are you sure you want to remove the article "{ART_NAME}" from RPGDX?</b><br />
<form action="{remove.ACTION}" method="post">
{S_FORM_TOKEN}
{remove.HIDDEN}
<input type="submit" name="confirmed" value="yes"> [<a href="{remove.NO_LINK}">no</a>]
</form>
</div>
<!-- END remove -->
