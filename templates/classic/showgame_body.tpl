<table width="100%" cellspacing="2" cellpadding="0">
	<tr>
		<td class="tableHeader" width="0">Name</td>
		<td class="tableCell" width="100%">{GAME_NAME}&nbsp;</td>
	</tr>
	<tr>
		<td class="tableHeader">Posted by</td>
		<td class="tableCell"><a href="{POSTER_PROFILE_URL}">{GAME_POSTER}</a>&nbsp;</td>
	</tr>
	<tr>
		<td class="tableHeader">Type</td>
		<td class="tableCell">{GAME_TYPE_LINK}&nbsp;</td>
	</tr>
  <!-- BEGIN status -->
	<tr>
		<td class="tableHeader" width="0">Status</td>
		<td class="tableCell" width="100%">{status.TEXT}&nbsp;</td>
	</tr>
  <!-- END status -->
  <!-- BEGIN language -->
	<tr>
		<td class="tableHeader" width="0">Prog. language</td>
		<td class="tableCell" width="100%">{language.NAME}&nbsp;</td>
	</tr>
  <!-- END language -->
	<tr>
		<td class="tableHeader">Last update</td>
		<td class="tableCell">{GAME_LASTUPDATE}&nbsp;</td>
	</tr>
</table>

<div class="description">
<!-- BEGIN descpart -->
<b>{descpart.HEADING}:</b>
<p>{descpart.CONTENT}</p>
<!-- END descpart -->
<!-- BEGIN nodesc -->
<i>No description given.</i>
<!-- END nodesc -->
</div>

<!-- BEGIN screens -->
<div class="screenshots">
<!-- BEGIN shot -->
<a href="{screens.shot.LINK}"><img src="{screens.shot.IMG}" alt="Screenshot" title="{screens.shot.TITLE}" width="160" border="0" class="screenshot" /></a>
<!-- END shot -->
</div>
<!-- END screens -->

<!-- BEGIN contest_award -->
<div class="description">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
  <tr>
    <td><img class="award_image" src="{contest_award.IMAGE_URL}"></td>
    <td align="center" valign="middle">
      {GAME_NAME} won {contest_award.CATEGORY_NAME} in the <a href="{contest_award.CONTEST_URL}">{contest_award.CONTEST_NAME}</a>.
    </td>
    <td align="right"><img class="award_image" src="{contest_award.IMAGE_URL}"></td>
  </tr>
</table>
</div>
<!-- END contest_award -->

<div class="description" style="border: 0px; padding: 0px;">

<!-- BEGIN reviews -->
<p>{reviews.LINK} (average rating: {reviews.AVERAGE})</p>
<!-- END reviews -->
<!-- BEGIN noreviews -->
<p><b>No reviews yet!</b></p>
<!-- END noreviews -->

<p align="center">
<!-- BEGIN link -->
[{link.LINK}] 
<!-- END link -->
</p>

<!-- BEGIN loginforreview -->
<p><i>You can login to be able to review this game.</i></p>
<!-- END loginforreview -->

</div>
