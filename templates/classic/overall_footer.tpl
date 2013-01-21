          </td>
        </tr>
        <tr>
          <td>
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td><img src="{TEMPL_DIR}images/darkcorner_bottomleft.gif" width="25" height="25" /></td>
                <td width="100%" style="background-color: rgb(56,56,97);" align="center" nowrap>&nbsp;</td>
                <td><img src="{TEMPL_DIR}images/darkcorner_bottomright.gif" width="25" height="25" /></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
    <td width="0" valign="top">
      <table border="0" cellpadding="0" cellspacing="5" width="100%">
        <tr>
          <td>
            <table border="0" cellpadding="0" cellspacing="1" class="sidebarTable">
              <tr><td background="{TEMPL_DIR}images/block_title.gif" class="sidebarTitle">Context</td></tr>
              <!-- BEGIN submenu -->
              <tr><td class="sidebarSubtitle">{submenu.TITLE}</td></tr>
              <!-- BEGIN update -->
              <tr><td class="sidebarItem">
                <div class="smallDate">{submenu.update.DATE}</div>
                {submenu.update.LINK}
              </td></tr>
              <!-- END update -->
              <!-- END submenu -->
              <tr><td class="sidebarBottom">&nbsp;</td></tr>
            </table>
          </td>
        </tr>
      </table>
      <br />
    </td>
    <td><img src="{TEMPL_DIR}images/pixel.gif" width="3" height="1" alt="" /></td>
  </tr>
  <tr><td colspan="4" height="10">&nbsp;</td></tr>
  <tr><td colspan="4" class="copyright">
    RPGDX &copy; 2003-2009 by Bjørn Lindeijer<br />
    Powered by <a href="http://www.php.net/">PHP</a> and <a href="http://www.mysql.com/">MySQL</a>
  </td></tr>
  <tr><td colspan="4" height="10">&nbsp;</td></tr>
</table>

</body>
</html>
