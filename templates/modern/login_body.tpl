<div align="center"><br />
<!-- BEGIN error -->
<p style="color: rgb(255,255,255); font-weight: bold;">{error.TEXT}</p>
<!-- END error -->
<form action="{S_LOGIN_ACTION}" method="post" target="_top">
{S_HIDDEN_FIELDS}
<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td class="formNameCell" align="right">Username:&nbsp;</td>
    <td width="150"><input type="text" name="username" size="25" maxlength="40" class="formInput" value="{USERNAME}"></td>
  </tr>
  <tr>
    <td class="formNameCell" align="right">Password:&nbsp;</td>
    <td><input type="password" name="password" size="25" maxlength="32" class="formInput"></td>
  </tr>
</table>
<br />
<input type="checkbox" name="autologin">&nbsp;Remember me<br />
<br />
<input type="submit" value="Login" name="login"><br />
</form>
When you choose to be remembered you will be automatically logged in on future visits to RPGDX. You can stop this behaviour by explicitly logging out.
</div>
