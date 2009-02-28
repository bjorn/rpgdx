<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td class="boxBody" style="border-width: 3px;">
      {TYPE_DESCRIPTION}
    </td>
  </tr>
</table>

<div style="height: 10px;">&nbsp;</div>

<!-- BEGIN rpglist -->
<form method="get" action="{rpglist.SORTMODE_ACTION}" style="madding: 0px; margin: 0px;">
{rpglist.HIDDEN_FORM_DATA}
<div style="font-size: 12px; whitespace: nowrap; text-align: right;">
Order by: <select name="{rpglist.SORTMODE_NAME}" size="1" class="formInput" style="width: auto;">{rpglist.SORTMODE_OPTIONS}</select>&nbsp;
<input style="font-size: 12px;" type="submit" name="submit" value="Sort"></div>
</form>

<div style="height: 10px;">&nbsp;</div>

<table cellspacing="2" cellpadding="2" border="0" width="100%">
  <tr>
    <td class="th" align="center" width="100%">Game name</td>
    <td class="th" align="center" width="100%">Rating</td>
    <td class="th" align="center" width="0">Last updated</td>
  </tr>

  <!-- BEGIN rpg -->
  <tr>
    <td><a href="{rpglist.rpg.LINK}">{rpglist.rpg.NAME}</a></td>
    <td style="white-space: nowrap;" align="right">
      <!-- BEGIN rating -->
      <div class="barBackground"><div class="barForeground" style="width: {rpglist.rpg.rating.SCORE}px;">&nbsp;</div></div>
      <!-- END rating -->
    </td>
    <td style="white-space: nowrap;">&nbsp;{rpglist.rpg.LAST_UPDATE}</td>
  </tr>
  <tr><td colspan="3" style="border-top: 1px solid rgb(80,80,130); border-bottom: 1px solid rgb(80,80,130);">
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
  </td></tr>
  <tr><td colspan="3">&nbsp;</td></tr>
  <!-- END rpg -->
</table>
<!-- END rpglist -->
<!-- BEGIN zeroprojects -->
<p align="center"><i>Currently, no projects of this type exist on RPGDX.</i></p>
<!-- END zeroprojects -->
