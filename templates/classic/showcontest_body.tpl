
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td valign="top" width="100%">
      <table width="100%" cellspacing="2" cellpadding="0" border="0">
        <tr>
          <td class="th" width="0">Start time</td>
          <td class="td" width="100%">{CONTEST_START}&nbsp;</td>
        </tr>
        <tr>
          <td class="th">End time</td>
          <td class="td">{CONTEST_END}&nbsp;</td>
        </tr>
      </table>
      <br />
      <h4>Description</h4>
      {CONTEST_DESCRIPTION}
      <br />
      <br />

      <h4>Participants</h4>

      <table cellpadding="0" cellspacing="1" width="100%">
        <tr>
          <td class="th">Name</td>
          <td class="th">Project</td>
          <td class="th">Subscribed</td>
          <td class="th">Won</td>
        </tr>
        <!-- BEGIN subscriptions -->
        <!-- BEGIN subscription -->
        <tr>
          <td class="td"><a href="{subscriptions.subscription.USER_URL}">{subscriptions.subscription.USER_NAME}&nbsp;</a></td>
          <td class="td"><a href="{subscriptions.subscription.PROJECT_URL}">{subscriptions.subscription.PROJECT_NAME}&nbsp;</a></td>
          <td class="td">{subscriptions.subscription.DATE}&nbsp;</td>
          <td class="td">{subscriptions.subscription.WON}&nbsp;</td>
        </tr>
        <!-- END subscription -->
        <!-- END subscriptions -->

        <!-- BEGIN no_subscriptions -->
        <tr>
          <td class="td" colspan="4" align="center"><i>None right now.</i></td>
        </tr>
        <!-- END no_subscriptions -->
      </table>
      <br />
      <br />

      <h4>Subscription</h4>
      <!-- BEGIN subscribe -->
      <i>If you have added a project with which you want to subscribe, subscribe with it here.</i><br /><br />
      <form action="{subscribe.ACTION}" method="post">
      {subscribe.HIDDEN}
      <table cellpadding="0" cellspacing="2" border="0">
        <tr>
          <td class="th">Project:</td>
          <td class="td">
            <select name="{subscribe.PROJECT_FIELD}" size="1" class="formListBox">
            {subscribe.PROJECT_OPTIONS}
            </select>
          </td>
        </tr>
        <tr>
          <td class="th">&nbsp;</td>
          <td class="td">
            <input type="submit" name="subscribe" value="Subscribe" class="formButton">
          </td>
        </tr>
      </table>
      </form>
      <!-- END subscribe -->

      <!-- BEGIN unsubscribe -->
      <p>You are currently subscribed with {unsubscribe.GAME_NAME}.</p>
      <form action="{unsubscribe.ACTION}" method="post">
      {unsubscribe.HIDDEN}
      <input type="submit" name="unsubscribe" value="Unsubscribe" class="formButton">
      </form>
      <!-- END unsubscribe -->

      <!-- BEGIN no_subscription -->
      <p><i>{no_subscription.TEXT}</i></p>
      <!-- END no_subscription -->

      <h4>Voting</h4>
      <!-- BEGIN voting -->
      <i>Before voting, please make sure you have read the contest description and that you've taken a look at each participating project that has fullfilled the requirements of the contest and its description. Take into account only the things done by the author himself (ie. don't vote for ripped or borrowed stuff).</i><br /><br />
      <form action="{voting.ACTION}" method="post">
      <table cellpadding="0" cellspacing="1">
        <tr>
          <td class="th">Category/place&nbsp;</td>
          <td class="th">Project</td>
        </tr>
        <!-- BEGIN categories -->
        <!-- BEGIN categorie -->
        <tr>
          <td class="td">{voting.categories.categorie.NAME}&nbsp;</a></td>
          <td class="td">
            <select name="{voting.categories.categorie.PROJECT_FIELD}" size="1" class="formListBox">
            {voting.PROJECT_OPTIONS}
            </select>
            <a href="{subscriptions.subscription.PROJECT_URL}">{subscriptions.subscription.PROJECT_NAME}&nbsp;</a>
          </td>
        </tr>
        <!-- END categorie -->
        <!-- END categories -->

        <!-- BEGIN no_categories -->
        <tr>
          <td class="td" colspan="2" align="center"><i>No categories/places yet.</i></td>
        </tr>
        <!-- END no_categories -->
      </table>
      <br>
      {voting.HIDDEN}
      <input type="submit" name="vote" value="Submit votes" class="formButton">
      <!-- END voting -->

      <!-- BEGIN no_voting -->
      <p><i>{no_voting.TEXT}</i></p>
      <!-- END no_voting -->
    </td>
  </tr>
</table>



