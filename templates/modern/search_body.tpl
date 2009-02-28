<!-- BEGIN form -->
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td class="boxBody" style="border-width: 3px;">
      <form method="post" action="{form.ACTION}" style="madding: 0px; margin: 0px;">
      {form.HIDDEN_FORM_DATA}
      <table cellpadding="5" cellspacing="0">
        <tr>
          <td>Status: <select name="{form.STATUS_FIELD}" class="formInput" style="width: auto;">{form.STATUS_OPTIONS}</select></td>
          <td><input type="checkbox" value="1" name="{form.DOWNLOAD_FIELD}" {SEARCH_DOWNLOAD}>&nbsp;Download available</input></td>
          <td><input type="checkbox" value="1" name="{form.REVIEWING_FIELD}" {SEARCH_REVIEWING}>&nbsp;Reviewing enabled</input></td>
        </tr>
        <tr>
          <td colspan="3">
            <table cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td width="0">Keywords:&nbsp;</td>
                <td width="100%"><input type="text" name="{form.QUERY_FIELD}" size="30" class="formInput" style="width: 98%;" value="{SEARCH_QUERY}"></td>
                <td width="0" align="right"><input class="formButton" type="submit" value="Search"></td>
              </tr>
            </table>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>
<!-- END form -->

<!-- BEGIN rpglist -->
<h3>Search returned {rpglist.NR_SEARCH_RESULTS} result(s):</h3>

<table cellspacing="0" cellpadding="2" border="0" width="100%">
  <!-- BEGIN rpg -->
  <tr>
    <td class="th" width="100%">
      <table cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
          <td style="white-space: nowrap;"><a href="{rpglist.rpg.LINK}">{rpglist.rpg.NAME}</a></td>
          <!-- BEGIN rating -->
          <td align="right">
            <table cellspacing="0" cellpadding="0" border="0">
              <tr>
                <td style="white-space: nowrap;" width="0"><span style="font-size: 10px;">Rating: &nbsp;</span></td>
                <td style="text-align: left;"><div class="barBackground"><div class="barForeground" style="width: {rpglist.rpg.rating.SCORE}px;">&nbsp;</div></div>
                </td>
              </tr>
            </table>
          </td>
          <!-- END rating -->
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="3">
      <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
          <td valign="top" width="32">
            <!-- BEGIN icon -->
            <img src="{rpglist.rpg.icon.URL}" width="{rpglist.rpg.icon.WIDTH}" height="{rpglist.rpg.icon.HEIGHT}" style="margin-right: 5px;" />
            <!-- END icon -->
          </td>
          <td valign="top" width="100%">
            {rpglist.rpg.BRIEF_DESC}
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr><td colspan="3">&nbsp;</td></tr>
  <!-- END rpg -->
</table>
<!-- END rpglist -->
<!-- BEGIN zeroprojects -->
<p align="center"><i>No project matching the above specification.</i></p>
<!-- END zeroprojects -->
