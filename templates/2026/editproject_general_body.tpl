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
<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tr>
    <td class="th" width="0">Project name:</td>
    <td class="td" width="100%"><input type="text" name="{form.NAME_FIELD}" value="{PROJECT_NAME}" class="formInput"></td>
  </tr>
  <tr><td colspan="2"><img src="images/pixel.gif" width="1" height="10" alt="" /></td></tr>
  <tr>
    <td valign="top" class="th">
      People working on it:<br />
      <span style="font-weight: normal; font-size: 85%;">
      <i>(Leave empty when you are<br />
      the only one working on the<br />
      project)</i>
      </span>
    </td>
    <td class="td"><textarea rows="5" name="{form.PEOPLE_FIELD}" cols="30" class="formInput">{PROJECT_PEOPLE}</textarea></td>
  </tr>
  <tr><td colspan="2"><img src="images/pixel.gif" width="1" height="10" /></td></tr>
  <tr>
    <td valign="top" class="th">Genre/category:</td>
    <td class="td">
      <select name="{form.GENRE_ID_FIELD}" size="1" class="formListBox">
      {form.GENRE_OPTIONS}
      </select>
    </td>
  </tr>
  <tr>
    <td valign="top" class="th">Programming language:</td>
    <td class="td">
      <select name="{form.LANG_ID_FIELD}" size="1" class="formListBox">
      {form.LANG_OPTIONS}
      </select>
    </td>
  </tr>
  <tr>
    <td valign="top" class="th">Project status:</td>
    <td class="td">
      <select name="{form.PROGRESS_ID_FIELD}" size="1" class="formListBox">
      {form.PROGRESS_OPTIONS}
      </select>
    </td>
  </tr>
  <tr>
    <td valign="top" class="th">Reviewing:</td>
    <td class="td">
      <input type="checkbox" name="{form.ALLOW_REVIEW_FIELD}" value="1" {PROJECT_ALLOW_REVIEW}> Allow reviewing
    </td>
  </tr>
  <tr><td colspan="2"><img src="images/pixel.gif" width="1" height="10" /></td></tr>
  <tr>
    <td valign="top" class="th">
      Brief description:
      <div style="font-size: 10px; margin-left: 1px; padding-top: 5px;">
      ignores newlines
      </div>
    </td>
    <td class="td"><textarea rows="3" name="{form.BRIEF_FIELD}" cols="30" class="formInput">{PROJECT_BRIEF}</textarea></td>
  </tr>
  <tr><td colspan="2"><img src="images/pixel.gif" width="1" height="10" /></td></tr>
  <tr>
    <td valign="top" class="th">
      Lengthy description:
      <div style="font-size: 10px; margin-left: 1px; padding-top: 5px;">
      {BBCODE_ON}
      </div>
    </td>
    <td class="td"><textarea rows="6" name="{form.LENGTHY_FIELD}" cols="30" class="formInput">{PROJECT_LENGTHY}</textarea></td>
  </tr>
  <tr><td colspan="2"><img src="images/pixel.gif" width="1" height="10" /></td></tr>
  <tr>
    <td valign="top" class="th">Project webpage:</td>
    <td class="td"><input type="text" name="{form.URL_FIELD}" class="formInput" value="{PROJECT_URL}"></td>
  </tr>
  <tr>
    <td valign="top" class="th">Download URL:</td>
    <td class="td"><input type="text" name="{form.DOWNLOAD_FIELD}" class="formInput" value="{PROJECT_DOWNLOAD}"></td>
  </tr>

  <!-- BEGIN edit -->
  <tr>
    <td valign="top" class="td">
      <div style="font-size: 10px; margin-left: 1px;">
      <br />
      html disabled
      </div>
    </td>
    <td class="td">
      <br /><input type="checkbox" value="1" name="{form.edit.FIELD}" {form.edit.CHECKED}}>&nbsp;Set "last updated" variable<br />&nbsp;<span style="font-weight: normal; font-size: 85%;"><i>(When you choose to do this, please report what has changed in the lengthy description)</i></span>
    </td>
  </tr>
  <!-- END edit -->
</table>
<p>
<input type="submit" value="{form.SUBMIT_TEXT}" name="submit">
</p>
</form>
<!-- END form -->

<!-- BEGIN remove -->
<p align="center">
<b>Are you sure you want to remove the project "{PROJECT_NAME}" from RPGDX?</b><br />
[<a href="{remove.YES_LINK}">yes</a>] [<a href="{remove.NO_LINK}">no</a>]
</p>
<!-- END remove -->
