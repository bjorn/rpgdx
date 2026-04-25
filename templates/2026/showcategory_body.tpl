<div class="boxBody" style="border-width: 3px;">
  {TYPE_DESCRIPTION}
</div>

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
  <tr><td colspan="3" class="rpg-desc-cell">
    <div class="rpg-desc">
      <!-- BEGIN icon -->
      <img class="rpg-icon" src="{rpglist.rpg.icon.URL}" width="{rpglist.rpg.icon.WIDTH}" height="{rpglist.rpg.icon.HEIGHT}" alt="" />
      <!-- END icon -->
      <div class="rpg-brief">{rpglist.rpg.BRIEF_DESC}</div>
    </div>
  </td></tr>
  <tr><td colspan="3">&nbsp;</td></tr>
  <!-- END rpg -->
</table>
<!-- END rpglist -->
<!-- BEGIN zeroprojects -->
<p align="center"><i>Currently, no projects of this type exist on RPGDX.</i></p>
<!-- END zeroprojects -->
